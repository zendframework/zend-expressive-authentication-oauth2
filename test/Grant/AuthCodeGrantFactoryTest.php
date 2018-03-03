<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Authentication\OAuth2\Grant;

use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Authentication\OAuth2\Grant\AuthCodeGrantFactory;

class AuthCodeGrantFactoryTest extends TestCase
{
    public function testInvoke()
    {
        $mockContainer = $this->prophesize(ContainerInterface::class);
        $mockAuthRepo = $this->prophesize(AuthCodeRepositoryInterface::class);
        $mockRefreshTokenRepo = $this->prophesize(RefreshTokenRepositoryInterface::class);

        $config = [
            'authentication' => [
                'auth_code_expire' => 'PT10M',
                'refresh_token_expire' => 'P1M'
            ]
        ];

        $mockContainer->has(AuthCodeRepositoryInterface::class)->willReturn(true);
        $mockContainer->has(RefreshTokenRepositoryInterface::class)->willReturn(true);
        $mockContainer->get('config')->willReturn($config);
        $mockContainer->get(AuthCodeRepositoryInterface::class)->willReturn($mockAuthRepo->reveal());
        $mockContainer->get(RefreshTokenRepositoryInterface::class)->willReturn($mockRefreshTokenRepo->reveal());

        $factory = new AuthCodeGrantFactory();

        $result = $factory($mockContainer->reveal());

        $this->assertInstanceOf(AuthCodeGrant::class, $result);
    }
}
