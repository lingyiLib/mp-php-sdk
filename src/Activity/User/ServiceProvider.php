<?php
/**
 * Created by PhpStorm.
 * User: Qkangkang<q.kk@foxmail.com>
 */

namespace lingyiLib\MiddleSdk\Activity\User;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class ServiceProvider.
 *
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}.
     */
    public function register(Container $app)
    {
        $app['user'] = function ($app) {
            return new Client($app);
        };
    }
}
