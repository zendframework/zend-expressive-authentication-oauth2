<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Authentication\OAuth2;

use League\OAuth2\Server\ResourceServer;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;
use TypeError;
use Zend\Expressive\Authentication\AuthenticationInterface;
use Zend\Expressive\Authentication\OAuth2\OAuth2Adapter;
use Zend\Expressive\Authentication\OAuth2\OAuth2AdapterFactory;
use Zend\Expressive\Authentication\UserInterface;

class OAuth2AdapterFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    /** @var ResourceServer|ObjectProphecy */
    private $resourceServer;

    /** @var ResponseInterface|ObjectProphecy */
    private $response;

    /** @var callable */
    private $responseFactory;

    public function setUp()
    {
        $this->container       = $this->prophesize(ContainerInterface::class);
        $this->resourceServer  = $this->prophesize(ResourceServer::class);
        $this->response        = $this->prophesize(ResponseInterface::class);
        $this->responseFactory = function () {
            return $this->response->reveal();
        };
        $this->user = $this->prophesize(UserInterface::class);
        $this->userFactory = function (
            string $identity,
            array $roles = [],
            array $details = []
        ) {
            return $this->user->reveal($identity, $roles, $details);
        };
    }

    public function testConstructor()
    {
        $factory = new OAuth2AdapterFactory();
        $this->assertInstanceOf(OAuth2AdapterFactory::class, $factory);
    }

    /**
     * @expectedException \Zend\Expressive\Authentication\OAuth2\Exception\InvalidConfigException
     */
    public function testInvokeWithEmptyContainer()
    {
        $factory = new OAuth2AdapterFactory();
        $oauth2Adapter = $factory($this->container->reveal());
    }

    public function testFactoryRaisesTypeErrorForNonCallableResponseFactory()
    {
        $this->container
            ->has(ResourceServer::class)
            ->willReturn(true);
        $this->container
            ->get(ResourceServer::class)
            ->willReturn($this->resourceServer->reveal());

        $this->container
            ->get(ResponseInterface::class)
            ->willReturn(new stdClass());

        $this->container
            ->get(UserInterface::class)
            ->willReturn($this->userFactory);

        $factory = new OAuth2AdapterFactory();

        $this->expectException(TypeError::class);
        $adapter = $factory($this->container->reveal());
    }

    public function testFactoryRaisesTypeErrorWhenResponseServiceProvidesResponseInstance()
    {
        $this->container
            ->has(ResourceServer::class)
            ->willReturn(true);
        $this->container
            ->get(ResourceServer::class)
            ->willReturn($this->resourceServer->reveal());

        $this->container
            ->get(ResponseInterface::class)
            ->will([$this->response, 'reveal']);

        $this->container
            ->get(UserInterface::class)
            ->willReturn($this->userFactory);

        $factory = new OAuth2AdapterFactory();

        $this->expectException(TypeError::class);
        $adapter = $factory($this->container->reveal());
    }

    public function testFactoryReturnsInstanceWhenAppropriateDependenciesArePresentInContainer()
    {
        $this->container
            ->has(ResourceServer::class)
            ->willReturn(true);
        $this->container
            ->get(ResourceServer::class)
            ->willReturn($this->resourceServer->reveal());

        $this->container
            ->has(ResponseInterface::class)
            ->willReturn(true);
        $this->container
            ->get(ResponseInterface::class)
            ->willReturn($this->responseFactory);

        $this->container
            ->get(UserInterface::class)
            ->willReturn($this->userFactory);

        $factory = new OAuth2AdapterFactory();
        $adapter = $factory($this->container->reveal());

        $this->assertInstanceOf(OAuth2Adapter::class, $adapter);
        $this->assertInstanceOf(AuthenticationInterface::class, $adapter);
    }
}
