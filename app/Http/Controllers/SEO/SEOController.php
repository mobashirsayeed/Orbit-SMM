<?php

namespace App\Http\Controllers\SEO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SEOController extends Controller
{
    // TODO: Implement SEOController logic
    
    public function index(Request $request)
    {
        return Inertia::render('SEO/SEO');
    }
}
