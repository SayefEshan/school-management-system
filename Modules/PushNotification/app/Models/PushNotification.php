<?php

namespace Modules\PushNotification\Models;

use App\Services\FileManagerService;
use Illuminate\Database\Eloquent\Model;

class PushNotification extends Model
{

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'body',
        'data',
        'image',
        'url',
        'description',
        'result',
    ];

    public function getImageAttribute($value)
    {
        return FileManagerService::getImage($value);
    }

    public function setImageAttribute($value)
    {
        $this->attributes['image'] = FileManagerService::uploadFile($value, null, 'push-notification');
    }
}
