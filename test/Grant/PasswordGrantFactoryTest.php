<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Authentication\OAuth2\Grant;

use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Authentication\OAuth2\Grant\PasswordGrantFactory;

class PasswordGrantFactoryTest extends TestCase
{
    public function testInvoke()
    {
        $mockContainer = $this->prophesize(ContainerInterface::class);
        $mockUserRepo = $this->prophesize(UserRepositoryInterface::class);
        $mockRefreshTokenRepo = $this->prophesize(RefreshTokenRepositoryInterface::class);

        $config = [
            'authentication' => [
                'refresh_token_expire' => 'P1M'
            ]
        ];

        $mockContainer->has(UserRepositoryInterface::class)->willReturn(true);
        $mockContainer->has(RefreshTokenRepositoryInterface::class)->willReturn(true);
        $mockContainer->get(UserRepositoryInterface::class)->willReturn($mockUserRepo->reveal());
        $mockContainer->get(RefreshTokenRepositoryInterface::class)->willReturn($mockRefreshTokenRepo->reveal());
        $mockContainer->get('config')->willReturn($config);

        $factory = new PasswordGrantFactory();

        $result = $factory($mockContainer->reveal());

        $this->assertInstanceOf(PasswordGrant::class, $result);
    }
}
