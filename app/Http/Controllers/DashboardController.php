<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the user dashboard.
     */
    public function __invoke(Request $request, DashboardService $service): View
    {
        $user = $request->user();
        $data = $service->getDashboardData($user);

        return view('dashboard', $data);
    }
}
