<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\LampResource;
use App\Models\Lamp;
use App\Models\OperationParameter;
use App\Models\SensorReading;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $lampsQuery = Lamp::query()
            ->with(['operationParameter', 'latestReading'])
            ->orderBy('lamp_code')
            ->orderBy('id');

        if ($search !== '') {
            $lampsQuery->where(function ($query) use ($search): void {
                $query->where('lamp_code', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%')
                    ->orWhere('location', 'like', '%'.$search.'%')
                    ->orWhere('controller_device_id', 'like', '%'.$search.'%');
            });
        }

        $lamps = $lampsQuery->get();

        $allLamps = Lamp::query()
            ->with(['operationParameter', 'latestReading'])
            ->orderBy('lamp_code')
            ->orderBy('id')
            ->get();

        $operationParameters = OperationParameter::query()
            ->with('lamp')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        $recentReadings = SensorReading::query()
            ->with('lamp')
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        $stats = [
            'total_lamps' => $allLamps->count(),
            'active_lamps' => $allLamps->where('is_active', true)->count(),
            'lamps_on' => $allLamps->where('power_status', 'ON')->count(),
            'lamps_dimmed' => $allLamps->where('power_status', 'ON')->where('intensity_percent', '<', 100)->count(),
            'lamps_online' => $allLamps->where('connectivity_status', 'ONLINE')->count(),
            'total_power_watt' => round((float) $allLamps->sum('power_watt'), 2),
        ];

        return view('dashboard.index', [
            'lamps' => $lamps,
            'allLamps' => $allLamps,
            'operationParameters' => $operationParameters,
            'recentReadings' => $recentReadings,
            'stats' => $stats,
            'search' => $search,
        ]);
    }

    /**
     * Real-time monitoring endpoint (KAN-39 / KF-07).
     */
    public function monitoringStatus(): JsonResponse
    {
        $lamps = Lamp::query()
            ->with(['operationParameter', 'latestReading'])
            ->orderBy('lamp_code')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => LampResource::collection($lamps),
            'meta' => [
                'total_lamps' => $lamps->count(),
                'active_lamps' => $lamps->where('is_active', true)->count(),
                'lamps_on' => $lamps->where('power_status', 'ON')->count(),
                'total_power_watt' => round((float) $lamps->sum('power_watt'), 2),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }
}
