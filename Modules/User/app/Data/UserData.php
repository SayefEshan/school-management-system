<?php

namespace Modules\User\Data;

use Illuminate\Support\Facades\Auth;
use Modules\User\Rules\PhoneNumber;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        #[Email]
        public ?string $email = null,

        public ?string $phone = null,

        #[Min(6)]
        public ?string $password = null,

        public ?string $gender = null,
        public ?string $first_name = null,
        public ?string $last_name = null,
        public mixed $image = null,

        public ?array $roles = [],

        public bool $is_active = true,
        public bool $is_verified = false,
    ) {}

    public static function rules(): array
    {
        $userId = Auth::id();
        
        return [
            'first_name' => ['nullable', 'string', 'max:40'],
            'last_name' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'required_without:phone', 'email', 'unique:users,email' . ($userId ? ',' . $userId : '')],
            'phone' => ['nullable', 'required_without:email', 'string', 'unique:users,phone' . ($userId ? ',' . $userId : ''), new PhoneNumber()],
            'password' => ['nullable', 'confirmed', 'min:6'],
            'gender' => ['nullable', 'in:male,female,other'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'exists:roles,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Override rules for update (email and phone uniqueness with ID)
     */
    public static function rulesForUpdate(int $userId): array
    {
        return [
            'first_name' => ['nullable', 'string', 'max:40'],
            'last_name' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'required_without:phone', 'email', 'unique:users,email,' . $userId],
            'phone' => ['nullable', 'required_without:email', 'string', 'unique:users,phone,' . $userId, new PhoneNumber()],
            'password' => ['nullable', 'confirmed', 'min:6'],
            'gender' => ['nullable', 'in:male,female,other'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'exists:roles,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public static function messages(): array
    {
        return [
            'email.unique' => 'The email has already been taken',
            'phone.unique' => 'The mobile no has already been taken',
        ];
    }
}
