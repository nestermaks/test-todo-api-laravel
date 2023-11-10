<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Str;

class TaskStatusRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'status' => Str::lower($this->status),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'max:40'],
        ];
    }
}
