<?php

namespace App\Platform\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
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
            'email' => 'string|required|email|max:255|unique:members,email',
            'password' => 'required|max:50|min:6',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'website_url' => 'url',
            'linkedIn_url' => 'url',
            'gitHub_url' => 'url',
            'behance_url' => 'url',
            'dribbble_url' => 'url',
            'skills' => 'array',
            'skills.*.skill_name' => 'string|max:255',
            'skills.*.level' => 'integer|between:1,10',
            'learning_interests' => 'array',
            'learning_interests.*.category_name' => 'string|max:255',
            'learning_interests.*.note' => 'string|max:255',
            'experiences' => 'array',
            'experiences.*.category_name' => 'string|max:255',
            'experiences.*.current_position' => 'string|max:255',
        ];
    }
}
