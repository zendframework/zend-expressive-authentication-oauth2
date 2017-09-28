<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Authentication\OAuth2;

class ConfigProvider
{
    /**
     * Return the configuration array.
     */
    public function __invoke() : array
    {
        return [
            'dependencies'  => $this->getDependencies(),
            'authentication' => include __DIR__ . '/../config/oauth2.php'
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies() : array
    {
        return [
            'aliases' => [
                // Change the alias value for Authentication adapter and
                // UserRepository adapter
                AuthenticationInterface::class => Adapter\BasicAccess::class,
                UserRepositoryInterface::class => UserRepository\Htpasswd::class
            ],
            'factories' => [
                OAuth2Middleware::class => OAuth2MiddlewareFactory::class,
                OAuth2Adapter::class => OAuth2AdapterFactory::class
            ]
        ];
    }
}
