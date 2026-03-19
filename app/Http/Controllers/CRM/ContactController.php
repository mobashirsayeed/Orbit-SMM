<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactController extends Controller
{
    // TODO: Implement ContactController logic
    
    public function index(Request $request)
    {
        return Inertia::render('CRM/Contact');
    }
}
