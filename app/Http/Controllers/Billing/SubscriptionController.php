<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    // TODO: Implement SubscriptionController logic
    
    public function index(Request $request)
    {
        return Inertia::render('Billing/Subscription');
    }
}
