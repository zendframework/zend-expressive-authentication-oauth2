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
use function sprintf;

class AuthorizationMiddlewareFactory
{
    use AuthFlowFactoryTrait;

    public function __invoke(ContainerInterface $container) : AuthorizationMiddleware
    {
        return new AuthorizationMiddleware(
            $this->getAuthorizationServer($container),
            $container->get(ResponseInterface::class)
        );
    }
}
