<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */
namespace Zend\Expressive\Authentication\OAuth2;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;

trait ResponsePrototypeTrait
{
    protected function getResponsePrototype(ContainerInterface $container) : ResponseInterface
    {
        // @codeCoverageIgnoreStart
        if (! $container->has(ResponseInterface::class)
            && ! class_exists(Response::class)
        ) {
            throw new Exception\InvalidConfigException(sprintf(
                'Cannot create %s service; dependency %s is missing. Either define the service, '
                . 'or install zendframework/zend-diactoros',
                OAuth2Middleware::class,
                ResponseInterface::class
            ));
        }
        // @codeCoverageIgnoreEnd

        return $container->has(ResponseInterface::class)
            ? $container->get(ResponseInterface::class)
            : new Response();
    }
}
