<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\Car;
use App\Models\User;
use App\Observers\CarObserver;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        $this->updateCarPaidAmount($transaction);
        $this->updateUserBalance($transaction);
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        $oldCarId = $transaction->getOriginal('car_id') ?? $transaction->car_id;
        $oldPurpose = $transaction->getOriginal('purpose') ?? $transaction->purpose;

        if ($oldCarId && $this->purposeAffectsCar($oldPurpose) && ($oldCarId != $transaction->car_id || $oldPurpose != $transaction->purpose)) {
            $this->recalculateCarPaidAmount($oldCarId);
        }

        if ($transaction->car_id && $this->purposeAffectsCar($transaction->purpose)) {
            $this->recalculateCarPaidAmount($transaction->car_id);
        }

        if ($this->purposeAffectsUserBalance($oldPurpose) && $transaction->user_id) {
            $this->recalculateUserBalance($transaction->user_id);
        }
        if ($this->purposeAffectsUserBalance($transaction->purpose) && $transaction->user_id) {
            $this->recalculateUserBalance($transaction->user_id);
        }
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        if ($transaction->car_id && $this->purposeAffectsCar($transaction->purpose)) {
            $this->recalculateCarPaidAmount($transaction->car_id);
        }

        if ($transaction->purpose === Transaction::PURPOSE_BALANCE_TOPUP && $transaction->user_id) {
            $this->recalculateUserBalance($transaction->user_id);
        }
        if ($transaction->purpose === Transaction::PURPOSE_WALLET_TO_CAR && $transaction->user_id) {
            $this->recalculateUserBalance($transaction->user_id);
        }
    }

    protected function purposeAffectsCar(string $purpose): bool
    {
        return in_array($purpose, [
            Transaction::PURPOSE_VEHICLE_PAYMENT,
            Transaction::PURPOSE_SHIPPING,
            Transaction::PURPOSE_WALLET_TO_CAR,
            Transaction::PURPOSE_CAR_TO_CAR_OUT,
            Transaction::PURPOSE_CAR_TO_CAR_IN,
        ], true);
    }

    protected function purposeAffectsUserBalance(string $purpose): bool
    {
        return in_array($purpose, [Transaction::PURPOSE_BALANCE_TOPUP, Transaction::PURPOSE_WALLET_TO_CAR], true);
    }

    /**
     * Recalculate car's paid_amount from all its transactions.
     */
    protected function recalculateCarPaidAmount(int $carId): void
    {
        $car = Car::find($carId);
        if (!$car) {
            return;
        }

        $addPurposes = [
            Transaction::PURPOSE_VEHICLE_PAYMENT,
            Transaction::PURPOSE_SHIPPING,
            Transaction::PURPOSE_WALLET_TO_CAR,
            Transaction::PURPOSE_CAR_TO_CAR_IN,
        ];
        $outPurpose = Transaction::PURPOSE_CAR_TO_CAR_OUT;

        $totalIn = (float) Transaction::where('car_id', $carId)->whereIn('purpose', $addPurposes)->sum('amount');
        $totalOut = (float) Transaction::where('car_id', $carId)->where('purpose', $outPurpose)->sum('amount');
        $totalPaid = max(0, $totalIn - $totalOut);

        CarObserver::setUpdatingFromTransaction(true);
        try {
            $car->paid_amount = $totalPaid;
            $car->save();
        } finally {
            CarObserver::setUpdatingFromTransaction(false);
        }
    }

    /**
     * Recalculate user balance: topups minus wallet-to-car transfers.
     */
    protected function recalculateUserBalance(int $userId): void
    {
        $user = User::find($userId);
        if (!$user) {
            return;
        }

        $topup = (float) Transaction::where('user_id', $userId)
            ->where('purpose', Transaction::PURPOSE_BALANCE_TOPUP)
            ->sum('amount');
        $walletToCar = (float) Transaction::where('user_id', $userId)
            ->where('purpose', Transaction::PURPOSE_WALLET_TO_CAR)
            ->sum('amount');
        $user->balance = max(0, $topup - $walletToCar);
        $user->save();
    }

    /**
     * Update car's paid_amount based on transaction (called on create).
     */
    protected function updateCarPaidAmount(Transaction $transaction): void
    {
        if ($transaction->car_id && $this->purposeAffectsCar($transaction->purpose)) {
            $this->recalculateCarPaidAmount($transaction->car_id);
        }
    }

    /**
     * Update user balance (called on create).
     */
    protected function updateUserBalance(Transaction $transaction): void
    {
        if ($transaction->purpose === Transaction::PURPOSE_BALANCE_TOPUP && $transaction->user_id) {
            $this->recalculateUserBalance($transaction->user_id);
        }
        if ($transaction->purpose === Transaction::PURPOSE_WALLET_TO_CAR && $transaction->user_id) {
            $this->recalculateUserBalance($transaction->user_id);
        }
    }
}
