<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class TaskCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'parent_id' => ['numeric', 'nullable'],
            'priority' => ['numeric', 'min:1', 'max:5'],
            'title' => ['required', 'max:255'],
            'description' => ['required', 'max:3000'],
        ];
    }
}
