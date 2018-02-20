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
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo;

class ConfigProvider
{
    /**
     * Return the configuration array.
     */
    public function __invoke() : array
    {
        return [
            'dependencies'   => $this->getDependencies(),
            'authentication' => include __DIR__ . '/../config/oauth2.php',
            'routes'         => $this->getRoutes()
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies() : array
    {
        return [
            'aliases' => [
                // Choose a different adapter changing the alias value
                AccessTokenRepositoryInterface::class => Pdo\AccessTokenRepository::class,
                AuthCodeRepositoryInterface::class => Pdo\AuthCodeRepository::class,
                ClientRepositoryInterface::class => Pdo\ClientRepository::class,
                RefreshTokenRepositoryInterface::class => Pdo\RefreshTokenRepository::class,
                ScopeRepositoryInterface::class => Pdo\ScopeRepository::class,
                UserRepositoryInterface::class => Pdo\UserRepository::class
            ],
            'factories' => [
                OAuth2Middleware::class => OAuth2MiddlewareFactory::class,
                OAuth2Adapter::class => OAuth2AdapterFactory::class,
                AuthorizationServer::class => AuthorizationServerFactory::class,
                ResourceServer::class => ResourceServerFactory::class,
                // Pdo adapter
                Pdo\PdoService::class => Pdo\PdoServiceFactory::class,
                Pdo\AccessTokenRepository::class => Pdo\AccessTokenRepositoryFactory::class,
                Pdo\AuthCodeRepository::class => Pdo\AuthCodeRepositoryFactory::class,
                Pdo\ClientRepository::class => Pdo\ClientRepositoryFactory::class,
                Pdo\RefreshTokenRepository::class => Pdo\RefreshTokenRepositoryFactory::class,
                Pdo\ScopeRepository::class => Pdo\ScopeRepositoryFactory::class,
                Pdo\UserRepository::class => Pdo\UserRepositoryFactory::class
            ]
        ];
    }

    public function getRoutes() : array
    {
        return [
            [
                'name'            => 'oauth',
                'path'            => '/oauth',
                'middleware'      => OAuth2Middleware::class,
                'allowed_methods' => ['GET', 'POST']
            ],
        ];
    }
}
