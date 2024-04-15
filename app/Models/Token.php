<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Token extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'spotify_id',
        'token',
        'token_at',
        'refresh_token',
        'refresh_token_at',

    ];
}
