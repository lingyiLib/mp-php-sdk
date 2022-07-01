<?php
/**
 * Created by PhpStorm.
 * User: Qkangkang<q.kk@foxmail.com>
 */

namespace lingyiLib\MiddleSdk\Kernel\Events;

Use lingyiLib\MiddleSdk\Kernel\AccessToken;

/**
 * Class AccessTokenRefreshed.
 *
 * @author mingyoung <mingyoungcheung@gmail.com>
 */
class AccessTokenRefreshed
{
    public $accessToken;

    /**
     * @param \lingyiLib\MiddleSdk\Kernel\AccessToken $accessToken
     */
    public function __construct(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;
    }
}
