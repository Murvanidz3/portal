<?php

namespace App\Http\Requests;

use App\Models\Car;
use Illuminate\Foundation\Http\FormRequest;

class StoreCarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'vin' => 'required|string|max:17',
            'make' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'lot_number' => 'required|string|max:50',
            'auction_name' => 'required|string|max:50',
            'auction_location' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'arrival_date' => 'nullable|date',
            'document_received_at' => 'nullable|date',
            'document_issued_at' => 'nullable|date',
            'status' => 'required|in:' . implode(',', array_keys(Car::getStatuses())),
            'container_number' => 'nullable|string|max:50',
            'booking_number' => 'nullable|string|max:100',
            'shipping_line' => 'nullable|string|max:100',
            'vessel' => 'nullable|string|max:100',
            'loading_date' => 'nullable|date',
            'estimated_arrival_date' => 'nullable|date',
            'terminal' => 'nullable|string|max:100',
            'vehicle_cost' => 'required|numeric|min:0',
            'dealer_profit' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'required|numeric|min:0',
            'additional_cost' => 'nullable|numeric|min:0',
            'transfer_commission' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'client_name' => 'nullable|string|max:100',
            'client_phone' => 'nullable|string|max:50',
            'client_id_number' => 'nullable|string|max:50',
            'dealer_phone' => 'nullable|string|max:50',
            'client_user_id' => 'nullable|exists:users,id',
            'user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'photo_category' => 'nullable|in:auction,pickup,warehouse,poti',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'vin' => 'VIN კოდი',
            'make_model' => 'მარკა/მოდელი',
            'year' => 'წელი',
            'lot_number' => 'ლოტის ნომერი',
            'auction_name' => 'აუქციონი',
            'container_number' => 'კონტეინერის ნომერი',
            'purchase_date' => 'შეძენის თარიღი',
            'arrival_date' => 'ჩამოსვლის თარიღი',
            'status' => 'სტატუსი',
            'vehicle_cost' => 'მანქანის ფასი',
            'shipping_cost' => 'ტრანსპორტირება',
            'paid_amount' => 'გადახდილი თანხა',
            'client_name' => 'კლიენტის სახელი',
            'client_phone' => 'კლიენტის ტელეფონი',
            'photos.*' => 'ფოტო',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'vin.required' => 'VIN კოდი სავალდებულოა.',
            'vin.max' => 'VIN კოდი არ უნდა აღემატებოდეს 17 სიმბოლოს.',
            'make_model.required' => 'მარკა/მოდელი სავალდებულოა.',
            'status.required' => 'სტატუსი სავალდებულოა.',
            'status.in' => 'არჩეული სტატუსი არასწორია.',
            'arrival_date.after_or_equal' => 'ჩამოსვლის თარიღი უნდა იყოს შეძენის თარიღის შემდეგ.',
            'photos.*.image' => 'ფაილი უნდა იყოს სურათი.',
            'photos.*.max' => 'ფოტოს ზომა არ უნდა აღემატებოდეს 10MB-ს.',
        ];
    }
}
