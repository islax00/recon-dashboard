<?php

namespace Modules\Intelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Intelligence\Http\Requests\ChatRequest;
use Modules\Intelligence\Services\LlmService;
use Modules\Reconnaissance\Models\Scan;

class ChatController extends Controller
{
    public function store(ChatRequest $request, Scan $scan, LlmService $llm): JsonResponse
    {
        $this->authorize('view', $scan);

        $answer = $llm->analyze($scan, $request->string('message')->toString());

        return response()->json([
            'answer' => $answer,
        ]);
    }
}
