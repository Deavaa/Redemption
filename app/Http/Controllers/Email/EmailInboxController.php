<?php

namespace App\Http\Controllers\Email;

use App\Http\Controllers\Controller;
use App\Models\EmailInboxSetting;
use App\Models\EmailMessage;
use App\Models\Branch;
use App\Services\ImapClient;
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
            'smtp_host' => 'nullable|string',
            'smtp_port' => 'nullable|integer',
            'smtp_encryption' => 'nullable|in:ssl,tls,none',
            'is_default_sender' => 'nullable|boolean',
            'folder' => 'nullable|string',
            'sync_interval_minutes' => 'nullable|integer|min:5',
        ]);

        // If this inbox is marked as default sender, unset the flag on all others
        if ($request->boolean('is_default_sender')) {
            EmailInboxSetting::where('is_default_sender', true)->update(['is_default_sender' => false]);
        }

        EmailInboxSetting::create([
            'branch_id' => $request->branch_id,
            'email_address' => $request->email_address,
            'imap_host' => $request->imap_host,
            'imap_port' => $request->imap_port,
            'imap_username' => $request->imap_username,
            'imap_password' => Crypt::encryptString($request->imap_password),
            'imap_encryption' => $request->imap_encryption,
            'smtp_host' => $request->smtp_host ?: $request->imap_host,
            'smtp_port' => $request->smtp_port ?: 465,
            'smtp_encryption' => $request->smtp_encryption ?: 'ssl',
            'is_default_sender' => $request->boolean('is_default_sender'),
            'folder' => $request->folder ?? 'INBOX',
            'sync_interval_minutes' => $request->sync_interval_minutes ?? 15,
        ]);

        return back()->with('success', 'Email inbox configured successfully. SMTP sending is ' . ($request->boolean('is_default_sender') ? 'ENABLED' : 'disabled') . '.');
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
            'smtp_host' => 'nullable|string',
            'smtp_port' => 'nullable|integer',
            'smtp_encryption' => 'nullable|in:ssl,tls,none',
            'is_default_sender' => 'nullable|boolean',
            'folder' => 'nullable|string',
            'sync_interval_minutes' => 'nullable|integer|min:5',
            'is_active' => 'nullable|boolean',
        ]);

        // If this inbox is being marked as default sender, unset the flag on all others
        if ($request->boolean('is_default_sender')) {
            EmailInboxSetting::where('id', '!=', $inboxSetting->id)
                ->where('is_default_sender', true)
                ->update(['is_default_sender' => false]);
        }

        $data = $request->only([
            'email_address', 'imap_host', 'imap_port', 'imap_username',
            'imap_encryption', 'folder', 'sync_interval_minutes', 'is_active',
            'smtp_host', 'smtp_port', 'smtp_encryption', 'is_default_sender'
        ]);

        // Fill in SMTP defaults from IMAP if not provided
        if (empty($data['smtp_host'])) {
            $data['smtp_host'] = $request->imap_host;
        }
        if (empty($data['smtp_port'])) {
            $data['smtp_port'] = 465;
        }
        if (empty($data['smtp_encryption'])) {
            $data['smtp_encryption'] = 'ssl';
        }

        if ($request->filled('imap_password')) {
            $data['imap_password'] = Crypt::encryptString($request->imap_password);
        }

        $inboxSetting->update($data);

        return back()->with('success', 'Email inbox settings updated. SMTP sending is ' . ($inboxSetting->fresh()->is_default_sender ? 'ENABLED' : 'disabled') . '.');
    }

    public function destroySettings(EmailInboxSetting $inboxSetting)
    {
        $inboxSetting->delete();
        return back()->with('success', 'Email inbox configuration deleted.');
    }

    public function syncInbox(EmailInboxSetting $inboxSetting)
    {
        $imap = null;

        try {
            $host = $inboxSetting->imap_host;
            $port = $inboxSetting->imap_port;
            $username = $inboxSetting->imap_username;
            $password = $inboxSetting->getDecryptedPassword();
            $encryption = $inboxSetting->imap_encryption;
            $folder = $inboxSetting->folder ?? 'INBOX';

            // Use our built-in ImapClient (no php-imap extension required)
            $imap = new ImapClient($host, $port, $encryption);

            // Connect and authenticate
            if (!$imap->connect($username, $password)) {
                return back()->with('error', 'Failed to connect to email server: ' . $imap->getLastError());
            }

            // Select the mailbox folder
            $folderInfo = $imap->selectFolder($folder);
            if ($folderInfo === null) {
                return back()->with('error', 'Failed to select folder: ' . $imap->getLastError());
            }

            // Search for unread messages (using UIDs)
            $messageUids = $imap->search('UNSEEN', true);
            $synced = 0;

            if (!empty($messageUids)) {
                // Reverse to get newest first
                $messageUids = array_reverse($messageUids);

                // Limit sync to prevent timeout
                $messageUids = array_slice($messageUids, 0, 50);

                foreach ($messageUids as $uid) {
                    // Skip if already exists
                    if (EmailMessage::where('message_id', (string) $uid)->exists()) {
                        continue;
                    }

                    // Fetch message details
                    $messageData = $imap->fetchMessageForSync($uid, true);
                    if ($messageData === null) {
                        continue;
                    }

                    // Auto-categorize based on subject/body
                    $category = $this->autoCategorize(
                        $messageData['subject'] ?? '',
                        $messageData['body_text'] ?? ''
                    );

                    EmailMessage::create([
                        'email_inbox_setting_id' => $inboxSetting->id,
                        'message_id' => $messageData['message_id'] ?? (string) $uid,
                        'subject' => $messageData['subject'] ?? '(No Subject)',
                        'body_html' => $messageData['body_html'] ?? '',
                        'body_text' => $messageData['body_text'] ?? '',
                        'from_name' => $messageData['from_name'] ?? '',
                        'from_email' => $messageData['from_email'] ?? '',
                        'to_email' => $messageData['to_email'] ?? '',
                        'cc' => !empty($messageData['cc']) ? $messageData['cc'] : null,
                        'received_at' => $messageData['date'] ?? now()->format('Y-m-d H:i:s'),
                        'is_read' => false,
                        'category' => $category,
                    ]);

                    $synced++;
                }
            }

            $inboxSetting->update(['last_synced_at' => now()]);

            return back()->with('success', "Synced {$synced} new email(s) successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        } finally {
            // Always close the connection
            if ($imap) {
                $imap->disconnect();
            }
        }
    }

    public function testConnection(EmailInboxSetting $inboxSetting)
    {
        try {
            $imap = new ImapClient(
                $inboxSetting->imap_host,
                $inboxSetting->imap_port,
                $inboxSetting->imap_encryption
            );

            $result = $imap->testConnection(
                $inboxSetting->imap_username,
                $inboxSetting->getDecryptedPassword(),
                $inboxSetting->folder ?? 'INBOX'
            );

            if ($result === null) {
                return back()->with('error', 'Connection test failed: ' . $imap->getLastError());
            }

            $info = [];
            if (isset($result['exists'])) {
                $info[] = "Total messages: {$result['exists']}";
            }
            if (isset($result['unseen'])) {
                $info[] = "Unread messages: {$result['unseen']}";
            }

            $infoStr = !empty($info) ? ' (' . implode(', ', $info) . ')' : '';

            return back()->with('success', 'Connection test successful!' . $infoStr);
        } catch (\Exception $e) {
            return back()->with('error', 'Connection test failed: ' . $e->getMessage());
        }
    }

    /**
     * Send a test email using this inbox's SMTP credentials.
     * Verifies that the SMTP configuration works for outgoing email.
     */
    public function sendTestEmail(EmailInboxSetting $inboxSetting)
    {
        try {
            $host = $inboxSetting->getSmtpHost();
            $port = $inboxSetting->smtp_port ?? 465;
            $encryption = $inboxSetting->smtp_encryption ?? 'ssl';
            $username = $inboxSetting->getSmtpUsername();
            $password = $inboxSetting->getSmtpPassword();

            if (empty($host) || empty($username) || empty($password)) {
                return back()->with('error', 'SMTP credentials are incomplete. Please configure SMTP host, username (IMAP username), and password (IMAP password).');
            }

            // Temporarily override the SMTP config for this request
            config([
                'mail.mailers.smtp' => [
                    'transport' => 'smtp',
                    'host' => $host,
                    'port' => (int) $port,
                    'encryption' => $encryption,
                    'username' => $username,
                    'password' => $password,
                    'timeout' => 30,
                    'local_domain' => parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST),
                ],
                'mail.from' => [
                    'address' => $inboxSetting->email_address,
                    'name' => config('app.name', 'School ERP'),
                ],
            ]);

            $testEmail = $inboxSetting->email_address;
            $subject = 'Test Email from ' . config('app.name', 'School ERP') . ' — SMTP Configuration OK';
            $body = "Hello,\n\n"
                  . "This is a test email sent from your School ERP system using the SMTP credentials configured for:\n\n"
                  . "  Email: {$inboxSetting->email_address}\n"
                  . "  SMTP Host: {$host}:{$port}\n"
                  . "  Encryption: {$encryption}\n"
                  . "  Default Sender: " . ($inboxSetting->is_default_sender ? 'Yes' : 'No') . "\n\n"
                  . "If you received this email, your SMTP configuration is working correctly. "
                  . "Database backup emails and system notifications will be sent from this account.\n\n"
                  . "Time sent: " . now()->toDateTimeString() . "\n";

            \Illuminate\Support\Facades\Mail::raw($body, function ($message) use ($testEmail, $subject) {
                $message->to($testEmail)->subject($subject);
            });

            return back()->with('success', "Test email sent successfully to {$testEmail} via {$host}:{$port}. Check the inbox (and spam folder) to confirm receipt.");
        } catch (\Exception $e) {
            $hint = '';
            $msg = $e->getMessage();
            if (str_contains($msg, 'Username and Password not accepted') || str_contains($msg, 'authentication')) {
                $hint = ' — Gmail rejected the credentials. Make sure you are using a Gmail App Password (16 chars), not your regular Gmail password.';
            } elseif (str_contains($msg, 'Connection refused') || str_contains($msg, 'timed out')) {
                $hint = ' — Could not reach SMTP server. Check host/port/encryption. Gmail uses smtp.gmail.com:465 (SSL) or :587 (TLS).';
            }
            return back()->with('error', 'Test email failed: ' . $msg . $hint);
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
