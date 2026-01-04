<?php

namespace Modules\Otp\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class VerificationCode extends Model
{
    use Notifiable;

    public function routeNotificationForMail()
    {
        return $this->contact;
    }

    protected $fillable = [
        'code',
        'contact_type',
        'contact',
        'expires_at',
        'is_verified',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now())->where('is_verified', false);
    }

    public function scopeCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    public function scopeContact($query, string $contact)
    {
        return $query->where('contact', $contact);
    }

    public function markAsVerified(): void
    {
        $this->update(['is_verified' => true]);
    }
}
