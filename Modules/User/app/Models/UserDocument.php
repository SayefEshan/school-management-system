<?php

namespace Modules\User\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDocument extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Modules\User\Database\Factories\UserDocumentFactory::new();
    }

    protected $fillable = [
        'user_id',
        'document_type',
        'document_number',
        'file_path',
        'back_file_path',
        'expiry_date',
        'status',
        'verification_response',
        'verified_at',
        'verified_by',
        'rejection_reason',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'verified_at' => 'datetime',
        'verification_response' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
