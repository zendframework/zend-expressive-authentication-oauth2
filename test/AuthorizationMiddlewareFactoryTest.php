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
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;
use TypeError;
use Zend\Expressive\Authentication\OAuth2\AuthorizationMiddleware;
use Zend\Expressive\Authentication\OAuth2\AuthorizationMiddlewareFactory;

/**
 * @covers \Zend\Expressive\Authentication\OAuth2\AuthorizationMiddlewareFactory
 */
class AuthorizationMiddlewareFactoryTest extends TestCase
{
    /** @var AuthorizationServer|ObjectProphecy */
    private $authServer;

    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    /** @var ResponseInterface|ObjectProphecy */
    private $response;

    protected function setUp() : void
    {
        $this->container  = $this->prophesize(ContainerInterface::class);
        $this->authServer = $this->prophesize(AuthorizationServer::class);
        $this->response   = $this->prophesize(ResponseInterface::class);
    }

    public function testConstructor()
    {
        $factory = new AuthorizationMiddlewareFactory();
        $this->assertInstanceOf(AuthorizationMiddlewareFactory::class, $factory);
    }

    public function testRaisesTypeErrorForInvalidAuthorizationServer()
    {
        $this->container
            ->get(AuthorizationServer::class)
            ->willReturn(new stdClass());
        $this->container
            ->get(ResponseInterface::class)
            ->willReturn(function () {
            });

        $factory = new AuthorizationMiddlewareFactory();

        $this->expectException(TypeError::class);
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesTypeErrorForNonCallableResponseFactory()
    {
        $this->container
            ->get(AuthorizationServer::class)
            ->willReturn($this->authServer->reveal());
        $this->container
            ->get(ResponseInterface::class)
            ->willReturn(new stdClass());

        $factory = new AuthorizationMiddlewareFactory();

        $this->expectException(TypeError::class);
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesTypeErrorWhenResponseServiceProvidesResponseInstance()
    {
        $this->container
            ->get(AuthorizationServer::class)
            ->willReturn($this->authServer->reveal());
        $this->container
            ->get(ResponseInterface::class)
            ->will([$this->response, 'reveal']);

        $factory = new AuthorizationMiddlewareFactory();

        $this->expectException(TypeError::class);
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsInstanceWhenAppropriateDependenciesArePresentInContainer()
    {
        $this->container
            ->get(AuthorizationServer::class)
            ->willReturn($this->authServer->reveal());
        $this->container
            ->get(ResponseInterface::class)
            ->willReturn(function () {
                return $this->response->reveal();
            });

        $factory = new AuthorizationMiddlewareFactory();
        $middleware = $factory($this->container->reveal());
        $this->assertInstanceOf(AuthorizationMiddleware::class, $middleware);
    }
}
