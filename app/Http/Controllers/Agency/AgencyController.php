<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AgencyController extends Controller
{
    // TODO: Implement AgencyController logic
    
    public function index(Request $request)
    {
        return Inertia::render('Agency/Agency');
    }
}
