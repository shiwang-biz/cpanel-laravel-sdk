<?php

namespace Shiwang\CpanelLaravelSdk\Modules;

use Shiwang\CpanelLaravelSdk\WhmClient;

class DnsManager
{
    public function __construct(protected WhmClient $client)
    {
    }

    /**
     * Create a new DNS zone for a domain.
     *
     * @param  array<string, mixed>  $extra  Additional params, e.g. ['trueowner' => 'cpuser']
     * @return array<string, mixed>
     */
    public function create(string $domain, string $ip, array $extra = []): array
    {
        return $this->client->request('adddns', array_merge($extra, [
            'domain' => $domain,
            'ip' => $ip,
        ]), 'POST');
    }

    /**
     * @return array<string, mixed>
     */
    public function delete(string $domain): array
    {
        return $this->client->request('killdns', [
            'domain' => $domain,
        ], 'POST');
    }

    /**
     * Dump the full DNS zone (all records) for a domain.
     *
     * @return array<string, mixed>
     */
    public function dump(string $domain): array
    {
        return $this->client->request('dumpzone', [
            'domain' => $domain,
        ]);
    }

    /**
     * Reset a DNS zone back to its default records.
     *
     * @return array<string, mixed>
     */
    public function reset(string $domain): array
    {
        return $this->client->request('resetzone', [
            'domain' => $domain,
        ], 'POST');
    }

    /**
     * Add, edit, or remove individual zone records.
     *
     * WHM's editzone expects raw record-indexed params (e.g.
     * 'add' => [['record' => 'www', 'type' => 'A', 'ttl' => 14400, 'data' => '1.2.3.4']]
     * or 'line0' / 'record0' style edits) — pass them through as documented
     * in the WHM API 1 editzone reference.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function editZone(string $domain, array $params): array
    {
        return $this->client->request('editzone', array_merge($params, [
            'domain' => $domain,
        ]), 'POST');
    }
}
