<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarController extends Controller
{
    // TODO: Implement CalendarController logic
    
    public function index(Request $request)
    {
        return Inertia::render('Social/Calendar');
    }
}
