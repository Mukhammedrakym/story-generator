<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoryRequest extends FormRequest
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
            'age' => 'required|integer|min:1',
            'language' => 'required|in:ru,kk',
            'genre' => 'required|in:adventure,fantasy,fairy_tale,comedy,drama,սimal_tale,family_tale,educational_tale,detective,travel',
            'characters' => 'required|array|min:1',
            'characters.*' => 'string|filled',
        ];
    }
}
