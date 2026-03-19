<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PipelineController extends Controller
{
    // TODO: Implement PipelineController logic
    
    public function index(Request $request)
    {
        return Inertia::render('CRM/Pipeline');
    }
}
