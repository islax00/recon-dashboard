<?php

namespace Modules\JsAnalyzer\Services;

use Illuminate\Support\Facades\Http;
use Modules\Core\Contracts\ReconToolInterface;
use Modules\Core\DTOs\ReconResultDto;
use Modules\Core\DTOs\ScanDto;
use Modules\Crawler\Models\Endpoint;
use Modules\JsAnalyzer\Models\JsFile;
use Modules\JsAnalyzer\Models\JsSecret;

class JsAnalyzerService implements ReconToolInterface
{
    /**
     * @var array<string, array{pattern: string, severity: string}>
     */
    protected array $patterns = [
        'api_key' => ['pattern' => '/(?:api[_-]?key|apikey)\s*[:=]\s*[\'"]([a-zA-Z0-9_\-]{16,})[\'"]/i', 'severity' => 'high'],
        'aws_key' => ['pattern' => '/AKIA[0-9A-Z]{16}/', 'severity' => 'critical'],
        'token' => ['pattern' => '/(?:token|bearer)\s*[:=]\s*[\'"]([a-zA-Z0-9_\-\.]{12,})[\'"]/i', 'severity' => 'medium'],
        'password' => ['pattern' => '/(?:password|passwd|pwd)\s*[:=]\s*[\'"]([^\'"]{6,})[\'"]/i', 'severity' => 'high'],
    ];

    public function name(): string
    {
        return 'js_analyzer';
    }

    public function run(ScanDto $scan): ReconResultDto
    {
        $endpoints = Endpoint::query()
            ->where('scan_id', $scan->id)
            ->where(function ($query) {
                $query->where('url', 'like', '%.js')
                    ->orWhere('content_type', 'like', '%javascript%');
            })
            ->get();

        $items = [];

        foreach ($endpoints as $endpoint) {
            $jsFile = JsFile::query()->updateOrCreate(
                ['scan_id' => $scan->id, 'url' => $endpoint->url],
                ['endpoint_id' => $endpoint->id, 'is_analyzed' => false],
            );

            $content = $this->fetchContent($endpoint->url);

            if ($content === null) {
                continue;
            }

            $jsFile->update([
                'size' => strlen($content),
                'is_analyzed' => true,
            ]);

            foreach ($this->findSecrets($content) as $secret) {
                $record = JsSecret::query()->create([
                    'scan_id' => $scan->id,
                    'js_file_id' => $jsFile->id,
                    'type' => $secret['type'],
                    'value' => $secret['value'],
                    'severity' => $secret['severity'],
                    'line_number' => $secret['line_number'],
                    'confidence' => $secret['confidence'],
                ]);

                $items[] = [
                    'id' => $record->id,
                    'type' => $record->type,
                    'severity' => $record->severity,
                    'url' => $endpoint->url,
                ];
            }
        }

        return new ReconResultDto(
            scanId: $scan->id,
            tool: $this->name(),
            success: true,
            items: $items,
            metadata: ['count' => count($items)],
        );
    }

    protected function fetchContent(string $url): ?string
    {
        try {
            $response = Http::timeout(10)->get($url);

            return $response->successful() ? $response->body() : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<int, array{type: string, value: string, severity: string, line_number: int, confidence: float}>
     */
    protected function findSecrets(string $content): array
    {
        $secrets = [];
        $lines = preg_split('/\R/', $content) ?: [];

        foreach ($lines as $index => $line) {
            foreach ($this->patterns as $type => $config) {
                if (preg_match($config['pattern'], $line, $matches) !== 1) {
                    continue;
                }

                $secrets[] = [
                    'type' => $type,
                    'value' => $matches[1] ?? $matches[0],
                    'severity' => $config['severity'],
                    'line_number' => $index + 1,
                    'confidence' => 0.9,
                ];
            }
        }

        return $secrets;
    }
}
