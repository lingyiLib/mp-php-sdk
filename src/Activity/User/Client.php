<?php
/**
 * Created by PhpStorm.
 * User: Qkangkang<q.kk@foxmail.com>
 */

namespace lingyiLib\MiddleSdk\Activity\User;

use lingyiLib\MiddleSdk\Kernel\BaseClient;

/**
 * Class Client.
 */
class Client extends BaseClient
{

    /**
     * @param array $userIds
     * @return array|\lingyiLib\MiddleSdk\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \lingyiLib\MiddleSdk\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUserByUserIds(array $userIds)
    {
        return $this->httpPostJson('/mp-api-user/api/user/findListByUserIds', ['userIds' => $userIds]);
    }

}
