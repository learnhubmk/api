<?php

namespace App\Admin\Http\Requests;

use App\Framework\Enums\UserStatusName;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\File;

class UpdateContentManagerManagementRequest extends FormRequest
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
        return [
            'email' => ['required', 'email', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', new Enum(UserStatusName::class)],
            'image' => ['nullable', 'file', File::types(['jpeg', 'png'])->max(4 * 1024)],
        ];
    }
}
