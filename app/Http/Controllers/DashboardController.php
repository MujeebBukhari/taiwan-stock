<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the application dashboard and portfolio overview.
     */
    public function index(Request $request, DashboardService $dashboardService): View
    {
        $user = $request->user();

        $data = $dashboardService->getDashboardData($user);

        return view('dashboard', $data);
    }
}
