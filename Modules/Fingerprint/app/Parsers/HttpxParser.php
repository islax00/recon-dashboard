<?php

namespace Modules\Fingerprint\Parsers;

use Modules\Core\Contracts\ReconParserInterface;

class HttpxParser implements ReconParserInterface
{
    public function tool(): string
    {
        return 'httpx';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function parse(string $output): array
    {
        return collect(preg_split('/\R/', trim($output)) ?: [])
            ->filter()
            ->map(function (string $line) {
                $decoded = json_decode($line, true);

                return is_array($decoded) ? $decoded : null;
            })
            ->filter()
            ->values()
            ->all();
    }
}
