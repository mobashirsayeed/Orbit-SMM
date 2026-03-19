<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HashtagController extends Controller
{
    // TODO: Implement HashtagController logic
    
    public function index(Request $request)
    {
        return Inertia::render('AI/Hashtag');
    }
}
