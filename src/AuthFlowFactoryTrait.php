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
use function sprintf;

/**
 * Provides helper methods for authorization flow related factories
 * @internal
 */
trait AuthFlowFactoryTrait
{
    private function getAuthorizationServer(ContainerInterface $container): AuthorizationServer
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

        return $authServer;
    }
}