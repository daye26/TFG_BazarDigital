<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminStatsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminStatsController extends Controller
{
    public function index(Request $request, AdminStatsService $adminStatsService): View
    {
        $range = $adminStatsService->resolveRange($request);

        return view('admin.stats.index', [
            'range' => $range,
            'summary' => $adminStatsService->summary($range['from'], $range['to']),
            'charts' => $adminStatsService->charts($range['from'], $range['to']),
        ]);
    }
}
