<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\GrantTypeInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Authentication\OAuth2\AuthorizationServerFactory;

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
}
