<?php

namespace App\Http\Controllers\Email;

use App\Http\Controllers\Controller;
use App\Models\EmailInboxSetting;
use App\Models\EmailMessage;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class EmailInboxController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = EmailMessage::with(['inboxSetting.branch', 'assignedTo']);

        // Branch managers see only their branch emails
        if ($user->hasRole('branch_principal') && $user->branch_id) {
            $inboxIds = EmailInboxSetting::where('branch_id', $user->branch_id)->pluck('id');
            $query->whereIn('email_inbox_setting_id', $inboxIds);
        }

        // Filters
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('is_read')) {
            $query->where('is_read', $request->is_read === '1');
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'LIKE', "%{$search}%")
                  ->orWhere('from_email', 'LIKE', "%{$search}%")
                  ->orWhere('from_name', 'LIKE', "%{$search}%")
                  ->orWhere('body_text', 'LIKE', "%{$search}%");
            });
        }
        if ($request->filled('inbox_id')) {
            $query->where('email_inbox_setting_id', $request->inbox_id);
        }

        $messages = $query->orderBy('received_at', 'desc')->paginate(25);

        // Stats
        $baseQuery = $user->hasRole('branch_principal') && $user->branch_id
            ? EmailMessage::whereIn('email_inbox_setting_id', EmailInboxSetting::where('branch_id', $user->branch_id)->pluck('id'))
            : new EmailMessage();

        $totalMessages = (clone $baseQuery)->count();
        $unreadCount = (clone $baseQuery)->where('is_read', false)->count();
        $starredCount = (clone $baseQuery)->where('is_starred', true)->count();

        $inboxSettings = $user->hasRole('branch_principal') && $user->branch_id
            ? EmailInboxSetting::where('branch_id', $user->branch_id)->get()
            : EmailInboxSetting::with('branch')->get();

        return view('admin.email-inbox.index', compact(
            'messages', 'totalMessages', 'unreadCount', 'starredCount',
            'inboxSettings'
        ));
    }

    public function show(EmailMessage $email_message)
    {
        $email_message->load(['inboxSetting.branch', 'assignedTo']);

        // Mark as read
        if (!$email_message->is_read) {
            $email_message->update(['is_read' => true]);
        }

        return view('admin.email-inbox.show', compact('email_message'));
    }

    public function updateCategory(Request $request, EmailMessage $email_message)
    {
        $request->validate([
            'category' => 'required|in:' . implode(',', array_keys(EmailMessage::categoryOptions())),
        ]);

        $email_message->update(['category' => $request->category]);

        return back()->with('success', 'Email category updated.');
    }

    public function toggleStar(EmailMessage $email_message)
    {
        $email_message->update(['is_starred' => !$email_message->is_starred]);

        return back()->with('success', $email_message->is_starred ? 'Email starred.' : 'Star removed.');
    }

    public function assign(Request $request, EmailMessage $email_message)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:2000',
        ]);

        $email_message->update([
            'assigned_to' => $request->assigned_to,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Email assigned successfully.');
    }

    // Settings management
    public function settings()
    {
        $inboxSettings = EmailInboxSetting::with('branch')->get();
        $branches = Branch::orderBy('name')->get();

        return view('admin.email-inbox.settings', compact('inboxSettings', 'branches'));
    }

    public function storeSettings(Request $request)
    {
        $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'email_address' => 'required|email',
            'imap_host' => 'required|string',
            'imap_port' => 'required|integer',
            'imap_username' => 'required|string',
            'imap_password' => 'required|string',
            'imap_encryption' => 'required|in:ssl,tls,none',
            'folder' => 'nullable|string',
            'sync_interval_minutes' => 'nullable|integer|min:5',
        ]);

        EmailInboxSetting::create([
            'branch_id' => $request->branch_id,
            'email_address' => $request->email_address,
            'imap_host' => $request->imap_host,
            'imap_port' => $request->imap_port,
            'imap_username' => $request->imap_username,
            'imap_password' => Crypt::encryptString($request->imap_password),
            'imap_encryption' => $request->imap_encryption,
            'folder' => $request->folder ?? 'INBOX',
            'sync_interval_minutes' => $request->sync_interval_minutes ?? 15,
        ]);

        return back()->with('success', 'Email inbox configured successfully.');
    }

    public function updateSettings(Request $request, EmailInboxSetting $inboxSetting)
    {
        $request->validate([
            'email_address' => 'required|email',
            'imap_host' => 'required|string',
            'imap_port' => 'required|integer',
            'imap_username' => 'required|string',
            'imap_password' => 'nullable|string',
            'imap_encryption' => 'required|in:ssl,tls,none',
            'folder' => 'nullable|string',
            'sync_interval_minutes' => 'nullable|integer|min:5',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->only([
            'email_address', 'imap_host', 'imap_port', 'imap_username',
            'imap_encryption', 'folder', 'sync_interval_minutes', 'is_active'
        ]);

        if ($request->filled('imap_password')) {
            $data['imap_password'] = Crypt::encryptString($request->imap_password);
        }

        $inboxSetting->update($data);

        return back()->with('success', 'Email inbox settings updated.');
    }

    public function destroySettings(EmailInboxSetting $inboxSetting)
    {
        $inboxSetting->delete();
        return back()->with('success', 'Email inbox configuration deleted.');
    }

    public function syncInbox(EmailInboxSetting $inboxSetting)
    {
        try {
            $host = $inboxSetting->imap_host;
            $port = $inboxSetting->imap_port;
            $username = $inboxSetting->imap_username;
            $password = $inboxSetting->getDecryptedPassword();
            $encryption = $inboxSetting->imap_encryption;
            $folder = $inboxSetting->folder;

            $flags = '';
            if ($encryption === 'ssl') {
                $flags = '/imap/ssl/novalidate-cert';
            } elseif ($encryption === 'tls') {
                $flags = '/imap/tls/novalidate-cert';
            } else {
                $flags = '/imap/novalidate-cert';
            }

            $mailbox = imap_open("{" . $host . ":" . $port . $flags . "}" . $folder, $username, $password);

            if (!$mailbox) {
                return back()->with('error', 'Failed to connect to email server: ' . imap_last_error());
            }

            $emails = imap_search($mailbox, 'UNSEEN');
            $synced = 0;

            if ($emails) {
                rsort($emails);
                $maxToSync = 50; // Limit sync to prevent timeout
                $emails = array_slice($emails, 0, $maxToSync);

                foreach ($emails as $emailNumber) {
                    $header = imap_headerinfo($mailbox, $emailNumber);
                    $messageId = $header->message_id ?? uniqid();

                    // Skip if already exists
                    if (EmailMessage::where('message_id', $messageId)->exists()) {
                        continue;
                    }

                    $fromName = isset($header->from[0]) ? $header->from[0]->personal ?? '' : '';
                    $fromEmail = isset($header->from[0]) ? $header->from[0]->mailbox . '@' . $header->from[0]->host : '';
                    $toEmail = isset($header->to[0]) ? $header->to[0]->mailbox . '@' . $header->to[0]->host : '';
                    $subject = isset($header->subject) ? imap_utf8($header->subject) : '(No Subject)';
                    $date = isset($header->date) ? date('Y-m-d H:i:s', strtotime($header->date)) : now();

                    // Get body
                    $bodyHtml = '';
                    $bodyText = '';
                    $structure = imap_fetchstructure($mailbox, $emailNumber);

                    if (!$structure->parts) {
                        $bodyText = imap_body($mailbox, $emailNumber);
                    } else {
                        // Multipart - get text and html parts
                        foreach ($structure->parts as $partNumber => $part) {
                            if ($part->type === 0 && $part->subtype === 'PLAIN') {
                                $bodyText = imap_fetchbody($mailbox, $emailNumber, $partNumber + 1);
                            } elseif ($part->type === 0 && $part->subtype === 'HTML') {
                                $bodyHtml = imap_fetchbody($mailbox, $emailNumber, $partNumber + 1);
                            }
                        }
                    }

                    // Auto-categorize based on subject/body
                    $category = $this->autoCategorize($subject, $bodyText);

                    EmailMessage::create([
                        'email_inbox_setting_id' => $inboxSetting->id,
                        'message_id' => $messageId,
                        'subject' => $subject,
                        'body_html' => $bodyHtml,
                        'body_text' => $bodyText,
                        'from_name' => $fromName,
                        'from_email' => $fromEmail,
                        'to_email' => $toEmail,
                        'received_at' => $date,
                        'is_read' => false,
                        'category' => $category,
                    ]);

                    $synced++;
                }
            }

            imap_close($mailbox);

            $inboxSetting->update(['last_synced_at' => now()]);

            return back()->with('success', "Synced {$synced} new email(s) successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    private function autoCategorize(string $subject, string $body): string
    {
        $subjectLower = strtolower($subject);
        $bodyLower = strtolower(substr($body, 0, 1000));

        $keywords = [
            'admission' => ['admission', 'enroll', 'register', 'apply', 'application'],
            'fee' => ['fee', 'payment', 'tuition', 'invoice', 'receipt', 'bank transfer'],
            'complaint' => ['complaint', 'issue', 'problem', 'unhappy', 'dissatisfied'],
            'academic' => ['grade', 'exam', 'result', 'transcript', 'report card', 'lesson'],
            'hr' => ['job', 'vacancy', 'resume', 'cv', 'interview', 'position'],
        ];

        foreach ($keywords as $category => $words) {
            foreach ($words as $word) {
                if (str_contains($subjectLower, $word) || str_contains($bodyLower, $word)) {
                    return $category;
                }
            }
        }

        return 'uncategorized';
    }
}
