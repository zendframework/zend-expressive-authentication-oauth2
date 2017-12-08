<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

namespace ZendTest\Expressive\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Zend\Expressive\Authentication\OAuth2\Entity\UserEntity;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\PdoService;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\UserRepository;

class UserRepositoryTest extends TestCase
{
    public function setUp()
    {
        $this->pdo = $this->prophesize(PdoService::class);
        $this->repo = new UserRepository($this->pdo->reveal());
    }

    public function testGetUserEntityByCredentialsReturnsNullIfStatementExecutionReturnsFalse()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':username', 'username')->shouldBeCalled();
        $statement->execute()->willReturn(false);

        $this->pdo
            ->prepare(Argument::containingString('SELECT password FROM oauth_users'))
            ->will([$statement, 'reveal']);

        $client = $this->prophesize(ClientEntityInterface::class);

        $this->assertNull(
            $this->repo ->getUserEntityByUserCredentials(
                'username',
                'password',
                'auth',
                $client->reveal()
            )
        );
    }

    public function testGetUserEntityByCredentialsReturnsNullIfPasswordVerificationFails()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':username', 'username')->shouldBeCalled();
        $statement->execute()->will(function () use ($statement) {
            $statement->fetch()->willReturn([
                'password' => 'not-the-same-password',
            ]);
            return null;
        });

        $this->pdo
            ->prepare(Argument::containingString('SELECT password FROM oauth_users'))
            ->will([$statement, 'reveal']);

        $client = $this->prophesize(ClientEntityInterface::class);

        $this->assertNull(
            $this->repo ->getUserEntityByUserCredentials(
                'username',
                'password',
                'auth',
                $client->reveal()
            )
        );
    }
}
