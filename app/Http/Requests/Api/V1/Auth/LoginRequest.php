<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\Auth\LoginRequest as WebLoginRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class LoginRequest extends WebLoginRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'device_name' => ['required', 'string', 'max:255'],
        ];
    }
}
