<?php

namespace Modules\Reconnaissance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Crawler\Models\Endpoint;
use Modules\Fingerprint\Models\Technology;
use Modules\Graph\Services\GraphBuilderService;
use Modules\JsAnalyzer\Models\JsSecret;
use Modules\Reconnaissance\Http\Requests\StoreScanRequest;
use Modules\Reconnaissance\Models\Scan;
use Modules\Reconnaissance\Services\PipelineService;
use Modules\Report\Models\Report;
use Modules\Subdomain\Models\Subdomain;

class ScanController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('scans/Index', [
            'scans' => Scan::query()
                ->where('user_id', $request->user()->id)
                ->latest()
                ->get(),
        ]);
    }

    public function store(StoreScanRequest $request, PipelineService $pipeline): JsonResponse|RedirectResponse
    {
        $scan = Scan::query()->create([
            'user_id' => $request->user()->id,
            'domain' => strtolower($request->string('domain')->toString()),
            'options' => $request->validated('options'),
        ]);

        $pipeline->dispatch($scan);

        if ($request->wantsJson()) {
            return response()->json([
                'scan' => $scan->fresh(),
            ], 201);
        }

        return to_route('scans.show', $scan);
    }

    public function show(Request $request, Scan $scan, GraphBuilderService $graphBuilder): JsonResponse|Response
    {
        $this->authorize('view', $scan);

        if ($request->wantsJson()) {
            return response()->json([
                'scan' => $scan->load('logs'),
            ]);
        }

        return Inertia::render('scans/Show', $this->scanPageData($scan, $graphBuilder));
    }

    /**
     * @return array<string, mixed>
     */
    protected function scanPageData(Scan $scan, GraphBuilderService $graphBuilder): array
    {
        $scan->load('logs');

        return [
            'scan' => $scan,
            'report' => Report::query()
                ->where('scan_id', $scan->id)
                ->with('findings')
                ->first(),
            'graph' => $graphBuilder->graphPayload($scan),
            'stats' => [
                'subdomains' => Subdomain::query()->where('scan_id', $scan->id)->count(),
                'endpoints' => Endpoint::query()->where('scan_id', $scan->id)->count(),
                'secrets' => JsSecret::query()->where('scan_id', $scan->id)->count(),
                'technologies' => Technology::query()->where('scan_id', $scan->id)->count(),
            ],
        ];
    }
}
