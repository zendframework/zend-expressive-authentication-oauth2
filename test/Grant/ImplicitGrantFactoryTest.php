<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Authentication\OAuth2\Grant;

use League\OAuth2\Server\Grant\ImplicitGrant;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Authentication\OAuth2\Grant\ImplicitGrantFactory;

class ImplicitGrantFactoryTest extends TestCase
{
    public function testInvoke()
    {
        $mockContainer = $this->prophesize(ContainerInterface::class);

        $config = [
            'authentication' => [
                'auth_code_expire' => 'PT10M',
            ]
        ];

        $mockContainer->get('config')->willReturn($config);

        $factory = new ImplicitGrantFactory();

        $result = $factory($mockContainer->reveal());

        $this->assertInstanceOf(ImplicitGrant::class, $result);
    }
}
