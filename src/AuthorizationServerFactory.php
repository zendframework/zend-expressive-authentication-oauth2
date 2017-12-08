<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Authentication\OAuth2;

use DateInterval;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Psr\Container\ContainerInterface;

class AuthorizationServerFactory
{
    use RepositoryTrait;

    public function __invoke(ContainerInterface $container) : AuthorizationServer
    {
        $clientRepository = $this->getClientRepository($container);
        $accessTokenRepository = $this->getAccessTokenRepository($container);
        $scopeRepository = $this->getScopeRepository($container);
        $userRepository  = $this->getUserRepository($container);
        $refreshTokenRepository = $this->getRefreshTokenRepository($container);
        $authCodeRepository = $this->getAuthCodeRepository($container);

        $config = $container->get('config')['authentication'] ?? [];
        if (! isset($config['private_key']) || empty($config['private_key'])) {
            throw new Exception\InvalidConfigException(
                'The private_key value is missing in config authentication'
            );
        }
        if (! isset($config['encryption_key']) || empty($config['encryption_key'])) {
            throw new Exception\InvalidConfigException(
                'The encryption_key value is missing in config authentication'
            );
        }
        if (! isset($config['access_token_expire'])) {
            throw new Exception\InvalidConfigException(
                'The access_token_expire value is missing in config authentication'
            );
        }
        if (! isset($config['refresh_token_expire'])) {
            throw new Exception\InvalidConfigException(
                'The refresh_token_expire value is missing in config authentication'
            );
        }
        if (! isset($config['auth_code_expire'])) {
            throw new Exception\InvalidConfigException(
                'The auth_code_expire value is missing in config authentication'
            );
        }
        $authServer = new AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            $config['private_key'],
            $config['encryption_key']
        );

        // Enable Client credentials grant
        $authServer->enableGrantType(
            new Grant\ClientCredentialsGrant(),
            new DateInterval($config['access_token_expire'])
        );

        // Enable Password grant
        $authServer->enableGrantType(
            $this->getPasswordGrant(
                $userRepository,
                $refreshTokenRepository,
                new DateInterval($config['refresh_token_expire'])
            ),
            new DateInterval($config['access_token_expire'])
        );

        // Enable Authentication Code grant
        $authServer->enableGrantType(
            $this->getAuthCodeGrant(
                $authCodeRepository,
                $refreshTokenRepository,
                new DateInterval($config['auth_code_expire']),
                new DateInterval($config['refresh_token_expire'])
            ),
            new DateInterval($config['access_token_expire'])
        );

        // Enable Implicit grant
        $authServer->enableGrantType(
            new Grant\ImplicitGrant(
                new DateInterval($config['access_token_expire'])
            ),
            new DateInterval($config['access_token_expire'])
        );

        // Enable Refresh token grant
        $authServer->enableGrantType(
            $this->getRefreshTokenGrant(
                $refreshTokenRepository,
                new DateInterval($config['refresh_token_expire'])
            ),
            new DateInterval($config['access_token_expire'])
        );

        return $authServer;
    }

    protected function getAuthCodeGrant(
        AuthCodeRepositoryInterface $authCodeRepo,
        RefreshTokenRepositoryInterface $refreshTokenRepo,
        DateInterval $code_expire,
        DateInterval $refresh_token_expire
    ) {
        $grant = new Grant\AuthCodeGrant(
            $authCodeRepo,
            $refreshTokenRepo,
            $code_expire
        );
        $grant->setRefreshTokenTTL($refresh_token_expire);
        return $grant;
    }

    protected function getPasswordGrant(
        UserRepositoryInterface $userRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        DateInterval $refresh_token_expire
    ) {
        $grant = new Grant\PasswordGrant(
            $userRepository,
            $refreshTokenRepository
        );
        $grant->setRefreshTokenTTL($refresh_token_expire);
        return $grant;
    }

    protected function getRefreshTokenGrant(
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        DateInterval $refreshTokenExpire
    ) {
        $grant = new Grant\RefreshTokenGrant($refreshTokenRepository);
        $grant->setRefreshTokenTTL($refreshTokenExpire);
        return $grant;
    }
}
