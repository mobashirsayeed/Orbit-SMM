<?php

namespace App\Http\Controllers\Inbox;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CannedResponseController extends Controller
{
    // TODO: Implement CannedResponseController logic
    
    public function index(Request $request)
    {
        return Inertia::render('Inbox/CannedResponse');
    }
}
