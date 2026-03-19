<?php

namespace App\Http\Controllers\GBP;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocationController extends Controller
{
    // TODO: Implement GBP Locations logic

    public function index(Request $request)
    {
        return Inertia::render('GBP/Location');
    }
}
