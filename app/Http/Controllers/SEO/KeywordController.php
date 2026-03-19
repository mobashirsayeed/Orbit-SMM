<?php

namespace App\Http\Controllers\SEO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KeywordController extends Controller
{
    // TODO: Implement KeywordController logic
    
    public function index(Request $request)
    {
        return Inertia::render('SEO/Keyword');
    }
}
