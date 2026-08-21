<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SessionPartition
{
    protected array $allowedPartitions = ['admin', 'agency'];

    public function handle(Request $request, Closure $next)
    {
        $partition = $this->determinePartition($request);

        if ($partition) {
            $cookieName = $this->buildCookieName($partition);

            config(['session.cookie' => $cookieName]);
            $request->attributes->set('_session_partition', $partition);
        }

        return $next($request);
    }

    protected function determinePartition(Request $request): ?string
    {
        $headerPartition = strtolower((string) $request->header('X-Session-Partition', ''));
        if ($this->isValidPartition($headerPartition)) {
            return $headerPartition;
        }

        $inputPartition = strtolower((string) $request->input('_session_partition', ''));
        if ($this->isValidPartition($inputPartition)) {
            return $inputPartition;
        }

        $path = trim($request->path(), '/');
        $hintPartition = strtolower((string) $request->cookies->get('_session_partition_hint', ''));

        // Keep auth entry points on the default session cookie so GET/POST /login
        // always share the same CSRF/session context.
        if ($path === 'login' || $path === 'logout') {
            return null;
        }

        if (Str::startsWith($path, 'admin')) {
            return 'admin';
        }

        if (Str::startsWith($path, 'agency')) {
            return 'agency';
        }

        // Shared endpoints under /agencies can be called from both admin and agency panels.
        // Infer partition from current cookies / referer instead of forcing agency.
        if (Str::startsWith($path, 'agencies')) {
            $adminCookie = $this->buildCookieName('admin');
            $agencyCookie = $this->buildCookieName('agency');

            $hasAdminCookie = $request->cookies->has($adminCookie);
            $hasAgencyCookie = $request->cookies->has($agencyCookie);

            if ($hasAdminCookie && !$hasAgencyCookie) {
                return 'admin';
            }

            if ($hasAgencyCookie && !$hasAdminCookie) {
                return 'agency';
            }

            if ($this->isValidPartition($hintPartition)) {
                return $hintPartition;
            }

            // If both cookies exist, use referer to preserve the active panel context.
            $refererPath = trim((string) parse_url((string) $request->headers->get('referer'), PHP_URL_PATH), '/');
            if (Str::startsWith($refererPath, 'admin')) {
                return 'admin';
            }
            if (Str::startsWith($refererPath, 'agency') || Str::startsWith($refererPath, 'agencies')) {
                return 'agency';
            }
        }

        // Generic fallback (e.g. /csrf-refresh): infer partition from available cookies.
        $adminCookie = $this->buildCookieName('admin');
        $agencyCookie = $this->buildCookieName('agency');
        $hasAdminCookie = $request->cookies->has($adminCookie);
        $hasAgencyCookie = $request->cookies->has($agencyCookie);

        if ($hasAdminCookie && !$hasAgencyCookie) {
            return 'admin';
        }

        if ($hasAgencyCookie && !$hasAdminCookie) {
            return 'agency';
        }

        if ($this->isValidPartition($hintPartition)) {
            return $hintPartition;
        }

        // If both cookies exist, try referer one more time.
        $refererPath = trim((string) parse_url((string) $request->headers->get('referer'), PHP_URL_PATH), '/');
        if (Str::startsWith($refererPath, 'admin')) {
            return 'admin';
        }
        if (Str::startsWith($refererPath, 'agency') || Str::startsWith($refererPath, 'agencies')) {
            return 'agency';
        }

        return null;
    }

    protected function isValidPartition(?string $value): bool
    {
        return $value && in_array($value, $this->allowedPartitions, true);
    }

    protected function buildCookieName(string $partition): string
    {
        $configured = config('session.cookie', Str::slug(config('app.name', 'laravel'), '_') . '_session');
        $base = preg_replace('/_(admin|agency)$/', '', (string) $configured) ?: (string) $configured;

        return $base . '_' . $partition;
    }
}






