<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TwoFactorChallengeRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'challenge_token' => [
                'required',
                'string',
                'size:64',
            ],
            'code' => [
                'nullable',
                'required_without:recovery_code',
                'prohibits:recovery_code',
                'string',
                'digits:6',
            ],
            'recovery_code' => [
                'nullable',
                'required_without:code',
                'prohibits:code',
                'string',
            ],
        ];
    }
}
