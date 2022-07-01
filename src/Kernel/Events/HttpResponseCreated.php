<?php
/**
 * Created by PhpStorm.
 * User: Qkangkang<q.kk@foxmail.com>
 */

namespace lingyiLib\MiddleSdk\Kernel\Events;

use Psr\Http\Message\ResponseInterface;

/**
 * Class HttpResponseCreated.
 *
 * @author mingyoung <mingyoungcheung@gmail.com>
 */
class HttpResponseCreated
{
    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    public $response;

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }
}
