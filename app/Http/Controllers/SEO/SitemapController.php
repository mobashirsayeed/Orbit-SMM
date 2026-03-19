<?php

namespace App\Http\Controllers\SEO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SitemapController extends Controller
{
    // TODO: Implement SitemapController logic
    
    public function index(Request $request)
    {
        return Inertia::render('SEO/Sitemap');
    }
}
