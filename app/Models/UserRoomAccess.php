<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRoomAccess extends Model
{
    use HasFactory;
    protected $fillable=[
        'uuid',
        'user_uuid',
        'room_uuid',
        'date',
        'start_time',
        'end_time',
        'status',
    ];
}
