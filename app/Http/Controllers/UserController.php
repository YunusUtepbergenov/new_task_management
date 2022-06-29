<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function userLeave(Request $request){
        $user = \App\Models\User::where('id', $request->user_id)->first();
        $user->update(['leave' => 1]);

        return back();
    }
}
