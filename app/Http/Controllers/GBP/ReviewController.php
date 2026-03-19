<?php

namespace App\Http\Controllers\GBP;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    // TODO: Implement GBP Reviews logic

    public function index(Request $request)
    {
        return Inertia::render('GBP/Review');
    }
}
