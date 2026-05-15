<?php
namespace App\Http\Controllers\CalendarEvent;
use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\BranchTelegramSetting;
use App\Models\TelegramSetting;
use App\Models\TelegramMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = CalendarEvent::where('is_announcement', true)
            ->orderBy('start_date', 'desc')
            ->paginate(20);
        $pendingAnnouncements = CalendarEvent::where('is_announcement', true)
            ->where('start_date', '>=', now())
            ->orderBy('start_date')->get();
        return view('admin.announcements.index', compact('announcements', 'pendingAnnouncements'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_date' => 'required|date',
            'category' => 'required|in:event,holiday,deadline,meeting,other',
            'send_telegram' => 'nullable|boolean',
        ]);

        $event = CalendarEvent::create([
            'title' => $r->title,
            'description' => $r->description,
            'category' => $r->category,
            'start_date' => $r->start_date,
            'is_all_day' => true,
            'is_announcement' => true,
            'color' => CalendarEvent::categoryColors()[$r->category] ?? '#4361ee',
        ]);

        if ($r->boolean('send_telegram')) {
            $this->sendToTelegram($event->id);
        }

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement created' . ($r->boolean('send_telegram') ? ' and sent to Telegram' : '') . '.');
    }

    public function sendToTelegram($id)
    {
        $event = CalendarEvent::findOrFail($id);
        $message = "📢 *{$event->title}*\n\n";
        if ($event->description) $message .= $event->description . "\n\n";
        $message .= "📅 " . $event->start_date->format('M d, Y');
        if ($event->category) $message .= "\n🏷 " . ucfirst($event->category);

        $sentCount = 0;
        $errors = [];

        // Send to global bot
        $globalSettings = TelegramSetting::getSettings();
        if ($globalSettings && $globalSettings->is_enabled && $globalSettings->bot_token && $globalSettings->chat_id) {
            try {
                $response = Http::post("https://api.telegram.org/bot{$globalSettings->bot_token}/sendMessage", [
                    'chat_id' => $globalSettings->chat_id,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                ]);
                $data = $response->json();
                if ($data['ok'] ?? false) {
                    TelegramMessage::create(['chat_id' => $globalSettings->chat_id, 'message' => $message, 'direction' => 'outgoing', 'status' => 'sent']);
                    $sentCount++;
                } else {
                    $errors[] = 'Global: ' . ($data['description'] ?? 'Unknown error');
                }
            } catch (\Exception $e) {
                $errors[] = 'Global: ' . $e->getMessage();
            }
        }

        // Send to all branch bots
        $branchSettings = BranchTelegramSetting::where('is_enabled', true)->whereNotNull('bot_token')->whereNotNull('chat_id')->get();
        foreach ($branchSettings as $bs) {
            try {
                $response = Http::post("https://api.telegram.org/bot{$bs->bot_token}/sendMessage", [
                    'chat_id' => $bs->chat_id,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                ]);
                $data = $response->json();
                if ($data['ok'] ?? false) {
                    TelegramMessage::create(['chat_id' => $bs->chat_id, 'message' => $message, 'direction' => 'outgoing', 'status' => 'sent']);
                    $sentCount++;
                } else {
                    $errors[] = "Branch {$bs->branch->name}: " . ($data['description'] ?? 'Unknown error');
                }
            } catch (\Exception $e) {
                $errors[] = "Branch {$bs->branch->name}: " . $e->getMessage();
            }
        }

        if ($sentCount > 0) {
            return redirect()->back()->with('success', "Announcement sent to {$sentCount} Telegram target(s)." . (!empty($errors) ? ' Errors: ' . implode('; ', $errors) : ''));
        }
        return redirect()->back()->with('error', 'Failed to send announcement: ' . implode('; ', $errors));
    }

    public function destroy($id)
    {
        CalendarEvent::findOrFail($id)->delete();
        return back()->with('success', 'Announcement deleted.');
    }
}
