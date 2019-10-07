<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\ClientRepository;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\PdoService;

class ClientRepositoryTest extends TestCase
{
    protected function setUp() : void
    {
        $this->pdo = $this->prophesize(PdoService::class);
        $this->repo = new ClientRepository($this->pdo->reveal());
    }

    public function testGetClientEntityReturnsNullIfStatementExecutionReturnsFalse()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':clientIdentifier', 'client_id')->shouldBeCalled();
        $statement->execute()->willReturn(false);

        $this->pdo
            ->prepare(Argument::containingString('SELECT * FROM oauth_clients'))
            ->will([$statement, 'reveal']);

        $this->assertNull(
            $this->repo ->getClientEntity('client_id')
        );
    }

    public function testGetClientEntityReturnsNullIfNoRowReturned()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':clientIdentifier', 'client_id')->shouldBeCalled();
        $statement->execute()->will(function () use ($statement) {
            $statement->fetch()->willReturn([]);
            return null;
        });

        $this->pdo
            ->prepare(Argument::containingString('SELECT * FROM oauth_clients'))
            ->will([$statement, 'reveal']);

        $client = $this->prophesize(ClientEntityInterface::class);

        $this->assertNull(
            $this->repo ->getClientEntity('client_id')
        );
    }

    public function invalidGrants()
    {
        return [
            'personal_access_password_mismatch' => ['authorization_code', [
                'personal_access_client' => 'personal',
                'password_client'        => 'password',
            ]],
            'personal_access_revoked' => ['personal_access', [
                'personal_access_client' => false,
            ]],
            'password_revoked' => ['password', [
                'password_client' => false,
            ]],
        ];
    }

    /**
     * @dataProvider invalidGrants
     */
    public function testValidateClientReturnsFalseIfRowIndicatesNotGranted(string $grantType, array $rowReturned)
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':clientIdentifier', 'client_id')->shouldBeCalled();
        $statement->execute()->will(function () use ($statement, $rowReturned) {
            $statement->fetch()->willReturn($rowReturned);
            return null;
        });

        $this->pdo
            ->prepare(Argument::containingString('SELECT * FROM oauth_clients'))
            ->will([$statement, 'reveal']);

        $client = $this->prophesize(ClientEntityInterface::class);

        $this->assertFalse(
            $this->repo ->validateClient(
                'client_id',
                '',
                $grantType
            )
        );
    }

    public function testValidateClientReturnsFalseForNonMatchingClientSecret()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':clientIdentifier', 'client_id')->shouldBeCalled();
        $statement->execute()->will(function () use ($statement) {
            $statement->fetch()->willReturn([
                'password_client' => true,
                'secret' => 'bar',
            ]);
            return null;
        });

        $this->pdo
            ->prepare(Argument::containingString('SELECT * FROM oauth_clients'))
            ->will([$statement, 'reveal']);

        $client = $this->prophesize(ClientEntityInterface::class);

        $this->assertFalse(
            $this->repo ->validateClient(
                'client_id',
                'foo',
                'password'
            )
        );
    }

    public function testValidateClientReturnsFalseForEmptyClientSecret()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':clientIdentifier', 'client_id')->shouldBeCalled();
        $statement->execute()->will(function () use ($statement) {
            $statement->fetch()->willReturn([
                'password_client' => true,
                'secret' => null,
            ]);
            return null;
        });

        $this->pdo
            ->prepare(Argument::containingString('SELECT * FROM oauth_clients'))
            ->will([$statement, 'reveal']);

        $client = $this->prophesize(ClientEntityInterface::class);

        $this->assertFalse(
            $this->repo ->validateClient(
                'client_id',
                'foo',
                'password'
            )
        );
    }
}
