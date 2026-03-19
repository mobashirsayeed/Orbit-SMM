<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BrandVoiceController extends Controller
{
    // TODO: Implement BrandVoiceController logic
    
    public function index(Request $request)
    {
        return Inertia::render('AI/BrandVoice');
    }
}
