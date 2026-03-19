<?php

namespace App\Http\Controllers\SEO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CrawlController extends Controller
{
    // TODO: Implement SEO Crawl logic

    public function index(Request $request)
    {
        return Inertia::render('SEO/Crawl');
    }
}
