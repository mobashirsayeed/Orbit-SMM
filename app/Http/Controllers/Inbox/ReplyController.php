<?php

namespace App\Http\Controllers\Inbox;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReplyController extends Controller
{
    // TODO: Implement ReplyController logic
    
    public function index(Request $request)
    {
        return Inertia::render('Inbox/Reply');
    }
}
