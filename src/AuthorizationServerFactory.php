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
use Psr\Container\ContainerInterface;

/**
 * Factory for OAuth AuthorizationServer
 *
 * Initializes a new AuthorizationServer with required params from config.
 * Then configured grant types are enabled with configured access token
 * expiry. Then any optionally configured event listeners are attached to the
 * AuthorizationServer.
 */
class AuthorizationServerFactory
{
    use ConfigTrait;
    use CryptKeyTrait;
    use RepositoryTrait;

    /**
     * @param ContainerInterface $container
     *
     * @return AuthorizationServer
     */
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
            // Config may set this grant to null. Continue on if grant has been disabled
            if (empty($grant)) {
                continue;
            }

            $authServer->enableGrantType(
                $container->get($grant),
                $accessTokenInterval
            );
        }

        // add listeners if configured
        $this->addListeners($authServer, $container);

        return $authServer;
    }

    /**
     * Optionally add event listeners
     *
     * @param AuthorizationServer $authServer
     * @param ContainerInterface  $container
     */
    private function addListeners(
        AuthorizationServer $authServer,
        ContainerInterface $container
    ): void {
        $listeners = $this->getListenersConfig($container);
        if (null === $listeners) {
            return;
        }
        foreach ($listeners as $listenerConfig) {
            $event = $listenerConfig[0];
            $listener = $listenerConfig[1];
            $priority = $listenerConfig[2] ?? null;
            if (is_string($listener)) {
                $listener = $container->get($listener);
            }
            $authServer->getEmitter()
                ->addListener($event, $listener, $priority);
        }
    }
}
