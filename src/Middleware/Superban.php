<?php

namespace Delaney\Superban\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class Superban extends ThrottleRequests
{
    /**
     * Maximum number of attempts allowed within timeframe
     *
     * @var int
     */
    protected $maxAttempts;

    /**
     * Number of minutes to ban clients
     *
     * @var int
     */
    protected $banTime;

    /**
     * Message to display in HTTP Response
     *
     * @var string
     */
    protected $message;

    /**
     * Limiter signature
     *
     * @var string
     */
    protected $signature = 'superban';

    /**
     * Create a new request throttler.
     *
     * @param  \Illuminate\Cache\RateLimiter  $limiter
     * @return void
     */
    public function __construct(RateLimiter $limiter)
    {
        parent::__construct($limiter);
        $this->message = config('superban.message') ?? 'You have been banned temporarily.';
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  int|string $maxAttempts
     * @param  float|int $decayMinutes
     * @param  float|int $banTime
     * @param  string $prefix
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Http\Exceptions\ThrottleRequestsException
     */
    public function handle(
        $request,
        Closure $next,
        $maxAttempts = 60,
        $decayMinutes = 1,
        $banTime = 0,
        string $prefix = ''
    )
    {
        $this->maxAttempts = (int) $maxAttempts;
        $this->banTime = (int) $banTime;
        $prefix = $this->signature;
        $this->checkRequest($request);

        return $this->handleRequest(
            $request,
            $next,
            [
                (object) [
                    'key' => $prefix.':'.$this->resolveRequestSignature($request),
                    'maxAttempts' => $this->resolveMaxAttempts($request, $this->maxAttempts),
                    'decayMinutes' => $decayMinutes,
                    'responseCallback' => [$this, 'banUser'],
                ],
            ]
        );
    }

    /**
     * Ban a user and/or IP address
     *
     * @param \Illuminate\Http\Request $request
     * @param array $headers
     * @return \Symfony\Component\HttpFoundation\Response
     */

    protected function banUser(Request $request, array $headers)
    {
        $data = $this->getRequestData($request);

        foreach ($data as $key) {
            foreach ($this->getCacheStores() as $store) {
                $store->put($key, true, $this->banTime * 60);
            }
        }

        return $this->getErrorResponse($headers);
    }

    /**
     * Check if a request is from a banned client
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function checkRequest(Request $request)
    {
        $data = $this->getRequestData($request);

        foreach ($data as $key) {
            foreach ($this->getCacheStores() as $store) {
                if ($store->get($key)) {
                    throw new HttpResponseException($this->getErrorResponse());
                }
            }
        }
    }

    /**
     * Get client keys used to check requests
     *
     * @return array $data
     */
    protected function getCacheStores()
    {
        $drivers = config('superban.drivers');

        if (count($drivers)) {
            return array_map(function ($driver) {
                return Cache::driver($driver) ?? Cache::driver();
            }, $drivers);
        }

        return [Cache::driver()];
    }

    /**
     * Get client keys used to check requests
     *
     * @param \Illuminate\Http\Request $request
     * @return array $data
     */

    protected function getRequestData(Request $request)
    {
        $config = config('superban');
        $keys = $config['user_keys'] ?? [];
        $user = $request->user();
        $ipAddress = true;
        if (!$config) {
            $ipAddress = $request->ip();
        } else {
            $ipAddress = config('superban.ban_ip_addresses') ? $request->ip() : null;
        }
        $data = [];

        if ($ipAddress) {
            $data = array_merge($data, [
                $this->signature.':'.$ipAddress,
            ]);
        }

        if ($user && count($keys)) {
            foreach ($keys as $key) {
                if ($value = $user[$key]) {
                    $data = array_merge($data, [$this->signature.':'.$key.':'.$value]);
                }
            }
        }

        return $data;
    }

    /**
     * Create ban response
     *
     * @param array $headers
     * @return \Symfony\Component\HttpFoundation\Response
     */

    protected function getErrorResponse($headers = null)
    {
        if (!$headers) {
            $headers = $this->getHeaders(
                $this->maxAttempts,
                0
            );
        }

        return new Response(
            $this->message,
            Response::HTTP_FORBIDDEN,
            $headers
        );
    }
}
