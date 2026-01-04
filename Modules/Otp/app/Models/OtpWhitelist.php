<?php

namespace Modules\Otp\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Otp\Database\Factories\OtpWhitelistFactory;

class OtpWhitelist extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_type',
        'recipient',
        'fixed_otp',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return OtpWhitelistFactory::new();
    }

    /**
     * Find a whitelist entry by recipient
     *
     * @param string $recipientType
     * @param string $recipient
     * @return self|null
     */
    public static function findByRecipient(string $recipientType, string $recipient): ?self
    {
        return static::where('recipient_type', $recipientType)
            ->where('recipient', $recipient)
            ->where('is_active', true)
            ->first();
    }
}
