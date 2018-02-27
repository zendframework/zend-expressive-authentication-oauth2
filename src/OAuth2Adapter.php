<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Authentication\OAuth2;

use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Authentication\AuthenticationInterface;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Authentication\UserRepository\UserTrait;

class OAuth2Adapter implements AuthenticationInterface
{
    use UserTrait;

    /**
     * @var ResourceServer
     */
    protected $resourceServer;

    /**
     * @var callable
     */
    protected $responseFactory;

    /**
     * Constructor
     *
     * @param ResourceServer $resourceServer
     * @param ResponseInterface $responsePrototype
     */
    public function __construct(ResourceServer $resourceServer, callable $responseFactory)
    {
        $this->resourceServer = $resourceServer;
        $this->responseFactory = function () use ($responseFactory) : ResponseInterface {
            return $responseFactory();
        };
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(ServerRequestInterface $request) : ?UserInterface
    {
        try {
            $result = $this->resourceServer->validateAuthenticatedRequest($request);
            $userId = $result->getAttribute('oauth_user_id', false);
            if (false !== $userId) {
                return $this->generateUser($userId, []);
            }
        } catch (OAuthServerException $exception) {
            return null;
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function unauthorizedResponse(ServerRequestInterface $request) : ResponseInterface
    {
        return ($this->responseFactory)()
            ->withHeader(
                'WWW-Authenticate',
                'Bearer token-example'
            )
            ->withStatus(401);
    }
}
