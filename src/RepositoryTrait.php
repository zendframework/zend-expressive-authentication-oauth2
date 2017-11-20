<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

namespace Zend\Expressive\Authentication\OAuth2;

use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Psr\Container\ContainerInterface;

trait RepositoryTrait
{
    protected function getUserRepository(ContainerInterface $container) : UserRepositoryInterface
    {
        if (! $container->has(UserRepositoryInterface::class)) {
            throw new Exception\InvalidConfigException(
                'OAuth2 User Repository is missing'
            );
        }
        return $container->get(UserRepositoryInterface::class);
    }

    protected function getScopeRepository(ContainerInterface $container) : ScopeRepositoryInterface
    {
        if (! $container->has(ScopeRepositoryInterface::class)) {
            throw new Exception\InvalidConfigException(
                'OAuth2 Scope Repository is missing'
            );
        }
        return $container->get(ScopeRepositoryInterface::class);
    }

    protected function getAccessTokenRepository(ContainerInterface $container) : AccessTokenRepositoryInterface
    {
        if (! $container->has(AccessTokenRepositoryInterface::class)) {
            throw new Exception\InvalidConfigException(
                'OAuth2 Access Token Repository is missing'
            );
        }
        return $container->get(AccessTokenRepositoryInterface::class);
    }

    protected function getClientRepository(ContainerInterface $container) : ClientRepositoryInterface
    {
        if (! $container->has(ClientRepositoryInterface::class)) {
            throw new Exception\InvalidConfigException(
                'OAuth2 Client Repository is missing'
            );
        }
        return $container->get(ClientRepositoryInterface::class);
    }

    protected function getRefreshTokenRepository(ContainerInterface $container) : RefreshTokenRepositoryInterface
    {
        if (! $container->has(RefreshTokenRepositoryInterface::class)) {
            throw new Exception\InvalidConfigException(
                'OAuth2 Refresk Token Repository is missing'
            );
        }
        return $container->get(RefreshTokenRepositoryInterface::class);
    }

    protected function getAuthCodeRepository(ContainerInterface $container) : AuthCodeRepositoryInterface
    {
        if (! $container->has(AuthCodeRepositoryInterface::class)) {
            throw new Exception\InvalidConfigException(
                'OAuth2 Refresk Token Repository is missing'
            );
        }
        return $container->get(AuthCodeRepositoryInterface::class);
    }
}
