<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

namespace ZendTest\Expressive\Authentication\OAuth2;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Authentication\OAuth2\OAuth2Middleware;

class OAuth2MiddlewareTest extends TestCase
{
    public function setUp()
    {
        $this->authServer    = $this->prophesize(AuthorizationServer::class);
        $this->response      = $this->prophesize(ResponseInterface::class);
        $this->serverRequest = $this->prophesize(ServerRequestInterface::class);
        $this->authRequest   = $this->prophesize(AuthorizationRequest::class);
        $this->delegate      = $this->prophesize(DelegateInterface::class);
    }

    public function testConstructor()
    {
        $middleware = new OAuth2Middleware(
            $this->authServer->reveal(),
            $this->response->reveal()
        );
        $this->assertInstanceOf(OAuth2Middleware::class, $middleware);
        $this->assertInstanceOf(ServerMiddlewareInterface::class, $middleware);
    }

    public function testProcessWithGet()
    {
        $this->authRequest->setUser(Argument::any())
                          ->willReturn(null);
        $this->authRequest->setAuthorizationApproved(true)
                          ->willReturn(null);

        $this->serverRequest->getMethod()
                            ->willReturn('GET');

        $this->authServer->completeAuthorizationRequest(
            $this->authRequest->reveal(),
            $this->response->reveal()
        )->willReturn($this->response->reveal());
        $this->authServer->validateAuthorizationRequest($this->serverRequest->reveal())
                         ->willReturn($this->authRequest);

        $middleware = new OAuth2Middleware(
            $this->authServer->reveal(),
            $this->response->reveal()
        );
        $response = $middleware->process(
            $this->serverRequest->reveal(),
            $this->delegate->reveal()
        );
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testProcessWithPost()
    {
        $this->serverRequest->getMethod()
                            ->willReturn('POST');

        $this->authServer->respondToAccessTokenRequest(
            $this->serverRequest->reveal(),
            $this->response->reveal()
        )->willReturn($this->response->reveal());

        $middleware = new OAuth2Middleware(
            $this->authServer->reveal(),
            $this->response->reveal()
        );
        $response = $middleware->process(
            $this->serverRequest->reveal(),
            $this->delegate->reveal()
        );
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
