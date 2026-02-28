<?php

namespace App\Services;

use App\Models\Car;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class CarService
{
    public function __construct(
        protected NotificationService $notificationService,
        protected FileUploadService $fileUploadService,
        protected SmsService $smsService
    ) {
    }

    /**
     * Create a new car.
     */
    public function createCar(array $data, ?array $photos = []): Car
    {
        return DB::transaction(function () use ($data, $photos) {
            // Prepare data
            $data = $this->prepareCarData($data);

            // Set user_id if not present (defaults to auth user)
            if (!isset($data['user_id'])) {
                $data['user_id'] = auth()->id();
            }

            // Create car
            $car = Car::create($data);

            // Upload photos
            if (!empty($photos)) {
                $category = $data['photo_category'] ?? 'auction';
                $this->fileUploadService->uploadCarPhotos($car, $photos, $category);
            }

            // Send notification if status is 'purchased'
            if ($car->status === Car::STATUS_PURCHASED) {
                try {
                    $this->notificationService->notifyStatusChange($car, null, Car::STATUS_PURCHASED);
                } catch (\Exception $e) {
                    \Log::error('Notification failed for car create', ['car_id' => $car->id, 'error' => $e->getMessage()]);
                }
            }

            return $car;
        });
    }

    /**
     * Update an existing car.
     */
    public function updateCar(Car $car, array $data, ?array $photos = []): Car
    {
        return DB::transaction(function () use ($car, $data, $photos) {
            $oldStatus = $car->status;

            // Prepare data
            $data = $this->prepareCarData($data);

            $car->update($data);

            // Upload photos
            if (!empty($photos)) {
                $category = $data['photo_category'] ?? 'auction';
                $this->fileUploadService->uploadCarPhotos($car, $photos, $category);
            }

            // Notify if status changed
            if ($oldStatus !== $car->status) {
                try {
                    $this->notificationService->notifyStatusChange($car, $oldStatus, $car->status);
                } catch (\Exception $e) {
                    \Log::error('Notification failed for car update', ['car_id' => $car->id, 'error' => $e->getMessage()]);
                }
            }

            return $car;
        });
    }

    /**
     * Delete a car.
     */
    public function deleteCar(Car $car): void
    {
        DB::transaction(function () use ($car) {
            // Delete all files
            $this->fileUploadService->deleteAllCarFiles($car);

            // Delete car
            $car->delete();
        });
    }

    /**
     * Update car status.
     */
    public function updateStatus(Car $car, string $newStatus): void
    {
        $oldStatus = $car->status;

        if ($oldStatus === $newStatus) {
            return;
        }

        $car->status = $newStatus;
        $car->save();

        $this->notificationService->notifyStatusChange($car, $oldStatus, $newStatus);
    }

    /**
     * Update recipient details.
     */
    public function updateRecipient(Car $car, array $data): Car
    {
        $car->update([
            'client_name' => $data['client_name'] ?? null,
            'client_phone' => $data['client_phone'] ?? null,
            'client_id_number' => $data['client_id_number'] ?? null,
            'client_user_id' => $data['client_user_id'] ?? null,
        ]);

        return $car;
    }

    /**
     * Prepare car data for storage.
     */
    protected function prepareCarData(array $data): array
    {
        // Backward compatibility for make_model
        if (!empty($data['make']) && !empty($data['model'])) {
            $data['make_model'] = trim($data['make']) . ' ' . trim($data['model']);
        }

        // Default financial values
        $financialFields = [
            'dealer_profit',
            'discount',
            'additional_cost',
            'transfer_commission',
            'paid_amount'
        ];

        foreach ($financialFields as $field) {
            $data[$field] = $data[$field] ?? 0;
        }

        return $data;
    }
}
