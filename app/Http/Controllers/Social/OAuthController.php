<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OAuthController extends Controller
{
    // TODO: Implement OAuthController logic
    
    public function index(Request $request)
    {
        return Inertia::render('Social/OAuth');
    }
}
