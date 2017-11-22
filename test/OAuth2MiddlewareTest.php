<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

namespace ZendTest\Expressive\Authentication\OAuth2;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Zend\Expressive\Authentication\OAuth2\OAuth2Middleware;

class OAuth2MiddlewareTest extends TestCase
{
    public function setUp()
    {
        $this->authServer    = $this->prophesize(AuthorizationServer::class);
        $this->response      = $this->prophesize(ResponseInterface::class);
        $this->serverRequest = $this->prophesize(ServerRequestInterface::class);
        $this->authRequest   = $this->prophesize(AuthorizationRequest::class);
        $this->handler       = $this->prophesize(RequestHandlerInterface::class);
    }

    public function testConstructor()
    {
        $middleware = new OAuth2Middleware(
            $this->authServer->reveal(),
            $this->response->reveal()
        );
        $this->assertInstanceOf(OAuth2Middleware::class, $middleware);
        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
    }

    public function testProcessWithGet()
    {
        $this->authRequest
            ->setUser(Argument::any())
            ->willReturn(null);
        $this->authRequest
            ->setAuthorizationApproved(true)
            ->willReturn(null);

        $this->serverRequest
            ->getMethod()
            ->willReturn('GET');

        $this->authServer
            ->completeAuthorizationRequest(
                $this->authRequest->reveal(),
                $this->response->reveal()
            )
            ->willReturn($this->response->reveal());
        $this->authServer
            ->validateAuthorizationRequest($this->serverRequest->reveal())
            ->willReturn($this->authRequest);

        $middleware = new OAuth2Middleware(
            $this->authServer->reveal(),
            $this->response->reveal()
        );
        $response = $middleware->process(
            $this->serverRequest->reveal(),
            $this->handler->reveal()
        );
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testProcessWithPost()
    {
        $this->serverRequest->getMethod()
                            ->willReturn('POST');

        $this->authServer
            ->respondToAccessTokenRequest(
                $this->serverRequest->reveal(),
                $this->response->reveal()
            )
            ->willReturn($this->response->reveal());

        $middleware = new OAuth2Middleware(
            $this->authServer->reveal(),
            $this->response->reveal()
        );
        $response = $middleware->process(
            $this->serverRequest->reveal(),
            $this->handler->reveal()
        );
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testAuthorizationRequestRaisingOAuthServerExceptionGeneratesResponseFromException()
    {
        $response = $this->prophesize(ResponseInterface::class);
        $oauthServerException = $this->prophesize(OAuthServerException::class);
        $oauthServerException
            ->generateHttpResponse(Argument::type(ResponseInterface::class))
            ->will([$response, 'reveal']);

        $this->authServer
            ->validateAuthorizationRequest(
                Argument::that([$this->serverRequest, 'reveal'])
            )
            ->willThrow($oauthServerException->reveal());

        $middleware = new OAuth2Middleware(
            $this->authServer->reveal(),
            $this->response->reveal()
        );

        $this->serverRequest->getMethod()->willReturn('GET');

        $result = $middleware->process(
            $this->serverRequest->reveal(),
            $this->handler->reveal()
        );

        $this->assertSame($response->reveal(), $result);
    }

    public function testAuthorizationRequestRaisingUnknownExceptionGeneratesResponseFromException()
    {
        $body = $this->prophesize(StreamInterface::class);
        $body
            ->write(Argument::containingString('oauth2 server error'))
            ->shouldBeCalled();

        $this->response->getBody()->will([$body, 'reveal'])->shouldBeCalled();
        $this->response
            ->withHeader(Argument::type('string'), Argument::type('string'))
            ->will([$this->response, 'reveal'])
            ->shouldBeCalled();
        $this->response
            ->withStatus(500)
            ->will([$this->response, 'reveal'])
            ->shouldBeCalled();

        $exception = new RuntimeException('oauth2 server error');

        $this->authServer
            ->validateAuthorizationRequest(
                Argument::that([$this->serverRequest, 'reveal'])
            )
            ->willThrow($exception);

        $middleware = new OAuth2Middleware(
            $this->authServer->reveal(),
            $this->response->reveal()
        );

        $this->serverRequest->getMethod()->willReturn('GET');

        $response = $middleware->process(
            $this->serverRequest->reveal(),
            $this->handler->reveal()
        );

        $this->assertSame($this->response->reveal(), $response);
    }

    public function testReturns501ResponseForInvalidMethods()
    {
        $this->serverRequest->getMethod()->willReturn('UNKNOWN');
        $this->response->withStatus(501)->will([$this->response, 'reveal']);

        $middleware = new OAuth2Middleware(
            $this->authServer->reveal(),
            $this->response->reveal()
        );

        $response = $middleware->process(
            $this->serverRequest->reveal(),
            $this->handler->reveal()
        );

        $this->assertSame($this->response->reveal(), $response);
    }

    public function testPostRequestResultingInOAuthServerExceptionUsesExceptionToGenerateResponse()
    {
        $this->serverRequest->getMethod()->willReturn('POST');

        $exception = $this->prophesize(OAuthServerException::class);
        $exception
            ->generateHttpResponse(Argument::that([$this->response, 'reveal']))
            ->will([$this->response, 'reveal']);

        $this->authServer
            ->respondToAccessTokenRequest(
                Argument::that([$this->serverRequest, 'reveal']),
                Argument::that([$this->response, 'reveal'])
            )
            ->willThrow($exception->reveal());

        $middleware = new OAuth2Middleware(
            $this->authServer->reveal(),
            $this->response->reveal()
        );

        $response = $middleware->process(
            $this->serverRequest->reveal(),
            $this->handler->reveal()
        );

        $this->assertSame($this->response->reveal(), $response);
    }

    public function testPostRequestResultingInGenericExceptionCastsExceptionToOauthServerExceptionToGenerateResponse()
    {
        $this->serverRequest->getMethod()->willReturn('POST');

        $exception = new RuntimeException('runtime-exception', 500);

        $body = $this->prophesize(StreamInterface::class);
        $body->write(Argument::containingString('runtime-exception'))->shouldBeCalled();

        $this->response
            ->withHeader('Content-type', 'application/json')
            ->will([$this->response, 'reveal']);

        $this->response
            ->getBody()
            ->will([$body, 'reveal']);

        $this->response
            ->withStatus(500)
            ->will([$this->response, 'reveal']);

        $this->authServer
            ->respondToAccessTokenRequest(
                Argument::that([$this->serverRequest, 'reveal']),
                Argument::that([$this->response, 'reveal'])
            )
            ->willThrow($exception);

        $middleware = new OAuth2Middleware(
            $this->authServer->reveal(),
            $this->response->reveal()
        );

        $response = $middleware->process(
            $this->serverRequest->reveal(),
            $this->handler->reveal()
        );

        $this->assertSame($this->response->reveal(), $response);
    }
}
