<?php

namespace App\Console\Commands;

use App\Models\Car;
use App\Models\Transaction;
use Illuminate\Console\Command;

class RecalculateCarPaidAmount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cars:recalculate-paid-amount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate paid_amount for all cars based on transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Recalculating paid_amount for all cars from transactions...');
        
        $cars = Car::all();
        $updated = 0;
        
        foreach ($cars as $car) {
            $addPurposes = [
                Transaction::PURPOSE_VEHICLE_PAYMENT,
                Transaction::PURPOSE_SHIPPING,
                Transaction::PURPOSE_WALLET_TO_CAR,
                Transaction::PURPOSE_CAR_TO_CAR_IN,
            ];
            $totalIn = (float) Transaction::where('car_id', $car->id)->whereIn('purpose', $addPurposes)->sum('amount');
            $totalOut = (float) Transaction::where('car_id', $car->id)->where('purpose', Transaction::PURPOSE_CAR_TO_CAR_OUT)->sum('amount');
            $totalPaid = max(0, $totalIn - $totalOut);

            $oldPaid = $car->paid_amount;
            $car->paid_amount = $totalPaid;
            $car->save();
            
            if ($oldPaid != $totalPaid) {
                $this->line("Car ID {$car->id} (VIN: {$car->vin}): Updated from \${$oldPaid} to \${$totalPaid}");
                $updated++;
            }
        }
        
        $this->info("Recalculation complete! Updated {$updated} cars.");
        $this->info("All cars now have paid_amount synchronized with transactions.");
        
        return Command::SUCCESS;
    }
}
