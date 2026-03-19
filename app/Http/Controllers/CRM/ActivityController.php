<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityController extends Controller
{
    // TODO: Implement ActivityController logic
    
    public function index(Request $request)
    {
        return Inertia::render('CRM/Activity');
    }
}
