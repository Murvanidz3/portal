<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'username' => 'required|string|max:50|unique:users,username|alpha_dash',
            'full_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'password' => ['required', 'confirmed', Password::min(6)],
            'role' => 'required|in:admin,dealer,client',
            'balance' => 'nullable|numeric|min:0|max:999999.99',
            'sms_enabled' => 'boolean',
            'approved' => 'boolean',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'username' => 'მომხმარებლის სახელი',
            'full_name' => 'სრული სახელი',
            'email' => 'ელ-ფოსტა',
            'phone' => 'ტელეფონი',
            'password' => 'პაროლი',
            'role' => 'როლი',
            'balance' => 'ბალანსი',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'username.required' => 'მომხმარებლის სახელი სავალდებულოა.',
            'username.unique' => 'ეს მომხმარებლის სახელი უკვე დაკავებულია.',
            'username.alpha_dash' => 'მომხმარებლის სახელი შეიძლება შეიცავდეს მხოლოდ ასოებს, ციფრებს და ხაზებს.',
            'email.unique' => 'ეს ელ-ფოსტა უკვე რეგისტრირებულია.',
            'password.required' => 'პაროლი სავალდებულოა.',
            'password.confirmed' => 'პაროლები არ ემთხვევა.',
            'password.min' => 'პაროლი უნდა იყოს მინიმუმ 6 სიმბოლო.',
        ];
    }
}
