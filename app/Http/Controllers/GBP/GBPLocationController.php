<?php

namespace App\Http\Controllers\GBP;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GBPLocationController extends Controller
{
    // TODO: Implement GBPLocationController logic
    
    public function index(Request $request)
    {
        return Inertia::render('GBP/GBPLocation');
    }
}
