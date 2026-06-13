<?php

namespace Modules\Reconnaissance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Reconnaissance\Http\Requests\StoreScanRequest;
use Modules\Reconnaissance\Models\Scan;
use Modules\Reconnaissance\Services\PipelineService;

class ScanController extends Controller
{
    public function store(StoreScanRequest $request, PipelineService $pipeline): JsonResponse
    {
        $scan = Scan::query()->create([
            'user_id' => $request->user()->id,
            'domain' => strtolower($request->string('domain')->toString()),
            'options' => $request->validated('options'),
        ]);

        $pipeline->dispatch($scan);

        return response()->json([
            'scan' => $scan->fresh(),
        ], 201);
    }

    public function show(Scan $scan): JsonResponse
    {
        $this->authorize('view', $scan);

        return response()->json([
            'scan' => $scan->load('logs'),
        ]);
    }
}
