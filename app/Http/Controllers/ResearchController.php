<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ResearchController extends Controller
{
    public function houses(){
        return view('page.research.houses');
    }
}
