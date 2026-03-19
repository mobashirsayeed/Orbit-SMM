<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AutomationController extends Controller
{
    // TODO: Implement AutomationController logic
    
    public function index(Request $request)
    {
        return Inertia::render('CRM/Automation');
    }
}
