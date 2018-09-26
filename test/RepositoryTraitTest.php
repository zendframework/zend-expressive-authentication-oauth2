<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Authentication\OAuth2;

use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Authentication\OAuth2\RepositoryTrait;

class RepositoryTraitTest extends TestCase
{
    public function setUp()
    {
        $this->trait = $trait = new class {
            use RepositoryTrait;

            public function proxy(string $name, ContainerInterface $container)
            {
                return $this->$name($container);
            }
        };
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    /**
     * @expectedException Zend\Expressive\Authentication\OAuth2\Exception\InvalidConfigException
     */
    public function testGetUserRepositoryWithoutService()
    {
        $this->container
            ->has(UserRepositoryInterface::class)
            ->willReturn(false);
        $this->trait->proxy('getUserRepository', $this->container->reveal());
    }

    public function testGetUserRepository()
    {
        $this->container
            ->has(UserRepositoryInterface::class)
            ->willReturn(true);
        $this->container
            ->get(UserRepositoryInterface::class)
            ->willReturn($this->prophesize(UserRepositoryInterface::class)->reveal());

        $result = $this->trait->proxy('getUserRepository', $this->container->reveal());
        $this->assertInstanceOf(UserRepositoryInterface::class, $result);
    }

    /**
     * @expectedException Zend\Expressive\Authentication\OAuth2\Exception\InvalidConfigException
     */
    public function testGetScopeRepositoryWithoutService()
    {
        $this->container
            ->has(ScopeRepositoryInterface::class)
            ->willReturn(false);
        $this->trait->proxy('getScopeRepository', $this->container->reveal());
    }

    public function testGetScopeRepository()
    {
        $this->container
            ->has(ScopeRepositoryInterface::class)
            ->willReturn(true);
        $this->container
            ->get(ScopeRepositoryInterface::class)
            ->willReturn($this->prophesize(ScopeRepositoryInterface::class)->reveal());

        $result = $this->trait->proxy('getScopeRepository', $this->container->reveal());
        $this->assertInstanceOf(ScopeRepositoryInterface::class, $result);
    }

    /**
     * @expectedException Zend\Expressive\Authentication\OAuth2\Exception\InvalidConfigException
     */
    public function testGetAccessTokenRepositoryWithoutService()
    {
        $this->container
            ->has(AccessTokenRepositoryInterface::class)
            ->willReturn(false);
        $this->trait->proxy('getAccessTokenRepository', $this->container->reveal());
    }

    public function testGetAccessTokenRepository()
    {
        $this->container
            ->has(AccessTokenRepositoryInterface::class)
            ->willReturn(true);
        $this->container
            ->get(AccessTokenRepositoryInterface::class)
            ->willReturn($this->prophesize(AccessTokenRepositoryInterface::class)->reveal());

        $result = $this->trait->proxy('getAccessTokenRepository', $this->container->reveal());
        $this->assertInstanceOf(AccessTokenRepositoryInterface::class, $result);
    }

    /**
     * @expectedException Zend\Expressive\Authentication\OAuth2\Exception\InvalidConfigException
     */
    public function testGetClientRepositoryWithoutService()
    {
        $this->container
            ->has(ClientRepositoryInterface::class)
            ->willReturn(false);
        $this->trait->proxy('getClientRepository', $this->container->reveal());
    }

    public function testGetClientRepository()
    {
        $this->container
            ->has(ClientRepositoryInterface::class)
            ->willReturn(true);
        $this->container
            ->get(ClientRepositoryInterface::class)
            ->willReturn($this->prophesize(ClientRepositoryInterface::class)->reveal());

        $result = $this->trait->proxy('getClientRepository', $this->container->reveal());
        $this->assertInstanceOf(ClientRepositoryInterface::class, $result);
    }

    /**
     * @expectedException Zend\Expressive\Authentication\OAuth2\Exception\InvalidConfigException
     */
    public function testGetRefreshTokenRepositoryWithoutService()
    {
        $this->container
            ->has(RefreshTokenRepositoryInterface::class)
            ->willReturn(false);
        $this->trait->proxy('getRefreshTokenRepository', $this->container->reveal());
    }

    public function testGetRefreshTokenRepository()
    {
        $this->container
            ->has(RefreshTokenRepositoryInterface::class)
            ->willReturn(true);
        $this->container
            ->get(RefreshTokenRepositoryInterface::class)
            ->willReturn($this->prophesize(RefreshTokenRepositoryInterface::class)->reveal());

        $result = $this->trait->proxy('getRefreshTokenRepository', $this->container->reveal());
        $this->assertInstanceOf(RefreshTokenRepositoryInterface::class, $result);
    }

    /**
     * @expectedException Zend\Expressive\Authentication\OAuth2\Exception\InvalidConfigException
     */
    public function testGetAuthCodeRepositoryWithoutService()
    {
        $this->container
            ->has(AuthCodeRepositoryInterface::class)
            ->willReturn(false);
        $this->trait->proxy('getAuthCodeRepository', $this->container->reveal());
    }

    public function testGetAuthCodeRepository()
    {
        $this->container
            ->has(AuthCodeRepositoryInterface::class)
            ->willReturn(true);
        $this->container
            ->get(AuthCodeRepositoryInterface::class)
            ->willReturn($this->prophesize(AuthCodeRepositoryInterface::class)->reveal());

        $result = $this->trait->proxy('getAuthCodeRepository', $this->container->reveal());
        $this->assertInstanceOf(AuthCodeRepositoryInterface::class, $result);
    }
}
