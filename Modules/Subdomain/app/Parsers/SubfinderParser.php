<?php

namespace Modules\Subdomain\Parsers;

use Modules\Core\Contracts\ReconParserInterface;

class SubfinderParser implements ReconParserInterface
{
    public function tool(): string
    {
        return 'subfinder';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function parse(string $output): array
    {
        return collect(preg_split('/\R/', trim($output)) ?: [])
            ->filter()
            ->map(fn (string $hostname) => ['hostname' => strtolower(trim($hostname))])
            ->unique('hostname')
            ->values()
            ->all();
    }
}
