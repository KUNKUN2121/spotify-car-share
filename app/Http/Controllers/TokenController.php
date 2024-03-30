<?php

namespace App\Http\Controllers;

use App\Models\Token;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function index(){
        // $tokens = Token::all();
        $user_id = 1;
        $tokens = Token::find($user_id);
        return $tokens->id;
    }


}
