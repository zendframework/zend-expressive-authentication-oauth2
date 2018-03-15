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
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Authentication\OAuth2\Exception\InvalidConfigException;

class AuthorizationServerFactory
{
    use ConfigTrait;
    use CryptKeyTrait;
    use RepositoryTrait;

    public function __invoke(ContainerInterface $container) : AuthorizationServer
    {
        $clientRepository = $this->getClientRepository($container);
        $accessTokenRepository = $this->getAccessTokenRepository($container);
        $scopeRepository = $this->getScopeRepository($container);

        $privateKey = $this->getCryptKey($this->getPrivateKey($container), 'authentication.private_key');
        $encryptKey = $this->getEncryptionKey($container);
        $grants = $this->getGrantsConfig($container);

        $authServer = new AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            $privateKey,
            $encryptKey
        );

        $accessTokenInterval = new DateInterval($this->getAccessTokenExpire($container));

        foreach ($grants as $grant) {
            // Config may set this grant to null.  Continue on if grant has been disabled
            if (empty($grant)) {
                continue;
            }

            $authServer->enableGrantType(
                $container->get($grant),
                $accessTokenInterval
            );
        }

        return $authServer;
    }
}
