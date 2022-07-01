<?php
/**
 * Created by PhpStorm.
 * User: Qkangkang<q.kk@foxmail.com>
 */

namespace lingyiLib\MiddleSdk;

/**
 * Class Factory.
 *
 * @method static \lingyiLib\MiddleSdk\Activity\Application            activity(array $config)
 */
class Factory
{
    /**
     * @param string $name
     * @param array  $config
     *
     * @return \lingyiLib\MiddleSdk\Kernel\ServiceContainer
     */
    public static function make($name, array $config)
    {
        $namespace = Kernel\Support\Str::studly($name);
        $application = "\\lingyiLib\\MiddleSdk\\{$namespace}\\Application";
        return new $application($config);
    }

    /**
     * Dynamically pass methods to the application.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return self::make($name, ...$arguments);
    }
}
