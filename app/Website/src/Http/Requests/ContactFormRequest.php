<?php

namespace App\Website\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email:filter',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ];

        if (app()->environment('production')) {
            // Only apply the captcha rule if not in the testing environment
            $rules['cf-turnstile-response'] = ['required', Rule::turnstile()];
        }

        return $rules;

    }
}
