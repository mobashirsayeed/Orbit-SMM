<?php

namespace App\Http\Controllers\SEO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RankController extends Controller
{
    // TODO: Implement SEO Rank logic

    public function index(Request $request)
    {
        return Inertia::render('SEO/Rank');
    }
}
