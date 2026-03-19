<?php

namespace App\Http\Controllers\Inbox;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssignController extends Controller
{
    // TODO: Implement AssignController logic
    
    public function index(Request $request)
    {
        return Inertia::render('Inbox/Assign');
    }
}
