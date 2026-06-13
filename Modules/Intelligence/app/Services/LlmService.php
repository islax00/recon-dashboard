<?php

namespace Modules\Intelligence\Services;

use Illuminate\Support\Facades\Http;
use Modules\Reconnaissance\Models\Scan;
use Modules\Report\Models\Report;

class LlmService
{
    public function analyze(Scan $scan, string $question): string
    {
        $report = Report::query()->where('scan_id', $scan->id)->first();
        $context = $report?->ai_summary ?? 'No report generated yet.';

        if (blank(config('intelligence.api_key'))) {
            return sprintf(
                'LLM is not configured. Scan context: %s Question: %s',
                $context,
                $question,
            );
        }

        $response = Http::withHeaders([
            'x-api-key' => (string) config('intelligence.api_key'),
            'anthropic-version' => '2023-06-01',
        ])->timeout(30)->post((string) config('intelligence.base_url'), [
            'model' => (string) config('intelligence.model'),
            'max_tokens' => 512,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => "Scan domain: {$scan->domain}\nContext: {$context}\nQuestion: {$question}",
                ],
            ],
        ]);

        if ($response->failed()) {
            return 'Unable to reach the intelligence provider.';
        }

        return (string) data_get($response->json(), 'content.0.text', 'No response returned.');
    }
}
