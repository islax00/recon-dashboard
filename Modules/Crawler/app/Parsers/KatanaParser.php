<?php

namespace Modules\Crawler\Parsers;

use Modules\Core\Contracts\ReconParserInterface;

class KatanaParser implements ReconParserInterface
{
    public function tool(): string
    {
        return 'katana';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function parse(string $output): array
    {
        return collect(preg_split('/\R/', trim($output)) ?: [])
            ->filter(fn (string $line) => filter_var(trim($line), FILTER_VALIDATE_URL) !== false)
            ->map(fn (string $url) => [
                'url' => trim($url),
                'method' => 'GET',
            ])
            ->unique('url')
            ->values()
            ->all();
    }
}
