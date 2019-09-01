<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Zend\Expressive\Authentication\OAuth2\AuthorizationMiddleware;

class AuthorizationMiddlewareTest extends TestCase
{
    /** @var AuthorizationRequest|ObjectProphecy */
    private $authRequest;

    /** @var AuthorizationServer|ObjectProphecy */
    private $authServer;

    /** @var RequestHandlerInterface|ObjectProphecy */
    private $handler;

    /** @var ResponseInterface|ObjectProphecy */
    private $response;

    /** @var callable */
    private $responseFactory;

    /** @var ServerRequestInterface|ObjectProphecy */
    private $serverRequest;

    protected function setUp() : void
    {
        $this->authServer      = $this->prophesize(AuthorizationServer::class);
        $this->response        = $this->prophesize(ResponseInterface::class);
        $this->serverRequest   = $this->prophesize(ServerRequestInterface::class);
        $this->authRequest     = $this->prophesize(AuthorizationRequest::class);
        $this->handler         = $this->prophesize(RequestHandlerInterface::class);
        $this->responseFactory = function () {
            return $this->response->reveal();
        };
    }

    public function testConstructor()
    {
        $middleware = new AuthorizationMiddleware(
            $this->authServer->reveal(),
            $this->responseFactory
        );

        $this->assertInstanceOf(AuthorizationMiddleware::class, $middleware);
        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
    }

    public function testProcess()
    {
        $this->authRequest
            ->setUser(Argument::any())
            ->shouldNotBeCalled(); // Ths middleware must not provide a user entity
        $this->authRequest
            ->setAuthorizationApproved(false) // Expect approval to be set to false only
            ->willReturn(null);

        // Mock a valid authorization request
        $this->authServer
            ->validateAuthorizationRequest($this->serverRequest->reveal())
            ->willReturn($this->authRequest->reveal());

        // Mock a instance immutability when the authorization request
        // is populated
        $newRequest = $this->prophesize(ServerRequestInterface::class);
        $this->serverRequest
             ->withAttribute(AuthorizationRequest::class, $this->authRequest->reveal())
             ->willReturn($newRequest->reveal());

        // Expect the handler to be called with the new modified request,
        // that contains the auth request attribute
        $handlerResponse = $this->prophesize(ResponseInterface::class)->reveal();
        $this->handler
            ->handle($newRequest->reveal())
            ->willReturn($handlerResponse);


        $middleware = new AuthorizationMiddleware(
            $this->authServer->reveal(),
            $this->responseFactory
        );
        $response = $middleware->process(
            $this->serverRequest->reveal(),
            $this->handler->reveal()
        );

        $this->assertSame($handlerResponse, $response);
    }

    public function testAuthorizationRequestRaisingOAuthServerExceptionGeneratesResponseFromException()
    {
        $response = $this->prophesize(ResponseInterface::class);
        $oauthServerException = $this->prophesize(OAuthServerException::class);
        $oauthServerException
            ->generateHttpResponse(Argument::type(ResponseInterface::class))
            ->willReturn($response->reveal());

        $this->authServer
            ->validateAuthorizationRequest($this->serverRequest->reveal())
            ->willThrow($oauthServerException->reveal());

        $middleware = new AuthorizationMiddleware(
            $this->authServer->reveal(),
            $this->responseFactory
        );

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

        $this->response->getBody()->willReturn($body->reveal())->shouldBeCalled();
        $this->response
            ->withHeader(Argument::type('string'), Argument::type('string'))
            ->willReturn($this->response->reveal())
            ->shouldBeCalled();
        $this->response
            ->withStatus(500)
            ->willReturn($this->response->reveal())
            ->shouldBeCalled();

        $exception = new RuntimeException('oauth2 server error');

        $this->authServer
            ->validateAuthorizationRequest($this->serverRequest->reveal())
            ->willThrow($exception);

        $middleware = new AuthorizationMiddleware(
            $this->authServer->reveal(),
            $this->responseFactory
        );

        $response = $middleware->process(
            $this->serverRequest->reveal(),
            $this->handler->reveal()
        );

        $this->assertSame($this->response->reveal(), $response);
    }
}
