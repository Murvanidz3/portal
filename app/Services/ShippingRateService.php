<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserShippingFile;
use App\Models\UserShippingRate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class ShippingRateService
{
    /**
     * Upload and process Excel file for a user.
     *
     * @param User $user The user to assign rates to
     * @param UploadedFile $file The uploaded Excel file
     * @param int $uploadedBy Admin user ID who uploaded
     * @return array Result with success status and message
     */
    public function uploadAndProcess(User $user, UploadedFile $file, int $uploadedBy): array
    {
        try {
            // Validate file type - CSV only
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['csv', 'txt'])) {
                return [
                    'success' => false,
                    'message' => 'არასწორი ფაილის ფორმატი. დასაშვებია მხოლოდ CSV.',
                ];
            }

            // Store the file
            $filename = 'shipping_rates_' . $user->id . '_' . time() . '.' . $extension;
            $path = $file->storeAs('shipping-rates/' . $user->id, $filename, 'public');

            if (!$path) {
                return [
                    'success' => false,
                    'message' => 'ფაილის შენახვა ვერ მოხერხდა.',
                ];
            }

            // Create file record
            $shippingFile = UserShippingFile::create([
                'user_id' => $user->id,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'uploaded_at' => now(),
                'uploaded_by' => $uploadedBy,
                'is_active' => true,
            ]);

            // Parse the Excel file
            $parseResult = $this->parseExcelFile($shippingFile);

            if (!$parseResult['success']) {
                // Delete the file if parsing failed
                $shippingFile->delete();
                Storage::disk('public')->delete($path);
                return $parseResult;
            }

            // Deactivate other files for this user
            $shippingFile->activateExclusive();

            return [
                'success' => true,
                'message' => 'ფაილი წარმატებით აიტვირთა და დამუშავდა.',
                'file_id' => $shippingFile->id,
                'rates_count' => $parseResult['rates_count'],
            ];

        } catch (\Exception $e) {
            Log::error('Shipping rate upload error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'შეცდომა: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Parse CSV file and extract shipping rates.
     */
    protected function parseExcelFile(UserShippingFile $shippingFile): array
    {
        try {
            $fullPath = Storage::disk('public')->path($shippingFile->file_path);

            if (!file_exists($fullPath)) {
                return [
                    'success' => false,
                    'message' => 'ფაილი ვერ მოიძებნა.',
                ];
            }

            // Parse CSV file
            $rows = $this->parseCsvFile($fullPath);

            if (count($rows) < 2) {
                return [
                    'success' => false,
                    'message' => 'ფაილი ცარიელია ან არასწორი ფორმატისაა.',
                ];
            }

            // Delete existing rates for this file
            UserShippingRate::where('shipping_file_id', $shippingFile->id)->delete();

            $rates = [];
            $ratesCount = 0;

            // Skip header row (first row)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                // Column A: COPART LOCATIONS
                // Column B: COPART PRICES
                // Column C: IAAI LOCATIONS
                // Column D: IAAI PRICES

                // Process COPART (columns A,B - indexes 0,1)
                if (!empty($row[0]) && isset($row[1]) && is_numeric($this->cleanPrice($row[1]))) {
                    $locationName = trim($row[0]);
                    $price = $this->cleanPrice($row[1]);

                    if ($locationName && $price > 0) {
                        $rates[] = [
                            'user_id' => $shippingFile->user_id,
                            'shipping_file_id' => $shippingFile->id,
                            'auction_type' => UserShippingRate::AUCTION_COPART,
                            'location_name' => $locationName,
                            'location_normalized' => UserShippingRate::normalizeLocation($locationName),
                            'price' => $price,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $ratesCount++;
                    }
                }

                // Process IAAI (columns C,D - indexes 2,3)
                if (!empty($row[2]) && isset($row[3]) && is_numeric($this->cleanPrice($row[3]))) {
                    $locationName = trim($row[2]);
                    $price = $this->cleanPrice($row[3]);

                    if ($locationName && $price > 0) {
                        $rates[] = [
                            'user_id' => $shippingFile->user_id,
                            'shipping_file_id' => $shippingFile->id,
                            'auction_type' => UserShippingRate::AUCTION_IAAI,
                            'location_name' => $locationName,
                            'location_normalized' => UserShippingRate::normalizeLocation($locationName),
                            'price' => $price,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $ratesCount++;
                    }
                }
            }

            if (count($rates) === 0) {
                return [
                    'success' => false,
                    'message' => 'ფაილში ვერ მოიძებნა ვალიდური ტარიფები.',
                ];
            }

            // Insert rates in chunks
            foreach (array_chunk($rates, 100) as $chunk) {
                DB::table('user_shipping_rates')->insert($chunk);
            }

            return [
                'success' => true,
                'rates_count' => $ratesCount,
            ];

        } catch (\Exception $e) {
            Log::error('Excel parsing error', [
                'file_id' => $shippingFile->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'ფაილის დამუშავება ვერ მოხერხდა: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Parse CSV file using native PHP functions.
     */
    protected function parseCsvFile(string $filePath): array
    {
        $rows = [];

        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        }

        // If no rows parsed with comma, try semicolon
        if (count($rows) <= 1) {
            $rows = [];
            if (($handle = fopen($filePath, 'r')) !== false) {
                while (($data = fgetcsv($handle, 0, ';')) !== false) {
                    $rows[] = $data;
                }
                fclose($handle);
            }
        }

        return $rows;
    }

    /**
     * Clean price value from Excel.
     */
    protected function cleanPrice($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Remove non-numeric characters except dot
        $value = preg_replace('/[^0-9.]/', '', (string) $value);

        return is_numeric($value) ? (float) $value : 0;
    }

    /**
     * Get shipping rate for a user and location.
     */
    public function getRate(int $userId, string $auctionType, string $location): ?float
    {
        return UserShippingRate::findRate($userId, $auctionType, $location);
    }

    /**
     * Get all rates for a user.
     */
    public function getUserRates(int $userId): array
    {
        return UserShippingRate::getUserRates($userId);
    }

    /**
     * Check if user has custom rates.
     */
    public function hasCustomRates(int $userId): bool
    {
        return UserShippingFile::forUser($userId)->active()->exists();
    }

    /**
     * Get active shipping file for user.
     */
    public function getActiveFile(int $userId): ?UserShippingFile
    {
        return UserShippingFile::forUser($userId)->active()->first();
    }

    /**
     * Delete shipping file and its rates.
     */
    public function deleteFile(int $fileId): bool
    {
        $file = UserShippingFile::find($fileId);

        if (!$file) {
            return false;
        }

        // Delete physical file
        if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        // Delete rates (cascade will handle this, but explicit for clarity)
        $file->rates()->delete();

        // Delete file record
        $file->delete();

        return true;
    }

    /**
     * Search location in user's rates.
     */
    public function searchLocation(int $userId, string $query): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        $normalized = UserShippingRate::normalizeLocation($query);

        return UserShippingRate::forUser($userId)
            ->active()
            ->where(function ($q) use ($normalized, $query) {
                $q->where('location_normalized', 'LIKE', '%' . $normalized . '%')
                    ->orWhere('location_name', 'LIKE', '%' . $query . '%');
            })
            ->orderBy('auction_type')
            ->orderBy('location_name')
            ->limit(20)
            ->get()
            ->toArray();
    }
}
