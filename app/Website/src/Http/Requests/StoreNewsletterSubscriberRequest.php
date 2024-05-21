<?php

namespace App\Website\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\RequiredIf;

class StoreNewsletterSubscriberRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email:filter'],
            'cf-turnstile-response' => [
                new RequiredIf(function () {
                    return app()->isProduction();
                }),
                Rule::when(app()->isProduction(), [
                    'string',
                    Rule::turnstile(),
                ]),
            ],
        ];
    }
}
