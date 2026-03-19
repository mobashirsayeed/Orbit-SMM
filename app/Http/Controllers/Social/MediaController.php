<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MediaController extends Controller
{
    // TODO: Implement MediaController logic
    
    public function index(Request $request)
    {
        return Inertia::render('Social/Media');
    }
}
