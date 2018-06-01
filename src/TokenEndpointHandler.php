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

use function strtoupper;

/**
 * Provides an OAuth2 token endpoint implementation
 *
 * The token endpoint is required to obtain the access token and optionally a refresh token.
 *
 * @see https://tools.ietf.org/html/rfc6749#section-3.2
 * @see https://oauth2.thephpleague.com/authorization-server/client-credentials-grant/
 * @see https://oauth2.thephpleague.com/authorization-server/resource-owner-password-credentials-grant/
 * @see https://oauth2.thephpleague.com/authorization-server/refresh-token-grant/
 */
class TokenEndpointHandler implements RequestHandlerInterface
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
        $this->responseFactory = $responseFactory;
    }

    private function createResponse(): ResponseInterface
    {
        return ($this->responseFactory)();
    }

    /**
     * Request an access token
     *
     * Used for client credential grant, password grant, and refresh token grant
     *
     * @see https://oauth2.thephpleague.com/authorization-server/client-credentials-grant/
     * @see https://oauth2.thephpleague.com/authorization-server/resource-owner-password-credentials-grant/
     * @see https://oauth2.thephpleague.com/authorization-server/refresh-token-grant/
     * @see https://tools.ietf.org/html/rfc6749#section-3.2
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->createResponse();

        try {
            return $this->server->respondToAccessTokenRequest($request, $response);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        } catch (\Exception $exception) {
            return (new OAuthServerException($exception->getMessage(), 0, 'unknown_error', 500))
                ->generateHttpResponse($response);
        }
    }
}
