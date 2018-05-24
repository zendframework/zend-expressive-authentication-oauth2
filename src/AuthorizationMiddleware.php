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
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Authentication\OAuth2\Entity\UserEntity;

use function strtoupper;

/**
 * Implements OAuth2 authorization request validation
 *
 * Performs checks if the OAuth authorization request is valid and populates it
 * to the next handler via the request object as attribute with the key
 * `League\OAuth2\Server\AuthorizationServer`
 *
 * The next handler should take care of checking the resource owner's authentication and
 * consent. It may intercept to ensure authentication and consent before populating it to
 * the authorization request object
 *
 * @see https://oauth2.thephpleague.com/authorization-server/auth-code-grant/
 * @see https://oauth2.thephpleague.com/authorization-server/implicit-grant/
 */
class AuthorizationMiddleware implements MiddlewareInterface
{
    /**
     * @var AuthorizationServer
     */
    protected $server;

    /**
     * @var callable
     */
    protected $responseFactory;

    public function __construct(AuthorizationServer $server, callable $responseFactory)
    {
        $this->server = $server;
        $this->responseFactory = function () use ($responseFactory) : ResponseInterface {
            return $responseFactory();
        };
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $response = ($this->responseFactory)();

        try {
            $authRequest = $this->server->validateAuthorizationRequest($request);

            // The next handler must take care of providing the
            // authenticated user and the approval
            $authRequest->setAuthorizationApproved(false);

            return $handler->handle($request->withAttribute(AuthorizationRequest::class, $authRequest));
        } catch (OAuthServerException $exception) {
            // The validation throws this exception if the request is not valid
            // for example when the client id is invalid
            return $exception->generateHttpResponse($response);
        } catch (\Exception $exception) {
            return (new OAuthServerException($exception->getMessage(), 0, 'unknown_error', 500))
                ->generateHttpResponse($response);
        }
    }
}
