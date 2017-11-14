<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Authentication\OAuth2;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Server\AuthorizationServer;
use Zend\Expressive\Authentication\OAuth2\OAuth2Middleware;
use Zend\Expressive\Authentication\OAuth2\OAuth2MiddlewareFactory;

class OAuth2MiddlewareFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container  = $this->prophesize(ContainerInterface::class);
        $this->authServer = $this->prophesize(AuthorizationServer::class);
        $this->response   = $this->prophesize(ResponseInterface::class);
    }

    public function testConstructor()
    {
        $factory = new OAuth2MiddlewareFactory();
        $this->assertInstanceOf(OAuth2MiddlewareFactory::class, $factory);
    }

    /**
     * @expectedException Zend\Expressive\Authentication\OAuth2\Exception\InvalidConfigException
     */
    public function testInvokeWithEmptyContainer()
    {
        $factory = new OAuth2MiddlewareFactory();
        $middleware = $factory($this->container->reveal());
    }

    public function testInvokeWithAuthServerWithoutResponseInterface()
    {
        $this->container->has(AuthorizationServer::class)
                        ->willReturn(true);
        $this->container->get(AuthorizationServer::class)
                        ->willReturn($this->authServer->reveal());
        $this->container->has(ResponseInterface::class)
                        ->willReturn(false);

        $factory = new OAuth2MiddlewareFactory();
        $middleware = $factory($this->container->reveal());
        $this->assertInstanceOf(OAuth2Middleware::class, $middleware);
    }

    public function testInvokeWithAuthServerWithResponseInterface()
    {
        $this->container->has(AuthorizationServer::class)
                        ->willReturn(true);
        $this->container->has(ResponseInterface::class)
                        ->willReturn(true);
        $this->container->get(AuthorizationServer::class)
                        ->willReturn($this->authServer->reveal());
        $this->container->get(ResponseInterface::class)
                        ->willReturn($this->response->reveal());

        $factory = new OAuth2MiddlewareFactory();
        $middleware = $factory($this->container->reveal());
        $this->assertInstanceOf(OAuth2Middleware::class, $middleware);
    }

}
