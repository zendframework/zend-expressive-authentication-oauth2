<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

namespace ZendTest\Expressive\Authentication\OAuth2;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Zend\Expressive\Authentication\OAuth2\AuthorizationServerFactory;

class AuthorizationServerFactoryTest extends TestCase
{
    const REPOSITORY_CLASSES = [
        ClientRepositoryInterface::class,
        AccessTokenRepositoryInterface::class,
        ScopeRepositoryInterface::class,
        UserRepositoryInterface::class,
        RefreshTokenRepositoryInterface::class,
        AuthCodeRepositoryInterface::class
    ];

    const CONFIG = [
        'private_key' => __DIR__ . '/TestAsset/private.key',
        'encryption_key' => 'iALlwJ1sH77dmFCJFo+pMdM6Af4bF/hCca1EDDx7MwE=',
        'access_token_expire' => 'P1D',
        'refresh_token_expire' => 'P1M',
        'auth_code_expire' => 'PT10M',
    ];

    public function setUp()
    {
        $this->container  = $this->prophesize(ContainerInterface::class);
    }

    public function testConstructor()
    {
        $factory = new AuthorizationServerFactory();
        $this->assertInstanceOf(AuthorizationServerFactory::class, $factory);
    }

    /**
     * @expectedException Zend\Expressive\Authentication\OAuth2\Exception\InvalidConfigException
     */
    public function testInvokeWithEmptyContainer()
    {
        $factory = new AuthorizationServerFactory();
        $authServer = $factory($this->container->reveal());
    }

    public function getRepositorySlices()
    {
        return [
            [ array_slice(self::REPOSITORY_CLASSES, 0, 1) ],
            [ array_slice(self::REPOSITORY_CLASSES, 0, 2) ],
            [ array_slice(self::REPOSITORY_CLASSES, 0, 3) ],
            [ array_slice(self::REPOSITORY_CLASSES, 0, 4) ],
            [ array_slice(self::REPOSITORY_CLASSES, 0, 5) ],
            [ self::REPOSITORY_CLASSES ]
        ];
    }

    /**
     * @dataProvider getRepositorySlices
     * @expectedException Zend\Expressive\Authentication\OAuth2\Exception\InvalidConfigException
     */
    public function testInvokeWithMissingRepository($repos)
    {
        foreach (self::REPOSITORY_CLASSES as $repo) {
            if (in_array($repo, $repos)) {
                $this->container->has($repo)->willReturn(true);
                $this->container->get($repo)->willReturn(
                    $this->prophesize($repo)->reveal()
                );
            } else {
                $this->container->has($repo)->willReturn(false);
            }
        }
        $this->container->get('config')->willReturn([]);

        $factory = new AuthorizationServerFactory();
        $authServer = $factory($this->container->reveal());
    }

    public function getConfigKeys()
    {
        $result = [];
        foreach (self::CONFIG as $key => $value) {
            $result[] = [ $key ];
        }
        return $result;
    }

    /**
     * @dataProvider getConfigKeys
     * @expectedException Zend\Expressive\Authentication\OAuth2\Exception\InvalidConfigException
     */
    public function testInvokeWithMissingConfig($key)
    {
        foreach (self::REPOSITORY_CLASSES as $repo) {
            $this->container->has($repo)->willReturn(true);
            $this->container->get($repo)->willReturn(
                $this->prophesize($repo)->reveal()
            );
        }
        $config = self::CONFIG;
        unset($config[$key]);
        $this->container->get('config')->willReturn([
            'authentication' => $config
        ]);

        $factory = new AuthorizationServerFactory();
        $authServer = $factory($this->container->reveal());
    }

    public function testInvokeWithValidData()
    {
        foreach (self::REPOSITORY_CLASSES as $repo) {
            $this->container->has($repo)->willReturn(true);
            $this->container->get($repo)->willReturn(
                $this->prophesize($repo)->reveal()
            );
        }
        $this->container->get('config')->willReturn([
            'authentication' => self::CONFIG
        ]);

        $factory = new AuthorizationServerFactory();
        $authServer = $factory($this->container->reveal());

        $this->assertInstanceOf(AuthorizationServer::class, $authServer);
    }
}
