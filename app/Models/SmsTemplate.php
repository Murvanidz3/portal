<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_key',
        'template_text',
        'description',
    ];

    /**
     * Get template by status key
     */
    public static function getByStatus(string $statusKey): ?self
    {
        return self::where('status_key', $statusKey)->first();
    }

    /**
     * Parse template with car data
     */
    public function parseForCar(Car $car): string
    {
        $message = $this->template_text;
        
        $replacements = [
            '[მანქანა]' => $car->make_model,
            '[წელი]' => $car->year,
            '[ვინ]' => $car->vin,
            '[ლოტი]' => $car->lot_number ?? '',
            '[კონტეინერი]' => $car->container_number ?? 'უცნობია',
            '[კლიენტი]' => $car->getClientDisplayName(),
        ];
        
        foreach ($replacements as $placeholder => $value) {
            $message = str_replace($placeholder, $value, $message);
        }
        
        return $message;
    }

    /**
     * Get all templates as key-value pairs
     */
    public static function getAllTemplates(): array
    {
        return self::pluck('template_text', 'status_key')->toArray();
    }
}
