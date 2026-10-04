<?php

namespace App\Http\Controllers;

use App\Services\ReportingService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(public ReportingService $reportingService) {}

    public function index(Request $request)
    {
        $period = $request->query('period', 'all');
        $kpis = $this->reportingService->getKpis($period);
        $recentActivities = $this->reportingService->getRecentActivities(10);

        return view('dashboard', compact('kpis', 'recentActivities'));
    }
}
