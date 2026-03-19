<?php

namespace App\Http\Controllers\SEO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SchemaController extends Controller
{
    // TODO: Implement SchemaController logic
    
    public function index(Request $request)
    {
        return Inertia::render('SEO/Schema');
    }
}
