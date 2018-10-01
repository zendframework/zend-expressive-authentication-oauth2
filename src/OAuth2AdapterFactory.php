<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Authentication\OAuth2;

use League\OAuth2\Server\ResourceServer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Authentication\UserInterface;

class OAuth2AdapterFactory
{
    public function __invoke(ContainerInterface $container) : OAuth2Adapter
    {
        $resourceServer = $container->has(ResourceServer::class)
            ? $container->get(ResourceServer::class)
            : null;

        if (null === $resourceServer) {
            throw new Exception\InvalidConfigException(
                'OAuth2 resource server is missing for authentication'
            );
        }

        return new OAuth2Adapter(
            $resourceServer,
            $container->get(ResponseInterface::class),
            $container->get(UserInterface::class)
        );
    }
}
