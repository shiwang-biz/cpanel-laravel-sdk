<?php

namespace Shiwang\CpanelLaravelSdk\Modules;

use Shiwang\CpanelLaravelSdk\WhmClient;

/**
 * Manages email accounts (mailboxes) on individual cPanel accounts.
 *
 * Like DomainManager, these have no direct WHM API 1 equivalent — WHM
 * proxies them through the target cPanel account's classic API 2 `Email`
 * module. Every method requires the cPanel username that owns the mailbox.
 */
class EmailManager
{
    public function __construct(protected WhmClient $client)
    {
    }

    /**
     * @param  int  $quotaMb  Mailbox size limit in megabytes, 0 for unlimited.
     * @return array<string, mixed>
     */
    public function create(string $cpanelUser, string $domain, string $email, string $password, int $quotaMb = 0): array
    {
        return $this->client->cpanelApiRequest($cpanelUser, 'Email', 'addpop', [
            'domain' => $domain,
            'email' => $email,
            'password' => $password,
            'quota' => $quotaMb,
        ], 'POST');
    }

    /**
     * @return array<string, mixed>
     */
    public function delete(string $cpanelUser, string $domain, string $email): array
    {
        return $this->client->cpanelApiRequest($cpanelUser, 'Email', 'delpop', [
            'domain' => $domain,
            'email' => $email,
        ], 'POST');
    }

    /**
     * @return array<string, mixed>
     */
    public function changePassword(string $cpanelUser, string $domain, string $email, string $password): array
    {
        return $this->client->cpanelApiRequest($cpanelUser, 'Email', 'passwdpop', [
            'domain' => $domain,
            'email' => $email,
            'password' => $password,
        ], 'POST');
    }

    /**
     * @param  int  $quotaMb  Mailbox size limit in megabytes, 0 for unlimited.
     * @return array<string, mixed>
     */
    public function editQuota(string $cpanelUser, string $domain, string $email, int $quotaMb): array
    {
        return $this->client->cpanelApiRequest($cpanelUser, 'Email', 'editquota', [
            'domain' => $domain,
            'email' => $email,
            'quota' => $quotaMb,
        ], 'POST');
    }

    /**
     * List email accounts (mailboxes), optionally filtered by domain.
     *
     * @return array<string, mixed>
     */
    public function list(string $cpanelUser, ?string $domain = null): array
    {
        return $this->client->cpanelApiRequest($cpanelUser, 'Email', 'listpopswithdisk', array_filter([
            'domain' => $domain,
        ]));
    }
}
