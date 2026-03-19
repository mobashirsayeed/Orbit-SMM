<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ApprovalController extends Controller
{
    // TODO: Implement ApprovalController logic
    
    public function index(Request $request)
    {
        return Inertia::render('Agency/Approval');
    }
}
