<?php

namespace App\Website\Http\Requests;

use App\Website\Http\Roules\MailboxValidEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     * @return array
     */
    public function rules(): array
    {
        // Resolve the MailboxValidEmail dependency from the container
        $mailboxValidEmail = app(MailboxValidEmail::class);

        return [
            'first_name' => ['required', 'string', 'max:50'],
            'email' => [
                'required',
                'email:filter',
                app()->isProduction() ? $mailboxValidEmail : 'nullable',
            ],
            'cf-turnstile-response' => [
                app()->isProduction() ? ['required', 'string', Rule::turnstile()] : 'nullable',
            ],
        ];
    }
}
