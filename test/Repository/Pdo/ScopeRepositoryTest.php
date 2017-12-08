<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Authentication\OAuth2\Repository\Pdo;

use DateTime;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\ScopeRepository;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\PdoService;

class ScopeRepositoryTest extends TestCase
{
    public function setUp()
    {
        $this->pdo = $this->prophesize(PdoService::class);
        $this->repo = new ScopeRepository($this->pdo->reveal());
    }

    public function testGetScopeEntityByIdentifierReturnsNullWhenStatementExecutionFails()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':identifier', 'id')->shouldBeCalled();
        $statement->execute()->willReturn(false)->shouldBeCalled();
        $statement->fetch()->shouldNotBeCalled();

        $this->pdo
            ->prepare(Argument::containingString('SELECT id FROM oauth_scopes'))
            ->will([$statement, 'reveal']);

        $this->assertNull($this->repo->getScopeEntityByIdentifier('id'));
    }

    public function testGetScopeEntityByIdentifierReturnsNullWhenReturnedRowDoesNotHaveIdentifier()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':identifier', 'id')->shouldBeCalled();
        $statement->execute()->shouldBeCalled();
        $statement->fetch()->willReturn([])->shouldBeCalled();

        $this->pdo
            ->prepare(Argument::containingString('SELECT id FROM oauth_scopes'))
            ->will([$statement, 'reveal']);

        $this->assertNull($this->repo->getScopeEntityByIdentifier('id'));
    }
}
