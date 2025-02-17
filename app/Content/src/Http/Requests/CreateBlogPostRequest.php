<?php

namespace App\Content\Http\Requests;

use App\Framework\Enums\RoleName;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class CreateBlogPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole(RoleName::ADMIN) || $this->user()->hasRole(RoleName::CONTENT_MANAGER);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'tags' => ['required', 'array'],
            'tags.*' => ['required', 'exists:blog_post_tags,id'],
            'image' => ['nullable', 'file', File::types(['jpeg', 'png'])->max(4 * 1024)],
        ];
    }
}
