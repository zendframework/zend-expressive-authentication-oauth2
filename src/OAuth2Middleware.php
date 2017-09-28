<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Authentication\OAuth2;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class OAuth2Middleware implements ServerMiddlewareInterface
{
    protected $server;
    protected $config;

    public function __construct(AuthorizationServer $server, array $config)
    {
        $this->server = $server;
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        try {
            return $this->server->respondToAccessTokenRequest($request, new Response());
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse(new Response());
        } catch (\Exception $exception) {
            return (new OAuthServerException($exception->getMessage(), 0, 'unknown_error', 500))
            ->generateHttpResponse(new Response());
        }
    }
}
