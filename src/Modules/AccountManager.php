<?php

namespace Shiwang\CpanelLaravelSdk\Modules;

use Shiwang\CpanelLaravelSdk\WhmClient;

class AccountManager
{
    public function __construct(protected WhmClient $client)
    {  
    }

    /**
     * Create a new cPanel account.
     *
     * Required keys: username, domain, password (or contactemail with a
     * generated password), plan (the WHM package name).
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function create(array $params): array
    {
        return $this->client->request('createacct', $params, 'POST');
    }

    /**
     * @return array<string, mixed>
     */
    public function suspend(string $username, ?string $reason = null): array
    {
        return $this->client->request('suspendacct', array_filter([
            'user' => $username,
            'reason' => $reason,
        ]), 'POST');
    }

    /**
     * @return array<string, mixed>
     */
    public function unsuspend(string $username): array
    {
        return $this->client->request('unsuspendacct', [
            'user' => $username,
        ], 'POST');
    }

    /**
     * @return array<string, mixed>
     */
    public function terminate(string $username, bool $keepDns = false): array
    {
        return $this->client->request('removeacct', [
            'user' => $username,
            'keepdns' => $keepDns ? 1 : 0,
        ], 'POST');
    }

    /**
     * List accounts, optionally filtered (e.g. ['search' => 'example.com', 'searchtype' => 'domain']).
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function list(array $filters = []): array
    {
        return $this->client->request('listaccts', $filters);
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(string $username): array
    {
        return $this->client->request('accountsummary', [
            'user' => $username,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function changePackage(string $username, string $package): array
    {
        return $this->client->request('changepackage', [
            'user' => $username,
            'pkg' => $package,
        ], 'POST');
    }

    /**
     * @return array<string, mixed>
     */
    public function changePassword(string $username, string $password): array
    {
        return $this->client->request('passwd', [
            'user' => $username,
            'password' => $password,
        ], 'POST');
    }

    /**
     * @param  int  $quotaMb  Disk quota in megabytes, 0 for unlimited.
     * @return array<string, mixed>
     */
    public function editQuota(string $username, int $quotaMb): array
    {
        return $this->client->request('editquota', [
            'user' => $username,
            'quota' => $quotaMb,
        ], 'POST');
    }
}
