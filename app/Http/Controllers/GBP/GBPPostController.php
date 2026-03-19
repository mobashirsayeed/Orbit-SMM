<?php

namespace App\Http\Controllers\GBP;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GBPPostController extends Controller
{
    // TODO: Implement GBPPostController logic
    
    public function index(Request $request)
    {
        return Inertia::render('GBP/GBPPost');
    }
}
