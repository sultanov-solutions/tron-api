<?php

declare(strict_types=1);

namespace IEXBase\TronAPI\Laravel;

use IEXBase\TronAPI\Tron;
use IEXBase\TronAPI\Laravel\Http\LaravelHttpProvider;
use Illuminate\Support\Manager;
use Illuminate\Support\Facades\Cache;

class TronManager extends Manager
{
    public function getDefaultDriver()
    {
        return $this->app['config']['tron.default'] ?? 'mainnet';
    }

    /**
     * Create connection by name from config/tron.php
     * Supports rotating API keys when multiple keys are provided in config/env.
     * You can override rotation by passing explicit 'api_key' via make($name, ['api_key' => '...']).
     */
    public function createDriver($name)
    {
        return $this->make($name);
    }

    /**
     * Build a fresh Tron instance with optional overrides.
     * Example: app(TronManager::class)->make('mainnet', ['api_key' => 'HARDCODED']);
     */
    public function make($name = null, array $override = [])
    {
        $name = $name ?? $this->getDefaultDriver();
        $connections = $this->app['config']['tron.connections'] ?? [];
        $opts = $connections[$name] ?? [];

        // Prepare API key with rotation (if not overridden)
        $apiKey = $override['api_key'] ?? null;
        if ($apiKey === null) {
            $apiKey = $this->resolveApiKey($name, $opts);
        }

        $headers = $opts['headers'] ?? [];
        if (!empty($apiKey)) {
            $headers['TRON-PRO-API-KEY'] = $apiKey;
        }

        $timeout = (int)($opts['timeout_ms'] ?? 30000);
        $factory = function($host, $timeoutMs, $headersArg, $status) {
            return new LaravelHttpProvider($host, $timeoutMs, $headersArg, $status);
        };

        return Tron::init([
            'network' => $override['network'] ?? ($opts['network'] ?? 'mainnet'),
            'endpoints' => $override['endpoints'] ?? ($opts['endpoints'] ?? []),
            'timeout_ms' => $override['timeout_ms'] ?? $timeout,
            'headers' => $override['headers'] ?? $headers,
            'auth' => $override['auth'] ?? ($opts['auth'] ?? []),
            'private_key' => $override['private_key'] ?? ($opts['private_key'] ?? null),
            'address' => $override['address'] ?? ($opts['address'] ?? null),
            'http_provider_factory' => $override['http_provider_factory'] ?? $factory,
            'api_key' => $apiKey, // still set to ensure headers in core path
        ]);
    }

    protected function resolveApiKey(string $connectionName, array $opts): ?string
    {
        // Support comma-separated env TRON_API_KEY, explicit array api_keys, or single api_key
        $keys = [];
        if (!empty($opts['api_keys']) && is_array($opts['api_keys'])) {
            $keys = array_values(array_filter(array_map('trim', $opts['api_keys']), fn($v)=>$v!==''));
        } elseif (!empty($opts['api_key']) && is_string($opts['api_key'])) {
            $parts = array_map('trim', explode(',', $opts['api_key']));
            $keys = array_values(array_filter($parts, fn($v)=>$v!==''));
        }

        if (count($keys) <= 1) {
            return $keys[0] ?? null;
        }

        $cacheKey = 'tron_api_key_index_'.$connectionName;
        $index = Cache::get($cacheKey, -1);
        $index = is_numeric($index) ? (int)$index : -1;
        $next = ($index + 1) % count($keys);
        Cache::forever($cacheKey, $next);
        Cache::forever($cacheKey.':value', $keys[$next]);
        return $keys[$next];
    }
}
