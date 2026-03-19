<?php

namespace App\Http\Controllers\GBP;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GBPInsightController extends Controller
{
    // TODO: Implement GBPInsightController logic
    
    public function index(Request $request)
    {
        return Inertia::render('GBP/GBPInsight');
    }
}
