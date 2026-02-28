<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Notification;
use App\Models\SmsTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Notify about car status change.
     */
    public function notifyStatusChange(Car $car, ?string $oldStatus, string $newStatus): void
    {
        // Get notification message from template
        $template = SmsTemplate::getByStatus($newStatus);
        $message = $template ? $template->parseForCar($car) : $this->getDefaultMessage($car, $newStatus);

        // Create in-app notification for client user
        if ($car->client_user_id) {
            $this->createNotification($car->client_user_id, $message, $car->id);
        }

        // Create in-app notification for dealer
        if ($car->user_id && $car->user_id !== $car->client_user_id) {
            $this->createNotification($car->user_id, $message, $car->id);
        }

        // Send SMS
        $this->smsService->sendForStatusChange($car, $newStatus);
    }

    /**
     * Create a notification.
     */
    public function createNotification(int $userId, string $message, ?int $carId = null): Notification
    {
        return Notification::createForUser($userId, $message, $carId);
    }

    /**
     * Send notification to multiple users.
     */
    public function notifyMultipleUsers(array $userIds, string $message, ?int $carId = null): int
    {
        $count = 0;
        
        foreach ($userIds as $userId) {
            $this->createNotification($userId, $message, $carId);
            $count++;
        }
        
        return $count;
    }

    /**
     * Notify all admins.
     */
    public function notifyAdmins(string $message, ?int $carId = null): int
    {
        $adminIds = User::admins()->pluck('id')->toArray();
        return $this->notifyMultipleUsers($adminIds, $message, $carId);
    }

    /**
     * Notify user about car payment.
     */
    public function notifyPayment(Car $car, float $amount): void
    {
        $message = sprintf(
            'გადახდა: %s %d - %s - $%.2f',
            $car->make_model,
            $car->year,
            $car->vin,
            $amount
        );

        if ($car->client_user_id) {
            $this->createNotification($car->client_user_id, $message, $car->id);
        }

        if ($car->user_id) {
            $this->createNotification($car->user_id, $message, $car->id);
        }
    }

    /**
     * Get default message for status change.
     */
    protected function getDefaultMessage(Car $car, string $status): string
    {
        $statusLabels = [
            'purchased' => 'შეძენილია',
            'warehouse' => 'საწყობშია',
            'loaded' => 'ჩატვირთულია',
            'on_way' => 'გზაშია',
            'poti' => 'ფოთშია',
            'green' => 'მწვანეშია',
            'delivered' => 'გაყვანილია',
        ];

        $statusLabel = $statusLabels[$status] ?? $status;

        return sprintf(
            '%s %d - %s: %s',
            $car->make_model,
            $car->year ?? '',
            $car->vin,
            $statusLabel
        );
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllReadForUser(int $userId): int
    {
        return Notification::markAllReadForUser($userId);
    }

    /**
     * Get unread count for user.
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Delete old notifications.
     */
    public function deleteOldNotifications(int $days = 90): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))
            ->where('is_read', true)
            ->delete();
    }
}
