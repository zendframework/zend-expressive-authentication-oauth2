<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class OAuth2MiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : OAuth2Middleware
    {
        $authServer = $container->has(AuthorizationServer::class)
            ? $container->get(AuthorizationServer::class)
            : null;

        if (null === $authServer) {
            throw new Exception\InvalidConfigException(sprintf(
                "The %s service is missing",
                AuthorizationServer::class
            ));
        }

        return new OAuth2Middleware(
            $authServer,
            $container->get(ResponseInterface::class)
        );
    }
}
