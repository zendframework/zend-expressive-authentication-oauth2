<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Authentication\OAuth2\Grant;

use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Authentication\OAuth2\Grant\RefreshTokenGrantFactory;

class RefreshTokenGrantFactoryTest extends TestCase
{
    public function testInvoke()
    {
        $mockContainer = $this->prophesize(ContainerInterface::class);
        $mockRefreshTokenRepo = $this->prophesize(RefreshTokenRepositoryInterface::class);

        $config = [
            'authentication' => [
                'refresh_token_expire' => 'P1M'
            ]
        ];

        $mockContainer->has(RefreshTokenRepositoryInterface::class)->willReturn(true);
        $mockContainer->get(RefreshTokenRepositoryInterface::class)->willReturn($mockRefreshTokenRepo->reveal());
        $mockContainer->get('config')->willReturn($config);

        $factory = new RefreshTokenGrantFactory();

        $result = $factory($mockContainer->reveal());

        $this->assertInstanceOf(RefreshTokenGrant::class, $result);
    }
}
