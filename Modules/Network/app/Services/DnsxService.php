<?php

namespace Modules\Network\Services;

use Modules\Core\Contracts\ReconToolInterface;
use Modules\Core\DTOs\ReconResultDto;
use Modules\Core\DTOs\ScanDto;
use Modules\Network\Models\IpAddress;
use Modules\Subdomain\Models\Subdomain;

class DnsxService implements ReconToolInterface
{
    public function name(): string
    {
        return 'dnsx';
    }

    public function run(ScanDto $scan): ReconResultDto
    {
        $subdomains = Subdomain::query()
            ->where('scan_id', $scan->id)
            ->get();

        $items = [];

        foreach ($subdomains as $subdomain) {
            $records = dns_get_record($subdomain->hostname, DNS_A + DNS_AAAA);

            if ($records === false || $records === []) {
                continue;
            }

            foreach ($records as $record) {
                if (! isset($record['ip']) && ! isset($record['ipv6'])) {
                    continue;
                }

                $ip = $record['ip'] ?? $record['ipv6'];

                $ipAddress = IpAddress::query()->updateOrCreate(
                    ['scan_id' => $scan->id, 'ip' => $ip],
                    ['subdomain_id' => $subdomain->id],
                );

                $subdomain->update(['ip_address' => $ip]);

                $items[] = [
                    'hostname' => $subdomain->hostname,
                    'ip' => $ip,
                    'ip_address_id' => $ipAddress->id,
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
}
