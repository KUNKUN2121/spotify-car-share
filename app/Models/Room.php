<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_spotify_id',
        'room_id',
    ];

    public function user(){
        return $this->hasOne(User::class);
    }
}
