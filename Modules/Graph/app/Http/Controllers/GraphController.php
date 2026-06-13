<?php

namespace Modules\Graph\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Graph\Services\GraphBuilderService;
use Modules\Reconnaissance\Models\Scan;

class GraphController extends Controller
{
    public function show(Scan $scan, GraphBuilderService $graphBuilder): JsonResponse
    {
        $this->authorize('view', $scan);

        return response()->json([
            'graph' => $graphBuilder->graphPayload($scan),
        ]);
    }
}
