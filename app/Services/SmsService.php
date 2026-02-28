<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Models\SmsTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $apiUrl = 'https://smsoffice.ge/api/v2/send/'; // POST requires trailing slash
    protected ?string $apiKey;
    protected string $sender;

    public function __construct()
    {
        // Use provided API key or fallback to setting
        $this->apiKey = 'aad8d93a80bb4782ae8bd95bfad2b234';
        $this->sender = Setting::get('sms_sender', 'smsoffice');
    }
    
    /**
     * Get SMS balance from API.
     */
    public function getBalance(): ?int
    {
        if (empty($this->apiKey)) {
            return null;
        }
        
        try {
            $response = Http::timeout(10)->get('https://smsoffice.ge/api/getBalance', [
                'key' => $this->apiKey,
            ]);
            
            if ($response->successful()) {
                $body = $response->body();
                // API returns the balance as a number
                $balance = (int) trim($body);
                return $balance > 0 ? $balance : 0;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('SMS: Failed to get balance', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Send SMS to a phone number.
     */
    public function send(string $phone, string $message): bool
    {
        // Format phone number
        $phone = $this->formatPhone($phone);
        
        if (empty($phone)) {
            Log::warning('SMS: Invalid phone number');
            return false;
        }

        // Check if API key is configured
        if (empty($this->apiKey)) {
            Log::warning('SMS: API key not configured');
            // Still log the SMS for testing/debugging
            SmsLog::log($phone, $message, 'failed', null);
            return false;
        }

        try {
            $referenceId = 'sms_' . uniqid();
            
            // Use POST method for better reliability (especially for long messages)
            // Use POST method with form data (as per API documentation)
            $response = Http::timeout(10)->asForm()->post($this->apiUrl, [
                'key' => $this->apiKey,
                'destination' => $phone,
                'sender' => $this->sender,
                'content' => $message,
                'urgent' => 'true',
            ]);

            // Parse JSON response
            $responseData = $response->json();
            $success = false;
            
            // Check API response format: { "Success": boolean, "Message": string, "ErrorCode": integer }
            if ($responseData && isset($responseData['Success'])) {
                $success = $responseData['Success'] === true || $responseData['ErrorCode'] == 0;
            } else {
                // Fallback: check HTTP status
                $success = $response->successful();
            }
            
            // Log the SMS
            SmsLog::log(
                $phone,
                $message,
                $success ? 'sent' : 'failed',
                $referenceId
            );

            if (!$success) {
                Log::error('SMS: Failed to send', [
                    'phone' => $phone,
                    'response' => $response->body(),
                    'error_code' => $responseData['ErrorCode'] ?? null,
                    'error_message' => $responseData['Message'] ?? null,
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            Log::error('SMS: Exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            SmsLog::log($phone, $message, 'failed', null);
            return false;
        }
    }

    /**
     * Send SMS for car status change.
     */
    public function sendForStatusChange(Car $car, string $newStatus): bool
    {
        // Get template for this status
        $template = SmsTemplate::getByStatus($newStatus);
        
        if (!$template) {
            Log::info('SMS: No template for status', ['status' => $newStatus]);
            return false;
        }

        // Get recipient phone number
        $phone = $this->getRecipientPhone($car);
        
        if (empty($phone)) {
            Log::info('SMS: No phone number for car', ['car_id' => $car->id]);
            return false;
        }

        // Check if user has SMS enabled
        if (!$this->shouldSendSms($car)) {
            Log::info('SMS: SMS disabled for user', ['car_id' => $car->id]);
            return false;
        }

        // Parse template with car data
        $message = $template->parseForCar($car);

        return $this->send($phone, $message);
    }

    /**
     * Send SMS to user.
     */
    public function sendToUser(User $user, string $message): bool
    {
        if (empty($user->phone)) {
            return false;
        }

        if (!$user->sms_enabled) {
            return false;
        }

        return $this->send($user->phone, $message);
    }

    /**
     * Send bulk SMS to multiple users.
     */
    public function sendBulk(array $userIds, string $message): array
    {
        $results = ['sent' => 0, 'failed' => 0];

        $users = User::whereIn('id', $userIds)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->where('sms_enabled', true)
            ->get();

        foreach ($users as $user) {
            if ($this->send($user->phone, $message)) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Format phone number to international format.
     */
    protected function formatPhone(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Handle Georgian numbers
        if (str_starts_with($phone, '5') && strlen($phone) === 9) {
            $phone = '995' . $phone;
        }
        
        // Add Georgia country code if missing
        if (strlen($phone) === 9 && !str_starts_with($phone, '995')) {
            $phone = '995' . $phone;
        }

        // Validate length
        if (strlen($phone) < 10 || strlen($phone) > 15) {
            return '';
        }

        return $phone;
    }

    /**
     * Get recipient phone number from car.
     */
    protected function getRecipientPhone(Car $car): string
    {
        // Priority: client user -> client phone -> dealer phone
        if ($car->client && !empty($car->client->phone)) {
            return $car->client->phone;
        }

        if (!empty($car->client_phone)) {
            return $car->client_phone;
        }

        if ($car->user && !empty($car->user->phone)) {
            return $car->user->phone;
        }

        return '';
    }

    /**
     * Check if SMS should be sent for this car.
     */
    protected function shouldSendSms(Car $car): bool
    {
        // Check client user first
        if ($car->client) {
            return $car->client->sms_enabled;
        }

        // Check dealer
        if ($car->user) {
            return $car->user->sms_enabled;
        }

        // If no associated users, check if manual phone is provided
        return !empty($car->client_phone);
    }
}
