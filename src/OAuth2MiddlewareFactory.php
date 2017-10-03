<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;

class OAuth2MiddlewareFactory
{
    public function __invoke(ContainerInterface $container): OAuth2Middleware
    {
        $clientRepository = $container->has(ClientRepositoryInterface::class) ?
                            $container->get(ClientRepositoryInterface::class) :
                            null;
        if (null === $clientRepository) {
            throw new Exception\InvalidConfigException(
                'OAuth2 Client Repository is missing'
            );
        }
        $accessTokenRepository = $container->has(AccessTokenRepositoryInterface::class) ?
                                 $container->get(AccessTokenRepositoryInterface::class) :
                                 null;
        if (null === $accessTokenRepository) {
            throw new Exception\InvalidConfigException(
                'OAuth2 Access Token Repository is missing'
            );
        }
        $scopeRepository = $container->has(ScopeRepositoryInterface::class) ?
                           $container->get(ScopeRepositoryInterface::class) :
                           null;
        if (null === $scopeRepository) {
            throw new Exception\InvalidConfigException(
                'OAuth2 Access Scope Repository is missing'
            );
        }
        $config = $container->get('config')['authentication'];
        if (!isset($config['private-key'])) {
            throw new Exception\InvalidConfigException(
                'The private-key configuration is missing for OAuth2'
            );
        }
        if (!isset($config['encryption-key'])) {
            throw new Exception\InvalidConfigException(
                'The encryption-key configuration is missing for OAuth2'
            );
        }
        $authServer = new \League\OAuth2\Server\AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            $config['private-key'],
            $config['encryption-key']
        );

        if (! $container->has(ResponseInterface::class)
            && ! class_exists(Response::class)
        ) {
            throw new Exception\InvalidConfigException(sprintf(
                'Cannot create %s service; dependency %s is missing. Either define the service, '
                . 'or install zendframework/zend-diactoros',
                OAuth2Middleware::class,
                ResponseInterface::class
            ));
        }
        $responsePrototype = $container->has(ResponseInterface::class)
            ? $container->get(ResponseInterface::class)
            : new Response();

        return new OAuth2Middleware($authServer, $responsePrototype);
    }
}
