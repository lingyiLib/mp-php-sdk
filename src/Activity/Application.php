<?php
/**
 * Created by PhpStorm.
 * User: Qkangkang<q.kk@foxmail.com>
 */

namespace lingyiLib\MiddleSdk\Activity;

use lingyiLib\MiddleSdk\Kernel\ServiceContainer;

/**
 * Class Application.
 *
 * @property User\Client               $user
 */
class Application extends ServiceContainer
{
    /**
     * @var array
     */
    protected $providers = [
        Auth\ServiceProvider::class,
        User\ServiceProvider::class,
    ];
}
