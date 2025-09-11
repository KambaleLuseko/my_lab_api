<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salles extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'capacity',
        'closed_at',
        'opened_at',
        'status',
        'created_at',
        'updated_at'
    ];
}
