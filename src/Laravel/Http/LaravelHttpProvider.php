<?php

declare(strict_types=1);

namespace IEXBase\TronAPI\Laravel\Http;

use IEXBase\TronAPI\Exception\NotFoundException;
use IEXBase\TronAPI\Exception\TronException;
use IEXBase\TronAPI\Provider\HttpProviderInterface;
use Illuminate\Support\Facades\Http;

class LaravelHttpProvider implements HttpProviderInterface
{
    protected string $host;
    protected int $timeout;
    protected array $headers;
    protected string $statusPage = '/';

    public function __construct(string $host, int $timeout = 30000, array $headers = [], string $statusPage = '/')
    {
        $this->host = $host;
        $this->timeout = $timeout;
        $this->headers = $headers;
        $this->statusPage = $statusPage;
    }

    public function setStatusPage(string $page = '/'): void
    {
        $this->statusPage = $page;
    }

    public function isConnected(): bool
    {
        $resp = $this->request($this->statusPage);
        return isset($resp['blockID']) || isset($resp['status']);
    }

    public function request($url, array $payload = [], string $method = 'get'): array
    {
        $method = strtoupper($method);
        if (!in_array($method, ['GET','POST'])) {
            throw new TronException('The method is not defined');
        }

        $client = Http::baseUrl($this->host)
            ->withHeaders($this->headers)
            ->timeout(max(1, (int)ceil($this->timeout / 1000)));

        try {
            if ($method === 'GET') {
                $response = $client->get($url, $payload);
            } else {
                $response = $client->asJson()->post($url, $payload);
            }
        } catch (\Throwable $e) {
            throw new TronException($e->getMessage(), $e->getCode(), $e);
        }

        if ($response->status() === 404) {
            throw new NotFoundException('Page not found');
        }

        $body = $response->body();
        if ($body === 'OK') {
            return ['status' => 1];
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            $decoded = [];
        }

        return $decoded;
    }
}

