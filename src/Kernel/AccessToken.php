<?php
/**
 * Created by PhpStorm.
 * User: Qkangkang<q.kk@foxmail.com>
 */

namespace lingyiLib\MiddleSdk\Kernel;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use lingyiLib\MiddleSdk\Kernel\Contracts\AccessTokenInterface;
use lingyiLib\MiddleSdk\Kernel\Exceptions\HttpException;
use lingyiLib\MiddleSdk\Kernel\Exceptions\InvalidArgumentException;
use lingyiLib\MiddleSdk\Kernel\Exceptions\RuntimeException;
use lingyiLib\MiddleSdk\Kernel\Traits\HasHttpRequests;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AccessToken.
 *
 */
abstract class AccessToken implements AccessTokenInterface
{
    use HasHttpRequests;

    /**
     * @var \lingyiLib\MiddleSdk\Kernel\ServiceContainer
     */
    protected $app;

    /**
     * @var array
     */
    protected $appConfig;

    /**
     * @var string
     */
    protected $requestMethod = 'POST';

    /**
     * @var string
     */
    protected $endpointToGetToken;

    /**
     * @var string
     */
    protected $queryName;

    /**
     * @var array
     */
    protected $token;

    /**
     * @var string
     */
    protected $tokenKey = 'accessToken';

    /**
     * @var string
     */
    protected $cachePrefix = 'lingyiLib.kernel.access_token.';

    /**
     * AccessToken constructor.
     *
     * @param \lingyiLib\MiddleSdk\Kernel\ServiceContainer $app
     */
    public function __construct(ServiceContainer $app)
    {
        $this->app = $app;
        $this->appConfig = $app->getConfig();
    }

    /**
     * @return array
     *
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\InvalidConfigException
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\InvalidArgumentException
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\RuntimeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getRefreshedToken(): array
    {
        return $this->getToken(true);
    }

    /**
     * @param bool $refresh
     *
     * @return array
     *
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\InvalidConfigException
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\InvalidArgumentException
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\RuntimeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getToken(bool $refresh = false): array
    {
        $cacheKey = $this->getCacheKey();
        if (!$refresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        /** @var array $token */
        $token = $this->requestToken($this->getCredentials(), true);
        $this->setToken($token[$this->tokenKey], $token['expires_in'] ?? 7200);
        $this->app->events->dispatch(new Events\AccessTokenRefreshed($this));
        return $token;
    }

    /**
     * @param string $token
     * @param int    $lifetime
     *
     * @return \lingyiLib\MiddleSdk\Kernel\Contracts\AccessTokenInterface
     *
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\InvalidArgumentException
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\RuntimeException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setToken(string $token, int $lifetime = 7200): AccessTokenInterface
    {
        Cache::add($this->getCacheKey(), [
            $this->tokenKey => $token,
            'expires_in' => $lifetime,
        ], $lifetime);
        if (!Cache::has($this->getCacheKey())) {
            throw new RuntimeException('Failed to cache access token.');
        }
        return $this;
    }

    /**
     * @return \lingyiLib\MiddleSdk\Kernel\Contracts\AccessTokenInterface
     *
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\InvalidConfigException
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\InvalidArgumentException
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\RuntimeException
     */
    public function refresh(): AccessTokenInterface
    {
        $this->getToken(true);
        return $this;
    }

    /**
     * @param array $credentials
     * @param bool  $toArray
     *
     * @return \Psr\Http\Message\ResponseInterface|\lingyiLib\MiddleSdk\Kernel\Support\Collection|array|object|string
     *
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\HttpException
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\InvalidConfigException
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\InvalidArgumentException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function requestToken(array $credentials, $toArray = false)
    {
        $response = $this->sendRequest($credentials);
        $result = json_decode($response->getBody()->getContents(), true);
        $formatted = $this->castResponseToType($response, $this->app['config']->get('response_type'));
        if (empty($result['data'][$this->tokenKey])) {
            throw new HttpException('Request access_token fail: '.json_encode($result, JSON_UNESCAPED_UNICODE), $response, $formatted);
        }
        return $toArray ? $result['data'] : $formatted['data'];
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     * @param array                              $requestOptions
     *
     * @return \Psr\Http\Message\RequestInterface
     *
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\InvalidConfigException
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\InvalidArgumentException
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\RuntimeException
     */
    public function applyToRequest(RequestInterface $request, array $requestOptions = []): RequestInterface
    {
        parse_str($request->getUri()->getQuery(), $query);
        $query = http_build_query(array_merge($this->getQuery(), $query));
        return $request->withUri($request->getUri()->withQuery($query));
    }

    /**
     * Send http request.
     *
     * @param array $credentials
     *
     * @return ResponseInterface
     *
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\InvalidArgumentException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendRequest(array $credentials): ResponseInterface
    {
        $options = [
            ('GET' === $this->requestMethod) ? 'query' : 'json' => $credentials,
        ];
        return $this->setHttpClient($this->app['http_client'])->request($this->appConfig['config']['url'].$this->getEndpoint(), $this->requestMethod, $options);
    }

    /**
     * @return string
     */
    protected function getCacheKey()
    {
        return $this->cachePrefix.md5(json_encode($this->getCredentials()));
    }

    /**
     * The request query will be used to add to the request.
     *
     * @return array
     *
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\InvalidConfigException
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\InvalidArgumentException
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\RuntimeException
     */
    protected function getQuery(): array
    {
        return [$this->queryName ?? $this->tokenKey => $this->getToken()[$this->tokenKey]];
    }

    /**
     * @return string
     *
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\InvalidArgumentException
     */
    public function getEndpoint(): string
    {
        if (empty($this->endpointToGetToken)) {
            throw new InvalidArgumentException('No endpoint for access token request.');
        }
        return $this->endpointToGetToken;
    }

    /**
     * @return string
     */
    public function getTokenKey()
    {
        return $this->tokenKey;
    }

    /**
     * Credential for get token.
     *
     * @return array
     */
    abstract protected function getCredentials(): array;
}
