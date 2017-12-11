<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Authentication\OAuth2\Repository\Pdo;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Authentication\OAuth2\Exception;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\PdoService;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\PdoServiceFactory;

class PdoServiceFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new PdoServiceFactory();
    }

    public function invalidConfiguration()
    {
        // @codingStandardsIgnoreStart
        return [
            'no-config-service'                   => [false, [], 'PDO configuration is missing'],
            'config-empty'                        => [true, [], 'PDO configuration is missing'],
            'config-authentication-empty'         => [true, ['authentication' => []], 'PDO configuration is missing'],
            'config-authentication-pdo-empty'     => [true, ['authentication' => ['pdo' => null]], 'PDO configuration is missing'],
            'config-authentication-pdo-dsn-empty' => [true, ['authentication' => ['pdo' => ['dsn' => null]]], 'DSN configuration is missing'],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * @dataProvider invalidConfiguration
     */
    public function testRaisesExceptionIfPdoConfigurationIsMissing(
        bool $hasConfig,
        array $config,
        string $expectedMessage
    ) {
        $this->container->has('config')->willReturn($hasConfig);
        if ($hasConfig) {
            $this->container->get('config')->willReturn($config)->shouldBeCalled();
        } else {
            $this->container->get('config')->shouldNotBeCalled();
        }

        $this->expectException(Exception\InvalidConfigException::class);
        $this->expectExceptionMessage($expectedMessage);

        ($this->factory)($this->container->reveal());
    }

    public function testValidConfigurationResultsInReturnedPdoServiceInstance()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'authentication' => [
                'pdo' => [
                    'dsn' => 'sqlite::memory:',
                ],
            ],
        ]);

        $pdo = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(PdoService::class, $pdo);
    }
}
