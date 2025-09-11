<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomManager extends Model
{
    use HasFactory;
    protected $fillable = ['uuid','room_uuid','user_uuid', 'date', 'start_time', 'end_time', 'status'];
}
