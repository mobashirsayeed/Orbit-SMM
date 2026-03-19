<?php

namespace App\Http\Controllers\GBP;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GBPReviewController extends Controller
{
    // TODO: Implement GBPReviewController logic
    
    public function index(Request $request)
    {
        return Inertia::render('GBP/GBPReview');
    }
}
