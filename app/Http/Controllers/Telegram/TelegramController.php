<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramSetting;
use App\Models\TelegramMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramController extends Controller
{
    public function index()
    {
        $settings = TelegramSetting::getSettings();
        $messages = TelegramMessage::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.telegram.index', compact('settings', 'messages'));
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

    public function send()
    {
        $settings = TelegramSetting::getSettings();
        $recentChats = TelegramMessage::select('chat_id', 'from_name')
            ->groupBy('chat_id', 'from_name')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
        return view('admin.telegram.send', compact('settings', 'recentChats'));
    }

    public function sendMessage(Request $r)
    {
        $r->validate([
            'chat_id' => 'required|string|max:200',
            'message' => 'required|string|max:4096',
        ]);

        $settings = TelegramSetting::getSettings();

        if (!$settings->is_enabled || !$settings->bot_token) {
            return redirect()->back()->with('error', 'Telegram is not configured. Please set up bot token first.')->withInput();
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$settings->bot_token}/sendMessage", [
                'chat_id' => $r->chat_id,
                'text' => $r->message,
                'parse_mode' => 'HTML',
            ]);

            $data = $response->json();

            if ($data['ok'] ?? false) {
                TelegramMessage::create([
                    'chat_id' => $r->chat_id,
                    'message' => $r->message,
                    'direction' => 'outgoing',
                    'status' => 'sent',
                ]);
                return redirect()->route('admin.telegram.send')->with('success', 'Message sent successfully.');
            } else {
                TelegramMessage::create([
                    'chat_id' => $r->chat_id,
                    'message' => $r->message,
                    'direction' => 'outgoing',
                    'status' => 'failed',
                ]);
                return redirect()->back()->with('error', 'Failed to send: ' . ($data['description'] ?? 'Unknown error'))->withInput();
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Connection error: ' . $e->getMessage())->withInput();
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
