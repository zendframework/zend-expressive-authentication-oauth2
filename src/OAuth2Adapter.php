<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Authentication\Adapter;

use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Authentication\AuthenticationInterface;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Authentication\UserRepository\UserTrait;

class OAuth2 implements AuthenticationInterface
{
    use UserTrait;

    protected $resourceServer;
    protected $config;

    public function __construct(ResourceServer $resourceServer, array $config)
    {
        $this->resourceServer = $resourceServer;
        $this->config = $config;
    }

    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        try {
            $result = $this->resourceServer->validateAuthenticatedRequest($request);
            $userId = $result->getAttribute('oauth_user_id', false);
            if (false !== $userId) {
                return $this->generateUser(
                    $result->getAttribute('oauth_user_id'),
                    ''
                );
            }
        } catch (OAuthServerException $exception) {
            return null;
        }
        return null;
    }
}
