<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Authentication\OAuth2;

use League\Event\ListenerProviderInterface;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\GrantTypeInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Authentication\OAuth2\AuthorizationServerFactory;
use League\OAuth2\Server\RequestEvent;
use League\Event\ListenerInterface;

use function array_merge;
use function array_slice;
use function in_array;

use Zend\Expressive\Authentication\OAuth2\Exception\InvalidConfigException;

class AuthorizationServerFactoryTest extends TestCase
{

    public function testInvoke()
    {
        $mockContainer = $this->prophesize(ContainerInterface::class);
        $mockClientRepo = $this->prophesize(ClientRepositoryInterface::class);
        $mockAccessTokenRepo = $this->prophesize(AccessTokenRepositoryInterface::class);
        $mockScopeRepo = $this->prophesize(ScopeRepositoryInterface::class);
        $mockClientGrant = $this->prophesize(GrantTypeInterface::class);
        $mockPasswordGrant = $this->prophesize(GrantTypeInterface::class);

        $config = [
            'authentication' => [
                'private_key' => __DIR__ . '/TestAsset/private.key',
                'encryption_key' => 'iALlwJ1sH77dmFCJFo+pMdM6Af4bF/hCca1EDDx7MwE=',
                'access_token_expire' => 'P1D',
                'grants' => [
                    ClientCredentialsGrant::class
                        => ClientCredentialsGrant::class,
                    PasswordGrant::class
                        => PasswordGrant::class,
                ],
            ]
        ];

        $mockContainer->has(ClientRepositoryInterface::class)->willReturn(true);
        $mockContainer->has(AccessTokenRepositoryInterface::class)->willReturn(true);
        $mockContainer->has(ScopeRepositoryInterface::class)->willReturn(true);

        $mockContainer->get(ClientRepositoryInterface::class)->willReturn($mockClientRepo->reveal());
        $mockContainer->get(AccessTokenRepositoryInterface::class)->willReturn($mockAccessTokenRepo->reveal());
        $mockContainer->get(ScopeRepositoryInterface::class)->willReturn($mockScopeRepo->reveal());
        $mockContainer->get(ClientCredentialsGrant::class)->willReturn($mockClientGrant->reveal());
        $mockContainer->get(PasswordGrant::class)->willReturn($mockPasswordGrant->reveal());
        $mockContainer->get('config')->willReturn($config);

        $factory = new AuthorizationServerFactory();

        $result = $factory($mockContainer->reveal());

        $this->assertInstanceOf(AuthorizationServer::class, $result);
    }

    /**
     * @return ObjectProphecy
     */
    private function getContainerMock(): ObjectProphecy
    {
        $mockContainer = $this->prophesize(ContainerInterface::class);
        $mockClientRepo = $this->prophesize(ClientRepositoryInterface::class);
        $mockAccessTokenRepo = $this->prophesize(AccessTokenRepositoryInterface::class);
        $mockScopeRepo = $this->prophesize(ScopeRepositoryInterface::class);
        $mockClientGrant = $this->prophesize(GrantTypeInterface::class);
        $mockPasswordGrant = $this->prophesize(GrantTypeInterface::class);

        $mockContainer->has(ClientRepositoryInterface::class)->willReturn(true);
        $mockContainer->has(AccessTokenRepositoryInterface::class)->willReturn(true);
        $mockContainer->has(ScopeRepositoryInterface::class)->willReturn(true);

        $mockContainer->get(ClientRepositoryInterface::class)->willReturn($mockClientRepo->reveal());
        $mockContainer->get(AccessTokenRepositoryInterface::class)->willReturn($mockAccessTokenRepo->reveal());
        $mockContainer->get(ScopeRepositoryInterface::class)->willReturn($mockScopeRepo->reveal());
        $mockContainer->get(ClientCredentialsGrant::class)->willReturn($mockClientGrant->reveal());
        $mockContainer->get(PasswordGrant::class)->willReturn($mockPasswordGrant->reveal());

        return $mockContainer;
    }

    public function testInvokeWithNullGrant()
    {
        $mockContainer = $this->getContainerMock();

        $config = [
            'authentication' => [
                'private_key' => __DIR__ . '/TestAsset/private.key',
                'encryption_key' => 'iALlwJ1sH77dmFCJFo+pMdM6Af4bF/hCca1EDDx7MwE=',
                'access_token_expire' => 'P1D',
                'grants' => [
                    ClientCredentialsGrant::class
                        => null,
                    PasswordGrant::class
                        => PasswordGrant::class,
                ],
            ]
        ];

        $mockContainer->get('config')->willReturn($config);

        $factory = new AuthorizationServerFactory();

        $result = $factory($mockContainer->reveal());

        $this->assertInstanceOf(AuthorizationServer::class, $result);
    }

    public function testInvokeWithListenerConfig()
    {
        $mockContainer = $this->getContainerMock();
        $mockListener = $this->prophesize(ListenerInterface::class);
        $mockContainer->has(ListenerInterface::class)->willReturn(true);
        $mockContainer->get(ListenerInterface::class)->willReturn($mockListener->reveal());

        $config = [
            'authentication' => [
                'private_key' => __DIR__ . '/TestAsset/private.key',
                'encryption_key' => 'iALlwJ1sH77dmFCJFo+pMdM6Af4bF/hCca1EDDx7MwE=',
                'access_token_expire' => 'P1D',
                'grants' => [
                    ClientCredentialsGrant::class
                        => ClientCredentialsGrant::class,
                ],
                'event-listeners' => [
                    [
                        RequestEvent::CLIENT_AUTHENTICATION_FAILED,
                        function (RequestEvent $event) {
                            // do something
                        }
                    ], [
                        RequestEvent::CLIENT_AUTHENTICATION_FAILED,
                        ListenerInterface::class
                    ]
                ]
            ]
        ];

        $mockContainer->get('config')->willReturn($config);

        $factory = new AuthorizationServerFactory();

        $result = $factory($mockContainer->reveal());

        $this->assertInstanceOf(AuthorizationServer::class, $result);
    }

    public function testInvokeWithListenerConfigMissingServiceThrowsException()
    {
        $mockContainer = $this->getContainerMock();
        $mockListener = $this->prophesize(ListenerInterface::class);
        $mockContainer->has(ListenerInterface::class)->willReturn(false);

        $config = [
            'authentication' => [
                'private_key' => __DIR__ . '/TestAsset/private.key',
                'encryption_key' => 'iALlwJ1sH77dmFCJFo+pMdM6Af4bF/hCca1EDDx7MwE=',
                'access_token_expire' => 'P1D',
                'grants' => [
                    ClientCredentialsGrant::class
                        => ClientCredentialsGrant::class,
                ],
                'event-listeners' => [
                    [
                        RequestEvent::CLIENT_AUTHENTICATION_FAILED,
                        ListenerInterface::class
                    ]
                ]
            ]
        ];

        $mockContainer->get('config')->willReturn($config);

        $factory = new AuthorizationServerFactory();

        $this->expectException(InvalidConfigException::class);

        $result = $factory($mockContainer->reveal());
    }

    public function testInvokeWithListenerProviderConfig()
    {
        $mockContainer = $this->getContainerMock();
        $mockProvider = $this->prophesize(ListenerProviderInterface::class);
        $mockContainer->has(ListenerProviderInterface::class)->willReturn(true);
        $mockContainer->get(ListenerProviderInterface::class)->willReturn($mockProvider->reveal());

        $config = [
            'authentication' => [
                'private_key' => __DIR__ . '/TestAsset/private.key',
                'encryption_key' => 'iALlwJ1sH77dmFCJFo+pMdM6Af4bF/hCca1EDDx7MwE=',
                'access_token_expire' => 'P1D',
                'grants' => [
                    ClientCredentialsGrant::class
                        => ClientCredentialsGrant::class,
                ],
                'event-listener-providers' => [
                    ListenerProviderInterface::class
                ]
            ]
        ];

        $mockContainer->get('config')->willReturn($config);

        $factory = new AuthorizationServerFactory();

        $result = $factory($mockContainer->reveal());

        $this->assertInstanceOf(AuthorizationServer::class, $result);
    }

    public function testInvokeWithListenerProviderConfigMissingServiceThrowsException()
    {
        $mockContainer = $this->getContainerMock();
        $mockProvider = $this->prophesize(ListenerProviderInterface::class);
        $mockContainer->has(ListenerProviderInterface::class)->willReturn(false);

        $config = [
            'authentication' => [
                'private_key' => __DIR__ . '/TestAsset/private.key',
                'encryption_key' => 'iALlwJ1sH77dmFCJFo+pMdM6Af4bF/hCca1EDDx7MwE=',
                'access_token_expire' => 'P1D',
                'grants' => [
                    ClientCredentialsGrant::class
                        => ClientCredentialsGrant::class,
                ],
                'event-listener-providers' => [
                    ListenerProviderInterface::class
                ]
            ]
        ];

        $mockContainer->get('config')->willReturn($config);

        $factory = new AuthorizationServerFactory();

        $this->expectException(InvalidConfigException::class);

        $result = $factory($mockContainer->reveal());
    }
}
