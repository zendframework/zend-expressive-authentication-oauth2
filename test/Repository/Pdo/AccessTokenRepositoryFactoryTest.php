<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Authentication\OAuth2\Repository\Pdo;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\PdoService;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\AccessTokenRepository;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\AccessTokenRepositoryFactory;

class AccessTokenRepositoryFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    protected function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->pdo = $this->prophesize(PdoService::class);
    }

    public function testFactory()
    {
        $this->container
            ->get(PdoService::class)
            ->willReturn($this->pdo->reveal());

        $factory = (new AccessTokenRepositoryFactory)($this->container->reveal());
        $this->assertInstanceOf(AccessTokenRepository::class, $factory);
    }
}
