<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TemplateController extends Controller
{
    // TODO: Implement TemplateController logic
    
    public function index(Request $request)
    {
        return Inertia::render('AI/Template');
    }
}
