<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseService
{
    private string $url;
    private string $key;
    private bool $enabled;

    public function __construct()
    {
        $this->url = rtrim((string) env('SUPABASE_URL', ''), '/');
        $this->key = (string) env('SUPABASE_ANON_KEY', '');
        $this->enabled = $this->url !== '' && $this->key !== '';
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function headers(array $extra = []): array
    {
        return array_merge([
            'apikey' => $this->key,
            'Authorization' => 'Bearer ' . $this->key,
            'Accept' => 'application/json',
        ], $extra);
    }

    public function insert(string $table, array $rows, array $options = []): array
    {
        return $this->request('post', $table, $rows, $options);
    }

    public function upsert(string $table, array $rows, array $options = [], ?string $onConflict = null): array
    {
        $qs = $onConflict ? ('?on_conflict=' . urlencode($onConflict)) : '';
        return $this->request('post', $table . $qs, $rows, $options + ['prefer' => 'resolution=merge-duplicates,return=representation']);
    }

    public function update(string $table, array $filters, array $data, array $options = []): array
    {
        $qs = $this->buildFilters($filters);
        return $this->request('patch', $table . $qs, $data, $options + ['prefer' => 'return=representation']);
    }

    public function delete(string $table, array $filters, array $options = []): array
    {
        $qs = $this->buildFilters($filters);
        return $this->request('delete', $table . $qs, [], $options);
    }

    private function buildFilters(array $filters): string
    {
        if (empty($filters)) return '';
        $parts = [];
        foreach ($filters as $k => $v) {
            $parts[] = $k . '=eq.' . urlencode((string) $v);
        }
        return '?' . implode('&', $parts);
    }

    private function request(string $method, string $path, array $body, array $options): array
    {
        if (!$this->enabled) {
            return ['ok' => false, 'status' => 503, 'error' => 'SUPABASE_DISABLED'];
        }
        $headers = $this->headers(isset($options['prefer']) ? ['Prefer' => $options['prefer']] : []);
        $url = $this->url . '/rest/v1/' . ltrim($path, '/');
        try {
            $resp = match (strtolower($method)) {
                'post' => Http::withHeaders($headers)->post($url, $body),
                'patch' => Http::withHeaders($headers)->patch($url, $body),
                'delete' => Http::withHeaders($headers)->delete($url),
                'get' => Http::withHeaders($headers)->get($url, $body),
                default => Http::withHeaders($headers)->send(strtoupper($method), $url, ['json' => $body]),
            };
            if ($resp->successful()) {
                $json = $resp->json();
                // POST/UPSERT returns array of rows; PATCH may return array or object
                $data = $json;
                return ['ok' => true, 'status' => $resp->status(), 'data' => $data, 'raw' => $resp];
            }
            $bodyText = $resp->body();
            $err = $this->classifyError($resp->status(), $bodyText);
            return ['ok' => false, 'status' => $resp->status(), 'error' => $err['code'], 'message' => $err['message'], 'raw' => $resp];
        } catch (\Throwable $e) {
            Log::warning('Supabase request error', ['method' => $method, 'path' => $path, 'error' => $e->getMessage()]);
            return ['ok' => false, 'status' => 0, 'error' => 'NETWORK_ERROR', 'message' => $e->getMessage()];
        }
    }

    private function classifyError(int $status, string $body): array
    {
        $msg = trim($body);
        $code = 'UNKNOWN_ERROR';
        if (in_array($status, [401, 403], true)) {
            $code = 'UNAUTHORIZED';
            if (stripos($body, 'row-level security') !== false || stripos($body, 'RLS') !== false) {
                $code = 'RLS_DENIED';
            }
        } elseif ($status >= 500) {
            $code = 'SERVER_ERROR';
        } elseif ($status === 400) {
            $code = 'BAD_REQUEST';
        } elseif ($status === 404) {
            $code = 'NOT_FOUND';
        }
        return ['code' => $code, 'message' => $msg !== '' ? $msg : $code];
    }
}
