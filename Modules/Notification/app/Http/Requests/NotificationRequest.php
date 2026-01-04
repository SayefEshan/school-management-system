<?php

namespace Modules\Notification\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class NotificationRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return match ($this->method()) {
            'POST' => [
                'type' => 'required|string|max:255',
                'notifiable_type' => 'required|string|max:255',
                'notifiable_id' => 'required',
                'data' => 'required|array',
            ],
            'PUT', 'PATCH' => [
                'type' => 'sometimes|string|max:255',
                'data' => 'sometimes|array',
            ],
            default => [],
        };
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->wantsJson() || $this->is('api/*')) {
            throw new HttpResponseException(response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422));
        }

        parent::failedValidation($validator);
    }
}
