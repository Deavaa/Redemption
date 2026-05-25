<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SMS Notification Service
 *
 * Supports multiple SMS gateways commonly used in Africa:
 * - Africa's Talking (recommended for Ethiopia)
 * - Twilio
 * - Custom HTTP API
 *
 * Configure in .env:
 *   SMS_DRIVER=africas_talking|twilio|custom|log
 *   SMS_API_KEY=...
 *   SMS_API_URL=... (for custom)
 *   SMS_SENDER_ID=... (sender name/number)
 *   SMS_RECIPIENTS=... (comma-separated default numbers)
 */
class SmsService
{
    private string $driver;
    private ?string $apiKey;
    private ?string $apiUrl;
    private ?string $username;
    private ?string $senderId;
    private ?string $recipients;

    public function __construct()
    {
        $this->driver = config('services.sms.driver', env('SMS_DRIVER', 'log'));
        $this->apiKey = config('services.sms.api_key', env('SMS_API_KEY'));
        $this->apiUrl = config('services.sms.api_url', env('SMS_API_URL'));
        $this->username = config('services.sms.username', env('SMS_USERNAME'));
        $this->senderId = config('services.sms.sender_id', env('SMS_SENDER_ID', 'Redemption'));
        $this->recipients = config('services.sms.recipients', env('SMS_RECIPIENTS'));
    }

    /**
     * Send an SMS message to one or more recipients.
     *
     * @param string|array $to Phone number(s)
     * @param string $message The SMS text
     * @return array ['success' => bool, 'message' => string, 'recipients' => int]
     */
    public function send(string|array $to, string $message): array
    {
        if (is_string($to)) {
            $to = array_map('trim', explode(',', $to));
        }

        // Filter out empty numbers
        $to = array_filter($to, fn($n) => !empty($n));

        if (empty($to)) {
            return ['success' => false, 'message' => 'No recipients provided', 'recipients' => 0];
        }

        return match ($this->driver) {
            'africas_talking' => $this->sendViaAfricasTalking($to, $message),
            'twilio' => $this->sendViaTwilio($to, $message),
            'custom' => $this->sendViaCustom($to, $message),
            'log' => $this->sendViaLog($to, $message),
            default => $this->sendViaLog($to, $message),
        };
    }

    /**
     * Send SMS to default recipients configured in .env.
     */
    public function sendToDefaults(string $message): array
    {
        if (empty($this->recipients)) {
            return ['success' => false, 'message' => 'No default SMS recipients configured', 'recipients' => 0];
        }

        return $this->send($this->recipients, $message);
    }

    /**
     * Send SMS via Africa's Talking API.
     * https://africastalking.com/
     */
    private function sendViaAfricasTalking(array $to, string $message): array
    {
        try {
            $phoneNumbers = implode(',', array_map(fn($n) => $this->formatPhone($n), $to));

            $response = Http::asForm()->post('https://api.africastalking.com/v1/messaging', [
                'username' => $this->username ?? 'sandbox',
                'to' => $phoneNumbers,
                'message' => $message,
                'from' => $this->senderId,
            ]);

            $data = $response->json();

            if (isset($data['SMSMessageData']['Recipients'])) {
                $recipients = $data['SMSMessageData']['Recipients'];
                $successCount = count(array_filter($recipients, fn($r) => ($r['statusCode'] ?? '') === '101'));

                Log::info('Africa\'s Talking SMS sent', [
                    'total' => count($recipients),
                    'successful' => $successCount,
                ]);

                return [
                    'success' => $successCount > 0,
                    'message' => "SMS sent to {$successCount}/" . count($recipients) . " recipients",
                    'recipients' => $successCount,
                ];
            }

            return ['success' => false, 'message' => $data['errorMessage'] ?? 'Unknown error', 'recipients' => 0];
        } catch (\Exception $e) {
            Log::error('Africa\'s Talking SMS failed: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'recipients' => 0];
        }
    }

    /**
     * Send SMS via Twilio API.
     * https://www.twilio.com/
     */
    private function sendViaTwilio(array $to, string $message): array
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $from = env('TWILIO_PHONE_NUMBER', $this->senderId);

        if (!$sid || !$token) {
            return ['success' => false, 'message' => 'Twilio credentials not configured', 'recipients' => 0];
        }

        $sent = 0;
        foreach ($to as $number) {
            try {
                $response = Http::withBasicAuth($sid, $token)
                    ->asForm()
                    ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                        'To' => $this->formatPhone($number),
                        'From' => $from,
                        'Body' => $message,
                    ]);

                if ($response->successful()) {
                    $sent++;
                }
            } catch (\Exception $e) {
                Log::warning("Twilio SMS failed for {$number}: " . $e->getMessage());
            }
        }

        return [
            'success' => $sent > 0,
            'message' => "SMS sent to {$sent}/" . count($to) . " recipients",
            'recipients' => $sent,
        ];
    }

    /**
     * Send SMS via a custom HTTP API.
     * Expects a POST endpoint that accepts: to, message, from, api_key
     */
    private function sendViaCustom(array $to, string $message): array
    {
        if (!$this->apiUrl) {
            return ['success' => false, 'message' => 'Custom SMS API URL not configured', 'recipients' => 0];
        }

        $sent = 0;
        foreach ($to as $number) {
            try {
                $response = Http::post($this->apiUrl, [
                    'to' => $this->formatPhone($number),
                    'message' => $message,
                    'from' => $this->senderId,
                    'api_key' => $this->apiKey,
                ]);

                if ($response->successful()) {
                    $sent++;
                }
            } catch (\Exception $e) {
                Log::warning("Custom SMS failed for {$number}: " . $e->getMessage());
            }
        }

        return [
            'success' => $sent > 0,
            'message' => "SMS sent to {$sent}/" . count($to) . " recipients",
            'recipients' => $sent,
        ];
    }

    /**
     * Log SMS instead of sending (for development/testing).
     */
    private function sendViaLog(array $to, string $message): array
    {
        foreach ($to as $number) {
            Log::info("[SMS] To: {$number} | Message: {$message}");
        }

        return [
            'success' => true,
            'message' => 'SMS logged (SMS_DRIVER=log). ' . count($to) . ' recipients.',
            'recipients' => count($to),
        ];
    }

    /**
     * Format phone number to international format.
     * Converts Ethiopian numbers: 0911... → +251911...
     */
    private function formatPhone(string $number): string
    {
        $number = preg_replace('/[^0-9+]/', '', $number);

        // Ethiopian number format: 09xx → +2519xx
        if (preg_match('/^0(9\d{8})$/', $number, $m)) {
            return '+251' . $m[1];
        }

        // Ethiopian number format: 2519... → +2519...
        if (preg_match('/^251(\d{9})$/', $number, $m)) {
            return '+251' . $m[1];
        }

        // Already has country code
        if (str_starts_with($number, '+')) {
            return $number;
        }

        // Add + prefix if it looks like a country code
        if (preg_match('/^\d{10,15}$/', $number)) {
            return '+' . $number;
        }

        return $number;
    }

    /**
     * Check if SMS is properly configured.
     */
    public function isConfigured(): bool
    {
        return $this->driver !== 'log' && !empty($this->apiKey);
    }
}
