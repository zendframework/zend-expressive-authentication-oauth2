<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

namespace ZendTest\Expressive\Authentication\OAuth2;

use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Authentication\OAuth2\Exception;
use Zend\Expressive\Authentication\OAuth2\ResourceServerFactory;

class ResourceServerFactoryTest extends TestCase
{
    const PUBLIC_KEY = __DIR__ . '/TestAsset/public.key';

    public function setUp()
    {
        $this->container  = $this->prophesize(ContainerInterface::class);
    }

    public function testConstructor()
    {
        $factory = new ResourceServerFactory();
        $this->assertInstanceOf(ResourceServerFactory::class, $factory);
    }

    public function testInvokeWithEmptyConfig()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);
        $factory = new ResourceServerFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $resourceServer = $factory($this->container->reveal());
    }

    /**
     * @expectedException Zend\Expressive\Authentication\OAuth2\Exception\InvalidConfigException
     */
    public function testInvokeWithConfigWithoutRepository()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'authentication' => [
                'public_key' => self::PUBLIC_KEY
            ]
        ]);
        $this->container
            ->has(AccessTokenRepositoryInterface::class)
            ->willReturn(false);

        $factory = new ResourceServerFactory();
        $resourceServer = $factory($this->container->reveal());
    }

    public function testInvokeWithConfigAndRepository()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'authentication' => [
                'public_key' => self::PUBLIC_KEY
            ]
        ]);
        $this->container
            ->has(AccessTokenRepositoryInterface::class)
            ->willReturn(true);
        $this->container
            ->get(AccessTokenRepositoryInterface::class)
            ->willReturn(
                $this->prophesize(AccessTokenRepositoryInterface::class)->reveal()
            );

        $factory = new ResourceServerFactory();
        $resourceServer = $factory($this->container->reveal());
        $this->assertInstanceOf(ResourceServer::class, $resourceServer);
    }
}
