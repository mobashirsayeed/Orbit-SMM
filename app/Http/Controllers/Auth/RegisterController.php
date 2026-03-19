<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegisterController extends Controller
{
    // TODO: Implement RegisterController logic
    
    public function index(Request $request)
    {
        return Inertia::render('Auth/Register');
    }
}
