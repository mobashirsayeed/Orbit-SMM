<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DealController extends Controller
{
    // TODO: Implement DealController logic
    
    public function index(Request $request)
    {
        return Inertia::render('CRM/Deal');
    }
}
