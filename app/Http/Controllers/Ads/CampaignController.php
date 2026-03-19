<?php

namespace App\Http\Controllers\Ads;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CampaignController extends Controller
{
    // TODO: Implement CampaignController logic
    
    public function index(Request $request)
    {
        return Inertia::render('Ads/Campaign');
    }
}
