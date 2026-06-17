<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SsoSimulationController extends Controller
{
    public function show(): View
    {
        return view('auth.sso-simulation');
    }
}
