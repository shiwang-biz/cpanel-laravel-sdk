<?php

namespace Shiwang\CpanelLaravelSdk\Modules;

use Shiwang\CpanelLaravelSdk\WhmClient;

/**
 * Manages addon, parked, and subdomains on individual cPanel accounts.
 *
 * These operations have no direct WHM API 1 equivalent — WHM proxies them
 * through the target cPanel account's classic API 2 (AddonDomain, SubDomain,
 * and Park modules), since UAPI has no replacement for these specific
 * functions. Every method here therefore requires the cPanel username that
 * owns the account being modified, in addition to the domain arguments.
 */
class DomainManager
{
    public function __construct(protected WhmClient $client)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function addAddonDomain(string $cpanelUser, string $domain, string $subdomain, string $documentRoot): array
    {
        return $this->client->cpanelApiRequest($cpanelUser, 'AddonDomain', 'addondomain', [
            'newdomain' => $domain,
            'subdomain' => $subdomain,
            'dir' => $documentRoot,
        ], 'POST');
    }

    /**
     * @return array<string, mixed>
     */
    public function deleteAddonDomain(string $cpanelUser, string $domain, string $subdomain): array
    {
        return $this->client->cpanelApiRequest($cpanelUser, 'AddonDomain', 'deladdondomain', [
            'domain' => $domain,
            'subdomain' => $subdomain,
        ], 'POST');
    }

    /**
     * @param  string  $subdomain  The subdomain label only, e.g. 'blog'.
     * @param  string  $rootDomain  The domain it belongs to, e.g. 'example.com'.
     * @return array<string, mixed>
     */
    public function addSubdomain(string $cpanelUser, string $subdomain, string $rootDomain, string $documentRoot): array
    {
        return $this->client->cpanelApiRequest($cpanelUser, 'SubDomain', 'addsubdomain', [
            'domain' => $subdomain,
            'rootdomain' => $rootDomain,
            'dir' => $documentRoot,
        ], 'POST');
    }

    /**
     * @param  string  $fullSubdomain  The full subdomain, e.g. 'blog.example.com'.
     * @return array<string, mixed>
     */
    public function deleteSubdomain(string $cpanelUser, string $fullSubdomain): array
    {
        return $this->client->cpanelApiRequest($cpanelUser, 'SubDomain', 'delsubdomain', [
            'domain' => $fullSubdomain,
        ], 'POST');
    }

    /**
     * @return array<string, mixed>
     */
    public function listSubdomains(string $cpanelUser): array
    {
        return $this->client->cpanelApiRequest($cpanelUser, 'SubDomain', 'listsubdomains');
    }

    /**
     * @param  string  $onTopOfDomain  The account's existing domain to park on top of.
     * @return array<string, mixed>
     */
    public function parkDomain(string $cpanelUser, string $domain, string $onTopOfDomain): array
    {
        return $this->client->cpanelApiRequest($cpanelUser, 'Park', 'park', [
            'domain' => $domain,
            'topdomain' => $onTopOfDomain,
        ], 'POST');
    }

    /**
     * @return array<string, mixed>
     */
    public function unparkDomain(string $cpanelUser, string $domain, string $subdomain): array
    {
        return $this->client->cpanelApiRequest($cpanelUser, 'Park', 'unpark', [
            'domain' => $domain,
            'subdomain' => $subdomain,
        ], 'POST');
    }

    /**
     * @return array<string, mixed>
     */
    public function listParkedDomains(string $cpanelUser): array
    {
        return $this->client->cpanelApiRequest($cpanelUser, 'Park', 'listparkeddomains');
    }
}
