<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ImageGenerationController extends Controller
{
    // TODO: Implement ImageGenerationController logic
    
    public function index(Request $request)
    {
        return Inertia::render('AI/ImageGeneration');
    }
}
