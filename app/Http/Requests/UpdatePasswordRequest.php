<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'confirmed', 'min:10', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'], // https://regex101.com/
        ];
    }
}
