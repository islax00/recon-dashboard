<?php

namespace Modules\Report\Services;

use Modules\Crawler\Models\Endpoint;
use Modules\JsAnalyzer\Models\JsSecret;
use Modules\Reconnaissance\Models\Scan;
use Modules\Report\Models\Finding;
use Modules\Report\Models\Report;
use Modules\Subdomain\Models\Subdomain;

class ReportAggregatorService
{
    /**
     * @var array<string, int>
     */
    protected array $severityWeights = [
        'info' => 0,
        'low' => 5,
        'medium' => 15,
        'high' => 30,
        'critical' => 50,
    ];

    public function aggregate(Scan $scan): Report
    {
        $subdomainsCount = Subdomain::query()->where('scan_id', $scan->id)->count();
        $endpointsCount = Endpoint::query()->where('scan_id', $scan->id)->count();
        $secrets = JsSecret::query()->where('scan_id', $scan->id)->get();
        $secretsCount = $secrets->count();

        $riskScore = min(100, $secrets->sum(fn (JsSecret $secret) => $this->severityWeights[$secret->severity] ?? 10));
        $riskLevel = $this->resolveRiskLevel($riskScore);

        $report = Report::query()->updateOrCreate(
            ['scan_id' => $scan->id],
            [
                'risk_score' => $riskScore,
                'risk_level' => $riskLevel,
                'subdomains_count' => $subdomainsCount,
                'endpoints_count' => $endpointsCount,
                'secrets_count' => $secretsCount,
                'vulnerabilities_count' => 0,
                'ai_summary' => sprintf(
                    'Scan of %s found %d subdomains, %d endpoints, and %d secrets.',
                    $scan->domain,
                    $subdomainsCount,
                    $endpointsCount,
                    $secretsCount,
                ),
            ],
        );

        Finding::query()->where('report_id', $report->id)->delete();

        foreach ($secrets as $secret) {
            Finding::query()->create([
                'report_id' => $report->id,
                'scan_id' => $scan->id,
                'title' => ucfirst(str_replace('_', ' ', $secret->type)),
                'description' => 'Secret found in JavaScript file.',
                'severity' => $this->mapSecretSeverity($secret->severity),
                'type' => 'secret',
                'findable_type' => JsSecret::class,
                'findable_id' => $secret->id,
            ]);
        }

        return $report->fresh(['findings']);
    }

    protected function resolveRiskLevel(int $riskScore): string
    {
        return match (true) {
            $riskScore >= 80 => 'critical',
            $riskScore >= 60 => 'high',
            $riskScore >= 30 => 'medium',
            $riskScore >= 10 => 'low',
            default => 'info',
        };
    }

    protected function mapSecretSeverity(string $severity): string
    {
        return match ($severity) {
            'critical' => 'critical',
            'high' => 'high',
            'medium' => 'medium',
            default => 'low',
        };
    }
}
