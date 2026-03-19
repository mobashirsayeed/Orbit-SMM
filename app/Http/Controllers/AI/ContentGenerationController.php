<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContentGenerationController extends Controller
{
    // TODO: Implement ContentGenerationController logic
    
    public function index(Request $request)
    {
        return Inertia::render('AI/ContentGeneration');
    }
}
