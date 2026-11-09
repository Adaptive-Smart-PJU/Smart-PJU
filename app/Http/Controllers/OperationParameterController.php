<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreOperationParameterRequest;
use App\Http\Requests\UpdateOperationParameterRequest;
use App\Http\Resources\OperationParameterResource;
use App\Models\OperationParameter;
use App\Services\SmartLightingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OperationParameterController extends Controller
{
    public function __construct(
        public readonly SmartLightingService $lightingService,
    ) {}

    /**
     * Get list of active operation parameters (KAN-41).
     */
    public function index(): JsonResponse
    {
        $parameters = OperationParameter::query()
            ->with('lamp')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        return OperationParameterResource::collection($parameters)->response();
    }

    /**
     * Show detail of an active operation parameter profile (KAN-41).
     */
    public function show(OperationParameter $operationParameter): JsonResponse
    {
        return (new OperationParameterResource($operationParameter->load('lamp')))->response();
    }

    /**
     * Create a new operation parameter profile for a lamp (KAN-40).
     */
    public function store(StoreOperationParameterRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->safe()->only([
            'lamp_id',
            'profile_name',
            'lux_threshold',
            'hysteresis_lux',
            'dim_intensity_percent',
            'bright_intensity_percent',
            'energy_saving_start_time',
            'energy_saving_end_time',
        ]);

        $parameter = OperationParameter::updateOrCreate(
            ['lamp_id' => $validated['lamp_id']],
            array_merge($validated, [
                'is_custom' => true,
                'is_active' => true,
                'synced_to_gateway_at' => now(),
            ]),
        );

        if ($request->expectsJson() || $request->is('api/*')) {
            return (new OperationParameterResource($parameter))
                ->response()
                ->setStatusCode(201);
        }

        return redirect()->route('dashboard')
            ->with('status', "Profil parameter operasi '{$parameter->profile_name}' berhasil ditambahkan.");
    }

    /**
     * Update operation parameter profile and sync to Gateway (KAN-42).
     */
    public function update(
        UpdateOperationParameterRequest $request,
        OperationParameter $operationParameter,
    ): RedirectResponse|JsonResponse {
        $validated = $request->safe()->only([
            'profile_name',
            'lux_threshold',
            'hysteresis_lux',
            'dim_intensity_percent',
            'bright_intensity_percent',
            'energy_saving_start_time',
            'energy_saving_end_time',
        ]);

        $operationParameter->update(array_merge($validated, [
            'is_custom' => true,
            'synced_to_gateway_at' => now(),
        ]));

        if ($request->expectsJson() || $request->is('api/*')) {
            return (new OperationParameterResource($operationParameter->refresh()))->response();
        }

        return redirect()->route('dashboard')
            ->with('status', "Parameter operasi '{$operationParameter->profile_name}' berhasil diperbarui & disinkronkan ke Gateway.");
    }

    /**
     * Reset or delete custom operation parameter profile back to safe defaults (KAN-43).
     */
    public function destroy(
        Request $request,
        OperationParameter $operationParameter,
    ): RedirectResponse|JsonResponse {
        if (! $request->user()?->isPengelola()) {
            abort(403, 'Akses ditolak untuk peran Anda.');
        }

        $resetParameter = $this->lightingService->resetOperationParameterToDefault($operationParameter);

        if ($request->expectsJson() || $request->is('api/*')) {
            return (new OperationParameterResource($resetParameter))->response();
        }

        return redirect()->route('dashboard')
            ->with('status', 'Profil parameter operasi kustom telah direset ke nilai bawaan yang aman.');
    }
}
