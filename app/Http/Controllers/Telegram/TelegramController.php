<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramSetting;
use App\Models\TelegramMessage;
use App\Models\Branch;
use App\Models\BranchTelegramSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramController extends Controller
{
    public function index()
    {
        $settings = TelegramSetting::getSettings();
        $messages = TelegramMessage::orderBy('created_at', 'desc')->paginate(30);
        $branches = Branch::orderBy('name')->get();
        $branchSettings = [];
        foreach ($branches as $branch) {
            $branchSettings[$branch->id] = BranchTelegramSetting::getOrCreateForBranch($branch->id);
        }

        return view('admin.telegram.index', compact('settings', 'messages', 'branches', 'branchSettings'));
    }

    public function updateSettings(Request $r)
    {
        $r->validate([
            'bot_token' => 'nullable|string|max:500',
            'chat_id' => 'nullable|string|max:200',
            'webhook_url' => 'nullable|url|max:500',
            'is_enabled' => 'nullable|boolean',
            'welcome_message' => 'nullable|string|max:1000',
        ]);

        $settings = TelegramSetting::getSettings();
        $settings->update($r->only(['bot_token', 'chat_id', 'webhook_url', 'is_enabled', 'welcome_message']));

        return redirect()->route('admin.telegram.index')->with('success', 'Telegram settings updated.');
    }

    /**
     * Update branch-specific Telegram settings.
     */
    public function updateBranchSettings(Request $r)
    {
        $r->validate([
            'branch_id' => 'required|exists:branches,id',
            'bot_token' => 'nullable|string|max:500',
            'chat_id' => 'nullable|string|max:200',
            'is_enabled' => 'nullable|boolean',
            'welcome_message' => 'nullable|string|max:1000',
        ]);

        $branchSetting = BranchTelegramSetting::getOrCreateForBranch($r->branch_id);
        $branchSetting->update($r->only(['bot_token', 'chat_id', 'is_enabled', 'welcome_message']));

        return redirect()->route('admin.telegram.index')->with('success', 'Branch Telegram settings updated.');
    }

    public function send()
    {
        $settings = TelegramSetting::getSettings();
        $branches = Branch::orderBy('name')->get();
        $recentChats = TelegramMessage::select('chat_id', 'from_name')
            ->groupBy('chat_id', 'from_name')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('admin.telegram.send', compact('settings', 'recentChats', 'branches'));
    }

    public function sendMessage(Request $r)
    {
        $r->validate([
            'chat_id' => 'required_without:branch_id|string|max:200|nullable',
            'branch_id' => 'nullable|exists:branches,id',
            'message' => 'required|string|max:4096',
            'send_to_all_branches' => 'nullable|boolean',
        ]);

        // Determine targets
        $targets = [];

        if ($r->filled('branch_id') || $r->boolean('send_to_all_branches')) {
            // Branch-based messaging
            if ($r->boolean('send_to_all_branches')) {
                $branchSettings = BranchTelegramSetting::where('is_enabled', true)
                    ->whereNotNull('bot_token')
                    ->whereNotNull('chat_id')
                    ->get();
                foreach ($branchSettings as $bs) {
                    $targets[] = [
                        'bot_token' => $bs->bot_token,
                        'chat_id'   => $bs->chat_id,
                        'branch'    => $bs->branch->name ?? 'Unknown',
                    ];
                }
            } else {
                $bs = BranchTelegramSetting::getForBranch($r->branch_id);
                if ($bs && $bs->is_enabled && $bs->bot_token && $bs->chat_id) {
                    $targets[] = [
                        'bot_token' => $bs->bot_token,
                        'chat_id'   => $bs->chat_id,
                        'branch'    => $bs->branch->name ?? 'Unknown',
                    ];
                } else {
                    return redirect()->back()->with('error', 'Telegram is not configured for this branch. Please set up bot token and chat ID first.')->withInput();
                }
            }
        } else {
            // Direct chat ID messaging
            $settings = TelegramSetting::getSettings();
            if (!$settings->is_enabled || !$settings->bot_token) {
                return redirect()->back()->with('error', 'Telegram is not configured. Please set up bot token first.')->withInput();
            }
            $targets[] = [
                'bot_token' => $settings->bot_token,
                'chat_id'   => $r->chat_id,
                'branch'    => 'Global',
            ];
        }

        $sentCount = 0;
        $errors = [];

        foreach ($targets as $target) {
            try {
                $response = Http::post("https://api.telegram.org/bot{$target['bot_token']}/sendMessage", [
                    'chat_id' => $target['chat_id'],
                    'text' => $r->message,
                    'parse_mode' => 'HTML',
                ]);

                $data = $response->json();

                if ($data['ok'] ?? false) {
                    TelegramMessage::create([
                        'chat_id' => $target['chat_id'],
                        'message' => $r->message,
                        'direction' => 'outgoing',
                        'status' => 'sent',
                    ]);
                    $sentCount++;
                } else {
                    TelegramMessage::create([
                        'chat_id' => $target['chat_id'],
                        'message' => $r->message,
                        'direction' => 'outgoing',
                        'status' => 'failed',
                    ]);
                    $errors[] = "Branch {$target['branch']}: " . ($data['description'] ?? 'Unknown error');
                }
            } catch (\Exception $e) {
                $errors[] = "Branch {$target['branch']}: " . $e->getMessage();
            }
        }

        if ($sentCount > 0 && empty($errors)) {
            return redirect()->route('admin.telegram.send')->with('success', "Message sent to {$sentCount} target(s) successfully.");
        } elseif ($sentCount > 0 && !empty($errors)) {
            return redirect()->route('admin.telegram.send')->with('success', "Message sent to {$sentCount} target(s), but some failed: " . implode('; ', $errors));
        } else {
            return redirect()->back()->with('error', 'Failed to send: ' . implode('; ', $errors))->withInput();
        }
    }

    public function webhook(Request $r)
    {
        $settings = TelegramSetting::getSettings();
        if (!$settings->is_enabled) return response('Disabled', 403);

        $update = $r->all();
        $msg = $update['message'] ?? null;

        if ($msg) {
            TelegramMessage::create([
                'chat_id' => (string) ($msg['chat']['id'] ?? ''),
                'from_id' => (string) ($msg['from']['id'] ?? ''),
                'from_name' => $msg['from']['first_name'] ?? ($msg['from']['username'] ?? 'Unknown'),
                'message' => $msg['text'] ?? '',
                'direction' => 'incoming',
                'status' => 'delivered',
            ]);
        }

        return response('OK', 200);
    }

    public function testConnection()
    {
        $settings = TelegramSetting::getSettings();
        if (!$settings->bot_token) {
            return response()->json(['success' => false, 'message' => 'No bot token configured']);
        }

        try {
            $response = Http::get("https://api.telegram.org/bot{$settings->bot_token}/getMe");
            $data = $response->json();

            if ($data['ok'] ?? false) {
                return response()->json(['success' => true, 'bot_name' => $data['result']['username'] ?? 'Unknown']);
            }
            return response()->json(['success' => false, 'message' => $data['description'] ?? 'Invalid token']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
