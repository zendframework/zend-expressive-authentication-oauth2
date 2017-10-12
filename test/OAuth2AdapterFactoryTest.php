<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Authentication\OAuth2;

use League\OAuth2\Server\ResourceServer;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Authentication\OAuth2\OAuth2Adapter;
use Zend\Expressive\Authentication\OAuth2\OAuth2AdapterFactory;

class OAuth2AdapterFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new OAuth2AdapterFactory();
        $this->resourceServer = $this->prophesize(ResourceServer::class);
        $this->responsePrototype = $this->prophesize(ResponseInterface::class);
    }

    public function testInvoke()
    {
        $this->container->has(ResourceServer::class)
                        ->willReturn(true);
        $this->container->get(ResourceServer::class)
                        ->willReturn($this->resourceServer->reveal());

        $this->container->has(ResponseInterface::class)
                        ->willReturn(true);
        $this->container->get(ResponseInterface::class)
                        ->willReturn($this->responsePrototype->reveal());

        $adapter = ($this->factory)($this->container->reveal());
        $this->assertInstanceOf(OAuth2Adapter::class, $adapter);
    }
}
