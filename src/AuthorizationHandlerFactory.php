<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

final class AuthorizationHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new AuthorizationHandler(
            $container->get(AuthorizationServer::class),
            $container->get(ResponseInterface::class)
        );
    }
}
