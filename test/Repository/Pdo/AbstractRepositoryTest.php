<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Authentication\OAuth2\Repository\Pdo;

use PHPUnit\Framework\TestCase;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\AbstractRepository;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\PdoService;

class AbstractRepositoryTest extends TestCase
{
    public function setUp()
    {
        $this->pdo = $this->prophesize(PdoService::class);
    }

    public function testConstructor()
    {
        $abstract = new AbstractRepository($this->pdo->reveal());
        $this->assertInstanceOf(AbstractRepository::class, $abstract);
    }

    public function testScopesToStringWithEmptyArray()
    {
        $proxy = new class($this->pdo->reveal()) extends AbstractRepository {
            public function scopesToString(array $scopes): string
            {
                return parent::scopesToString($scopes);
            }
        };
        $result = $proxy->scopesToString([]);
        $this->assertEquals('', $result);
    }
}
