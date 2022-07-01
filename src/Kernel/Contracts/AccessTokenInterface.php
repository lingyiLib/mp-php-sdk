<?php
/**
 * Created by PhpStorm.
 * User: Qkangkang<q.kk@foxmail.com>
 */

namespace lingyiLib\MiddleSdk\Kernel\Contracts;

use Psr\Http\Message\RequestInterface;

/**
 * Interface AuthorizerAccessToken.
 */
interface AccessTokenInterface
{
    /**
     * @return array
     */
    public function getToken(): array;

    /**
     * @return \lingyiLib\MiddleSdk\Kernel\Contracts\AccessTokenInterface
     */
    public function refresh(): self;

    public function applyToRequest(RequestInterface $request, array $requestOptions = []): RequestInterface;
}
