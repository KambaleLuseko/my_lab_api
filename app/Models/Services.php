<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'uuid',
        'salles_id'
    ];

    public function  salles ()
    {
        return $this -> belongsTo(Salles::class);
    }
    


}
