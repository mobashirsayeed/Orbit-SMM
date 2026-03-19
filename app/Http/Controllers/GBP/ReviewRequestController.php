<?php

namespace App\Http\Controllers\GBP;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewRequestController extends Controller
{
    // TODO: Implement GBP Review Requests logic

    public function index(Request $request)
    {
        return Inertia::render('GBP/ReviewRequest');
    }
}
