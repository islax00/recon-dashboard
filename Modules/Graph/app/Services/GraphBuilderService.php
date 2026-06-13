<?php

namespace Modules\Graph\Services;

use Modules\Core\Enums\NodeType;
use Modules\Crawler\Models\Endpoint;
use Modules\Fingerprint\Models\Technology;
use Modules\Graph\Models\GraphEdge;
use Modules\Graph\Models\GraphNode;
use Modules\JsAnalyzer\Models\JsFile;
use Modules\Network\Models\IpAddress;
use Modules\Reconnaissance\Models\Scan;
use Modules\Subdomain\Models\Subdomain;

class GraphBuilderService
{
    public function build(Scan $scan): array
    {
        GraphEdge::query()->where('scan_id', $scan->id)->delete();
        GraphNode::query()->where('scan_id', $scan->id)->delete();

        $domainNodeId = $this->nodeId('domain', $scan->domain);
        $this->createNode($scan, $domainNodeId, NodeType::Domain, $scan->domain);

        $subdomainMap = [];

        foreach (Subdomain::query()->where('scan_id', $scan->id)->get() as $subdomain) {
            $nodeId = $this->nodeId('subdomain', $subdomain->hostname);
            $subdomainMap[$subdomain->id] = $nodeId;

            $this->createNode($scan, $nodeId, NodeType::Subdomain, $subdomain->hostname, [
                'is_alive' => $subdomain->is_alive,
                'status_code' => $subdomain->status_code,
            ]);

            $this->createEdge($scan, $domainNodeId, $nodeId, 'contains');
        }

        foreach (IpAddress::query()->where('scan_id', $scan->id)->get() as $ipAddress) {
            $nodeId = $this->nodeId('ip', $ipAddress->ip);
            $this->createNode($scan, $nodeId, NodeType::Ip, $ipAddress->ip);

            if ($ipAddress->subdomain_id && isset($subdomainMap[$ipAddress->subdomain_id])) {
                $this->createEdge($scan, $subdomainMap[$ipAddress->subdomain_id], $nodeId, 'resolves_to');
            }
        }

        foreach (Endpoint::query()->where('scan_id', $scan->id)->get() as $endpoint) {
            $nodeId = $this->nodeId('endpoint', $endpoint->url);
            $this->createNode($scan, $nodeId, NodeType::Endpoint, $endpoint->url, [
                'method' => $endpoint->method,
                'status_code' => $endpoint->status_code,
            ]);

            if ($endpoint->subdomain_id && isset($subdomainMap[$endpoint->subdomain_id])) {
                $this->createEdge($scan, $subdomainMap[$endpoint->subdomain_id], $nodeId, 'has_endpoint');
            }
        }

        foreach (JsFile::query()->where('scan_id', $scan->id)->get() as $jsFile) {
            $nodeId = $this->nodeId('js', $jsFile->url);
            $this->createNode($scan, $nodeId, NodeType::JsFile, $jsFile->url, [
                'size' => $jsFile->size,
            ], 'medium');

            if ($jsFile->endpoint_id) {
                $endpoint = Endpoint::query()->find($jsFile->endpoint_id);
                if ($endpoint) {
                    $endpointNodeId = $this->nodeId('endpoint', $endpoint->url);
                    $this->createEdge($scan, $endpointNodeId, $nodeId, 'contains');
                }
            }
        }

        foreach (Technology::query()->where('scan_id', $scan->id)->get() as $technology) {
            $nodeId = $this->nodeId('tech', $technology->name);
            $this->createNode($scan, $nodeId, NodeType::Technology, $technology->name, [
                'version' => $technology->version,
                'category' => $technology->category,
            ]);

            if ($technology->subdomain_id && isset($subdomainMap[$technology->subdomain_id])) {
                $this->createEdge($scan, $subdomainMap[$technology->subdomain_id], $nodeId, 'runs');
            }
        }

        return $this->graphPayload($scan);
    }

    /**
     * @return array{nodes: array<int, array<string, mixed>>, edges: array<int, array<string, mixed>>}
     */
    public function graphPayload(Scan $scan): array
    {
        return [
            'nodes' => GraphNode::query()
                ->where('scan_id', $scan->id)
                ->get()
                ->map(fn (GraphNode $node) => [
                    'id' => $node->node_id,
                    'type' => $node->type,
                    'label' => $node->label,
                    'metadata' => $node->metadata,
                    'risk_level' => $node->risk_level,
                ])
                ->all(),
            'edges' => GraphEdge::query()
                ->where('scan_id', $scan->id)
                ->get()
                ->map(fn (GraphEdge $edge) => [
                    'source' => $edge->source_node_id,
                    'target' => $edge->target_node_id,
                    'relation' => $edge->relation,
                ])
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    protected function createNode(
        Scan $scan,
        string $nodeId,
        NodeType $type,
        string $label,
        ?array $metadata = null,
        string $riskLevel = 'info',
    ): GraphNode {
        return GraphNode::query()->create([
            'scan_id' => $scan->id,
            'node_id' => $nodeId,
            'type' => $type->value,
            'label' => $label,
            'metadata' => $metadata,
            'risk_level' => $riskLevel,
        ]);
    }

    protected function createEdge(Scan $scan, string $source, string $target, string $relation): GraphEdge
    {
        return GraphEdge::query()->create([
            'scan_id' => $scan->id,
            'source_node_id' => $source,
            'target_node_id' => $target,
            'relation' => $relation,
        ]);
    }

    protected function nodeId(string $prefix, string $value): string
    {
        return $prefix.':'.hash('xxh128', strtolower($value));
    }
}
