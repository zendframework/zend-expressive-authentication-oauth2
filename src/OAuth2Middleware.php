<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Authentication\OAuth2\Entity\UserEntity;

class OAuth2Middleware implements MiddlewareInterface
{
    /**
     * @var AuthorizationServer
     */
    protected $server;

    /**
     * @var ResponseInterface
     */
    protected $responsePrototype;

    /**
     * Constructor
     *
     * @param AuthorizationServer $server
     * @param ResponseInterface $responsePrototype
     */
    public function __construct(AuthorizationServer $server, ResponseInterface $responsePrototype)
    {
        $this->server = $server;
        $this->responsePrototype = $responsePrototype;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $method = $request->getMethod();
        switch (strtoupper($method)) {
            case 'GET':
                return $this->authorizationRequest($request);
            case 'POST':
                return $this->accessTokenRequest($request);
        }
        return $this->responsePrototype->withStatus(501); // Method not implemented
    }

    /**
     * Authorize the request and return an authorization code
     * Used for authorization code grant and implicit grant
     *
     * @see https://oauth2.thephpleague.com/authorization-server/auth-code-grant/
     * @see https://oauth2.thephpleague.com/authorization-server/implicit-grant/
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    protected function authorizationRequest(ServerRequestInterface $request) : ResponseInterface
    {
        try {
            // Validate the HTTP request and return an AuthorizationRequest object.
            $authRequest = $this->server->validateAuthorizationRequest($request);

            // The auth request object can be serialized and saved into a user's session.
            // You will probably want to redirect the user at this point to a login endpoint.

            // Once the user has logged in set the user on the AuthorizationRequest
            $authRequest->setUser(new UserEntity('guest')); // an instance of UserEntityInterface

            // At this point you should redirect the user to an authorization page.
            // This form will ask the user to approve the client and the scopes requested.

            // Once the user has approved or denied the client update the status
            // (true = approved, false = denied)
            $authRequest->setAuthorizationApproved(true);

            // Return the HTTP redirect response
            return $this->server->completeAuthorizationRequest($authRequest, $this->responsePrototype);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($this->responsePrototype);
        } catch (\Exception $exception) {
            return (new OAuthServerException($exception->getMessage(), 0, 'unknown_error', 500))
                ->generateHttpResponse($this->responsePrototype);
        }
    }

    /**
     * Request an access token
     * Used for client credential grant, password grant, and refresh token grant
     *
     * @see https://oauth2.thephpleague.com/authorization-server/client-credentials-grant/
     * @see https://oauth2.thephpleague.com/authorization-server/resource-owner-password-credentials-grant/
     * @see https://oauth2.thephpleague.com/authorization-server/refresh-token-grant/
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    protected function accessTokenRequest(ServerRequestInterface $request) : ResponseInterface
    {
        try {
            return $this->server->respondToAccessTokenRequest($request, $this->responsePrototype);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($this->responsePrototype);
        } catch (\Exception $exception) {
            return (new OAuthServerException($exception->getMessage(), 0, 'unknown_error', 500))
                ->generateHttpResponse($this->responsePrototype);
        }
    }
}
