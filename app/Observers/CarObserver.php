<?php

namespace App\Observers;

use App\Models\Car;
use App\Models\Transaction;

class CarObserver
{
    /**
     * Flag to prevent infinite loop with TransactionObserver
     */
    private static bool $updatingFromTransaction = false;

    /**
     * Handle the Car "created" event.
     */
    public function created(Car $car): void
    {
        // If paid_amount is set on creation, create initial transaction
        if ($car->paid_amount > 0 && !self::$updatingFromTransaction) {
            $this->createTransactionForPaidAmount($car, $car->paid_amount, true);
        }
    }

    /**
     * Handle the Car "updated" event.
     */
    public function updated(Car $car): void
    {
        // Skip if update is from TransactionObserver to prevent infinite loop
        if (self::$updatingFromTransaction) {
            return;
        }

        // Check if paid_amount changed
        if ($car->wasChanged('paid_amount')) {
            $oldPaidAmount = (float) ($car->getOriginal('paid_amount') ?? 0);
            $newPaidAmount = (float) $car->paid_amount;
            $difference = $newPaidAmount - $oldPaidAmount;
            
            // Only create transaction if paid_amount increased
            if ($difference > 0.01) { // Use small threshold to avoid floating point issues
                $this->createTransactionForPaidAmount($car, $difference, false);
            }
        }
    }

    /**
     * Create transaction for paid_amount change
     */
    protected function createTransactionForPaidAmount(Car $car, float $amount, bool $isInitial = false): void
    {
        // Check if there's already a transaction created in the last few seconds to avoid duplicates
        $recentTransaction = Transaction::where('car_id', $car->id)
            ->whereIn('purpose', [
                Transaction::PURPOSE_VEHICLE_PAYMENT,
                Transaction::PURPOSE_SHIPPING
            ])
            ->where('created_at', '>=', now()->subSeconds(5))
            ->first();
        
        if ($recentTransaction) {
            // Transaction was just created, likely by TransactionObserver or another process
            return;
        }
        
        // Determine purpose based on car costs and current paid amount
        // Check which cost is closer to the paid amount
        $vehicleRemaining = max(0, $car->vehicle_cost - ($car->paid_amount - $amount));
        $shippingRemaining = max(0, ($car->shipping_cost + $car->additional_cost) - ($car->paid_amount - $amount));
        
        // If vehicle cost is more significant or both are similar, default to vehicle payment
        $purpose = ($car->vehicle_cost > 0 && ($vehicleRemaining > $shippingRemaining || $car->vehicle_cost >= $car->shipping_cost)) 
            ? Transaction::PURPOSE_VEHICLE_PAYMENT 
            : Transaction::PURPOSE_SHIPPING;
        
        // Set flag to prevent TransactionObserver from triggering CarObserver again
        self::$updatingFromTransaction = true;
        
        try {
            // Create transaction
            Transaction::create([
                'car_id' => $car->id,
                'user_id' => $car->user_id ?? auth()->id(),
                'amount' => $amount,
                'payment_date' => now()->toDateString(),
                'purpose' => $purpose,
                'comment' => $isInitial 
                    ? 'საწყისი გადახდა (ავტომატურად შექმნილი)' 
                    : 'გადახდა (ავტომატურად შექმნილი paid_amount-ის ცვლილებიდან)',
            ]);
        } finally {
            // Always reset flag
            self::$updatingFromTransaction = false;
        }
    }

    /**
     * Set flag to indicate update is from TransactionObserver
     */
    public static function setUpdatingFromTransaction(bool $value): void
    {
        self::$updatingFromTransaction = $value;
    }

    /**
     * Handle the Car "deleted" event.
     */
    public function deleted(Car $car): void
    {
        //
    }

    /**
     * Handle the Car "restored" event.
     */
    public function restored(Car $car): void
    {
        //
    }

    /**
     * Handle the Car "force deleted" event.
     */
    public function forceDeleted(Car $car): void
    {
        //
    }
}
