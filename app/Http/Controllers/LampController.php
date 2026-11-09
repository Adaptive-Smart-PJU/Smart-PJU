<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreLampRequest;
use App\Http\Requests\UpdateLampRequest;
use App\Http\Resources\LampResource;
use App\Models\Lamp;
use App\Models\OperationParameter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LampController extends Controller
{
    /**
     * List all lamps with search support (KAN-35).
     */
    public function index(Request $request): View|JsonResponse
    {
        $search = $request->string('search')->trim()->toString();

        $query = Lamp::query()
            ->with(['operationParameter', 'latestReading'])
            ->orderBy('lamp_code')
            ->orderBy('id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('lamp_code', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%')
                    ->orWhere('location', 'like', '%'.$search.'%')
                    ->orWhere('controller_device_id', 'like', '%'.$search.'%');
            });
        }

        $lamps = $query->get();

        if ($request->expectsJson() || $request->is('api/*')) {
            return LampResource::collection($lamps)->response();
        }

        return view('lamps.index', [
            'lamps' => $lamps,
            'search' => $search,
        ]);
    }

    /**
     * Show detail of a registered lamp (KAN-35).
     */
    public function show(Request $request, Lamp $lamp): View|JsonResponse
    {
        $lamp->load(['operationParameter', 'latestReading', 'sensorReadings' => function ($query): void {
            $query->orderByDesc('recorded_at')->orderByDesc('id')->limit(20);
        }]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return (new LampResource($lamp))->response();
        }

        return view('lamps.show', [
            'lamp' => $lamp,
        ]);
    }

    /**
     * Store a newly registered lamp (KAN-34).
     */
    public function store(StoreLampRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->safe()->only([
            'lamp_code',
            'name',
            'location',
            'latitude',
            'longitude',
            'controller_device_id',
            'operation_mode',
        ]);

        $validated['operation_mode'] = $validated['operation_mode'] ?? 'ADAPTIVE';
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['last_seen_at'] = now();

        $lamp = Lamp::create($validated);

        OperationParameter::create(array_merge(
            OperationParameter::safeDefaultAttributes(),
            [
                'lamp_id' => $lamp->id,
                'synced_to_gateway_at' => now(),
            ],
        ));

        $lamp->load('operationParameter');

        if ($request->expectsJson() || $request->is('api/*')) {
            return (new LampResource($lamp))
                ->response()
                ->setStatusCode(201);
        }

        return redirect()->route('dashboard')
            ->with('status', "Data lampu {$lamp->lamp_code} berhasil ditambahkan.");
    }

    /**
     * Update an existing lamp's location and controller identity (KAN-36).
     */
    public function update(UpdateLampRequest $request, Lamp $lamp): RedirectResponse|JsonResponse
    {
        $validated = $request->safe()->only([
            'lamp_code',
            'name',
            'location',
            'latitude',
            'longitude',
            'controller_device_id',
            'operation_mode',
        ]);

        if ($request->has('is_active')) {
            $validated['is_active'] = $request->boolean('is_active');
        }

        $lamp->update($validated);

        if ($request->expectsJson() || $request->is('api/*')) {
            return (new LampResource($lamp->refresh()->load('operationParameter')))->response();
        }

        return redirect()->route('dashboard')
            ->with('status', "Data lampu {$lamp->lamp_code} berhasil diperbarui.");
    }

    /**
     * Deactivate or toggle active status of a lamp (KAN-37).
     */
    public function deactivate(Request $request, Lamp $lamp): RedirectResponse|JsonResponse
    {
        $lamp->update([
            'is_active' => false,
            'power_status' => 'OFF',
            'intensity_percent' => 0,
            'current_ampere' => 0.0,
            'power_watt' => 0.0,
        ]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return (new LampResource($lamp->refresh()))->response();
        }

        return redirect()->route('dashboard')
            ->with('status', "Lampu {$lamp->lamp_code} telah dinonaktifkan.");
    }

    /**
     * Delete a lamp record (KAN-37).
     */
    public function destroy(Request $request, Lamp $lamp): RedirectResponse|JsonResponse
    {
        $code = $lamp->lamp_code;
        $lamp->delete();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => "Data lampu {$code} berhasil dihapus.",
            ]);
        }

        return redirect()->route('dashboard')
            ->with('status', "Data lampu {$code} berhasil dihapus.");
    }
}
