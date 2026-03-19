<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PasswordResetController extends Controller
{
    // TODO: Implement PasswordResetController logic
    
    public function index(Request $request)
    {
        return Inertia::render('Auth/PasswordReset');
    }
}
