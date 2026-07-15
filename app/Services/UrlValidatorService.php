<?php

namespace App\Services;

use App\Models\BlockedDomain;
use InvalidArgumentException;

class UrlValidatorService
{
    private const ALLOWED_SCHEMES = ['http', 'https'];
    private const MAX_URL_LENGTH = 2048;

    private const PRIVATE_IP_RANGES = [
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        '127.0.0.0/8',
        '169.254.0.0/16',
        '0.0.0.0/8',
        '100.64.0.0/10',
        '198.18.0.0/15',
        '224.0.0.0/4',
        '240.0.0.0/4',
    ];

    public function validate(string $url): array
    {
        $url = $this->sanitize($url);

        if (strlen($url) > self::MAX_URL_LENGTH) {
            return $this->fail('INVALID_URL', 'URL exceeds maximum length of ' . self::MAX_URL_LENGTH . ' characters.');
        }

        $parsed = parse_url($url);
        if ($parsed === false || !isset($parsed['scheme'], $parsed['host'])) {
            return $this->fail('INVALID_URL', 'The provided URL is not valid.');
        }

        if (!in_array(strtolower($parsed['scheme']), self::ALLOWED_SCHEMES, true)) {
            return $this->fail('INVALID_URL', 'URL scheme must be http or https.');
        }

        if (isset($parsed['user']) || isset($parsed['pass'])) {
            return $this->fail('INVALID_URL', 'URLs with embedded credentials are not allowed.');
        }

        $host = $parsed['host'];

        if ($this->isDomainBlocked($host)) {
            return $this->fail('PRIVATE_URL', 'This domain has been blocked.');
        }

        // Resolve DNS and check for private IPs (SSRF protection)
        $ips = $this->resolveHost($host);
        if (empty($ips)) {
            return $this->fail('FETCH_FAILED', 'Could not resolve the hostname.');
        }

        foreach ($ips as $ip) {
            if ($this->isPrivateIp($ip)) {
                return $this->fail('PRIVATE_URL', 'URL resolves to a private or reserved IP address.');
            }
        }

        return [
            'valid' => true,
            'url' => $url,
            'host' => $host,
            'resolved_ips' => $ips,
        ];
    }

    private function sanitize(string $url): string
    {
        $url = trim($url);
        $url = str_replace("\0", '', $url);
        // Normalize unicode whitespace
        $url = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $url);

        return $url;
    }

    private function resolveHost(string $host): array
    {
        // Check if it's already an IP
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return [$host];
        }

        $records = @dns_get_record($host, DNS_A | DNS_AAAA);
        if ($records === false || empty($records)) {
            // Fallback to gethostbyname
            $ip = @gethostbyname($host);
            if ($ip === $host) {
                return [];
            }
            return [$ip];
        }

        return array_map(fn($r) => $r['ip'] ?? $r['ipv6'] ?? null, $records);
    }

    private function isPrivateIp(string $ip): bool
    {
        if (empty($ip)) {
            return true;
        }

        // Check IPv6 loopback and private ranges
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $lower = strtolower($ip);
            if ($lower === '::1' || str_starts_with($lower, 'fc') || str_starts_with($lower, 'fd') || str_starts_with($lower, 'fe80')) {
                return true;
            }
            // IPv4-mapped IPv6
            if (str_starts_with($lower, '::ffff:')) {
                $ip = substr($lower, 7);
            } else {
                return false;
            }
        }

        // Use PHP's built-in filter
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return true;
        }

        return false;
    }

    private function isDomainBlocked(string $host): bool
    {
        return BlockedDomain::where('domain', $host)->exists();
    }

    public function validateRedirectTarget(string $url): bool
    {
        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['host'])) {
            return false;
        }

        if (!in_array(strtolower($parsed['scheme'] ?? ''), self::ALLOWED_SCHEMES, true)) {
            return false;
        }

        $ips = $this->resolveHost($parsed['host']);
        foreach ($ips as $ip) {
            if ($this->isPrivateIp($ip)) {
                return false;
            }
        }

        return true;
    }

    private function fail(string $code, string $message): array
    {
        return [
            'valid' => false,
            'error_code' => $code,
            'error_message' => $message,
        ];
    }
}
