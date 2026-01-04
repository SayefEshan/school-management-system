<?php

namespace Modules\PushNotification\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class FirebaseToken extends Model
{

    protected $fillable = [
        'token',
        'device_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
