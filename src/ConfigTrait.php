<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Authentication\OAuth2;

use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Authentication\OAuth2\Exception\InvalidConfigException;

trait ConfigTrait
{
    protected function getPrivateKey(ContainerInterface $container)
    {
        $config = $container->get('config')['authentication'] ?? [];

        if (! isset($config['private_key']) || empty($config['private_key'])) {
            throw new InvalidConfigException(
                'The private_key value is missing in config authentication'
            );
        }

        return $config['private_key'];
    }

    protected function getEncryptionKey(ContainerInterface $container) : string
    {
        $config = $container->get('config')['authentication'] ?? [];

        if (! isset($config['encryption_key']) || empty($config['encryption_key'])) {
            throw new InvalidConfigException(
                'The encryption_key value is missing in config authentication'
            );
        }

        return $config['encryption_key'];
    }

    protected function getAccessTokenExpire(ContainerInterface $container) : string
    {
        $config = $container->get('config')['authentication'] ?? [];

        if (! isset($config['access_token_expire'])) {
            throw new InvalidConfigException(
                'The access_token_expire value is missing in config authentication'
            );
        }

        return $config['access_token_expire'];
    }

    protected function getRefreshTokenExpire(ContainerInterface $container) : string
    {
        $config = $container->get('config')['authentication'] ?? [];

        if (! isset($config['refresh_token_expire'])) {
            throw new InvalidConfigException(
                'The refresh_token_expire value is missing in config authentication'
            );
        }

        return $config['refresh_token_expire'];
    }

    protected function getAuthCodeExpire(ContainerInterface $container) : string
    {
        $config = $container->get('config')['authentication'] ?? [];

        if (! isset($config['auth_code_expire'])) {
            throw new Exception\InvalidConfigException(
                'The auth_code_expire value is missing in config authentication'
            );
        }

        return $config['auth_code_expire'];
    }

    protected function getGrantsConfig(ContainerInterface $container) : array
    {
        $config = $container->get('config')['authentication'] ?? [];

        if (empty($config['grants'])) {
            throw new InvalidConfigException(
                'The grants value is missing in config authentication and must be an array'
            );
        }
        if (! is_array($config['grants'])) {
            throw new InvalidConfigException(
                'The grants must be an array value'
            );
        }

        return $config['grants'];
    }

    /**
     * @param ContainerInterface $container
     *
     * @return array|null
     */
    protected function getListenersConfig(ContainerInterface $container) : ?array
    {
        $config = $container->get('config')['authentication'] ?? [];

        if (empty($config['listeners'])) {
            return null;
        }
        if (! is_array($config['listeners'])) {
            throw new InvalidConfigException(
                'The listeners must be an array value'
            );
        }

        return $config['listeners'];
    }
}
