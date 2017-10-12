<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Authentication\OAuth2;

use League\OAuth2\Server\ResourceServer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Authentication\AuthenticationInterface;
use Zend\Expressive\Authentication\OAuth2\OAuth2Adapter;
use Zend\Expressive\Authentication\UserInterface;

class OAuth2AdapterTest extends TestCase
{
    public function setUp()
    {
        $this->resourceServer = $this->prophesize(ResourceServer::class);
        $this->responsePrototype = $this->prophesize(ResponseInterface::class);
    }

    public function testConstructor()
    {
        $adapter = new OAuth2Adapter(
            $this->resourceServer->reveal(),
            $this->responsePrototype->reveal()
        );
        $this->assertInstanceOf(OAuth2Adapter::class, $adapter);
        $this->assertInstanceOf(AuthenticationInterface::class, $adapter);
    }

    public function testAuthenticate()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $requestAfterAuth = $this->prophesize(ServerRequestInterface::class);
        $requestAfterAuth->getAttribute('oauth_user_id', false)
                         ->willReturn('user');

        $this->resourceServer->validateAuthenticatedRequest($request->reveal())
                             ->willReturn($requestAfterAuth->reveal());

        $adapter = new OAuth2Adapter(
            $this->resourceServer->reveal(),
            $this->responsePrototype->reveal()
        );
        $authenticatedUser = $adapter->authenticate($request->reveal());
        $this->assertInstanceOf(UserInterface::class, $authenticatedUser);
        $this->assertEquals('user', $authenticatedUser->getUsername());
    }

    public function testUnauthorizedResponse()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $requestAfterAuth = $this->prophesize(ServerRequestInterface::class);
        $requestAfterAuth->getAttribute('oauth_user_id', false)
                         ->willReturn(false);

        $this->resourceServer->validateAuthenticatedRequest($request->reveal())
                             ->willReturn($requestAfterAuth->reveal());

        $this->responsePrototype->withStatus(401)
                                ->willReturn($this->responsePrototype->reveal());

        $adapter = new OAuth2Adapter(
            $this->resourceServer->reveal(),
            $this->responsePrototype->reveal()
        );
        $authenticatedUser = $adapter->authenticate($request->reveal());
        $this->assertNull($authenticatedUser);

        $result = $adapter->unauthorizedResponse($request->reveal());
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
}
