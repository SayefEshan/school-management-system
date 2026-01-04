<?php

namespace Modules\ActivityLog\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequestResponse
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $request_params = $request->all();
        if (isset($request_params['password'])) {
            $request_params['password'] = str_repeat('*', strlen($request_params['password']));
        }
        if (isset($request_params['password_confirmation'])) {
            $request_params['password_confirmation'] = str_repeat('*', strlen($request_params['password_confirmation']));
        }

        if ($request->is('api/*') && $request->method() !== 'OPTIONS') {
            try {
                $user = $request->user();
                try {
                    $responseLog = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
                } catch (\Throwable $e) {
                    $responseLog = $response->getContent();
                }
                Log::channel('daily_api')->info($request->method() . " ::: " . $request->fullUrl(), [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'user' => $user ? [
                        'id' => $user->id,
                        'last_name' => $user->last_name ?? null,
                        'phone' => $user->phone ?? null,
                        'email' => $user->email ?? null,
                    ] : null,
                    'parameters' => $request_params,
                    'status_code' => $response->getStatusCode(),
                    'headers' => $this->filterHeaders($request->headers->all(), ['authorization', 'cookie']),
                    'response' => $responseLog,
                ]);
            } catch (\Throwable $e) {
                Log::channel('daily_api')->error("Could not log API request", [
                    'error' => $e,
                    'request' => $request_params,
                ]);
            }

            return $response;
        }

        if (str_contains($request->fullUrl(), 'get-unread-notification') || str_contains($request->fullUrl(), 'server-info')) {
            return $response;
        }
        try {
            $user = $request->user();
            Log::channel('daily_admin')->info($request->method() . " ::: " . $request->fullUrl(), [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user' => $user ? [
                    'id' => $user->id,
                    'last_name' => $user->last_name ?? null,
                    'phone' => $user->phone ?? null,
                    'email' => $user->email ?? null,
                ] : null,
                'parameters' => $request_params,
                'headers' => $request->headers->all(),
                'status_code' => $response->getStatusCode(),
                'response' => $this->handleResponse($response, $request),
            ]);
        } catch (\Throwable $e) {
            Log::channel('daily_admin')->error("Could not log Admin request", [
                'error' => $e->getMessage(),
                'request' => $request_params,
            ]);
        }

        return $response;
    }

    private function filterHeaders(array $headers, array $exclude): array
    {
        return array_filter($headers, function ($key) use ($exclude) {
            return !in_array(strtolower($key), $exclude, true);
        }, ARRAY_FILTER_USE_KEY);
    }

    private function handleResponse(Response $response, Request $request): array|string
    {
        try {
            if (!$response->getContent()) {
                return [];
            }
            if ($request->isMethod('GET')) {
                return [];
            }
            if ($response->getStatusCode() === 204) {
                return [];
            }
            if ($response->getStatusCode() === 302) {
                return ['redirect' => $response->headers->get('Location')];
            }
            if ($response->getStatusCode() >= 400) {
                try {
                    return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
                } catch (\Throwable $e) {
                    return ['error' => $response->getContent()];
                }
            }
            return $response->getContent();
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
