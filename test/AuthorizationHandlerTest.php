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
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;
use TypeError;

/**
 * @covers \Zend\Expressive\Authentication\OAuth2\AuthorizationHandler
 */
class AuthorizationHandlerTest extends TestCase
{
    public function testHandleUsesAuthorizationServerService(): void
    {
        $server = $this->prophesize(AuthorizationServer::class);
        $response = $this->prophesize(ResponseInterface::class);
        $authRequest = $this->prophesize(AuthorizationRequest::class);
        $request = $this->prophesize(ServerRequestInterface::class);
        $expectedResponse = $response->reveal();

        $request->getAttribute(AuthorizationRequest::class)
            ->willReturn($authRequest->reveal());

        $server->completeAuthorizationRequest($authRequest->reveal(), $expectedResponse)
            ->shouldBeCalled()
            ->willReturn($expectedResponse);

        $subject = new AuthorizationHandler($server->reveal(), function() use ($expectedResponse): ResponseInterface {
            return $expectedResponse;
        });

        self::assertSame($expectedResponse, $subject->handle($request->reveal()));
    }

    public function testInvalidResponseFactoryThrowsTypeError()
    {
        $server = $this->prophesize(AuthorizationServer::class);
        $authRequest = $this->prophesize(AuthorizationRequest::class);
        $request = $this->prophesize(ServerRequestInterface::class);

        $request->getAttribute(AuthorizationRequest::class)
            ->willReturn($authRequest->reveal());

        $server->completeAuthorizationRequest(Argument::any())
            ->shouldNotBeCalled();

        $subject = new AuthorizationHandler($server->reveal(), function() {
            return new stdClass();
        });

        $this->expectException(TypeError::class);
        $subject->handle($request->reveal());
    }
}
