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
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\AccessTokenRepository;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\PdoService;

class AccessTokenRepositoryTest extends TestCase
{
    public function setUp()
    {
        $this->pdo = $this->prophesize(PdoService::class);
        $this->repo = new AccessTokenRepository($this->pdo->reveal());
    }

    public function testPersistNewAccessTokenRaisesExceptionWhenStatementExecutionFails()
    {
        $client = $this->prophesize(ClientEntityInterface::class);
        $client->getIdentifier()->willReturn('client_id');

        $scope = $this->prophesize(ScopeEntityInterface::class);
        $scope->getIdentifier()->willReturn('authentication');

        $time = time();
        $date = $this->prophesize(DateTime::class);
        $date->getTimestamp()->willReturn($time);

        $accessToken = $this->prophesize(AccessTokenEntityInterface::class);
        $accessToken->getIdentifier()->willReturn('id');
        $accessToken->getUserIdentifier()->willReturn('user_id');
        $accessToken->getClient()->will([$client, 'reveal']);
        $accessToken->getScopes()->willReturn([$scope->reveal()]);
        $accessToken->getExpiryDateTime()->will([$date, 'reveal']);

        $statement = $this->prophesize(PDOStatement::class);
        $statement
            ->execute([
                ':id'         => 'id',
                ':user_id'    => 'user_id',
                ':client_id'  => 'client_id',
                ':scopes'     => 'authentication',
                ':revoked'    => 0,
                ':expires_at' => date('Y-m-d H:i:s',$time),
            ])
            ->willReturn(false);

        $this->pdo
            ->prepare(Argument::containingString('INSERT INTO oauth_access_tokens'))
            ->will([$statement, 'reveal']);

        $this->expectException(UniqueTokenIdentifierConstraintViolationException::class);
        $this->repo->persistNewAccessToken($accessToken->reveal());
    }

    public function testIsAccessTokenRevokedReturnsFalseWhenStatementFailsExecution()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':tokenId', 'token_id')->shouldBeCalled();
        $statement->execute()->willReturn(false)->shouldBeCalled();
        $statement->fetch()->shouldNotBeCalled();

        $this->pdo
            ->prepare(Argument::containingString('SELECT revoked FROM oauth_access_tokens'))
            ->will([$statement, 'reveal']);

        $this->assertFalse($this->repo->isAccessTokenRevoked('token_id'));
    }

    public function testIsAccessTokenRevokedReturnsFalseWhenRowDoesNotContainRevokedFlag()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':tokenId', 'token_id')->shouldBeCalled();
        $statement->execute()->willReturn(null)->shouldBeCalled();
        $statement->fetch()->willReturn([])->shouldBeCalled();

        $this->pdo
            ->prepare(Argument::containingString('SELECT revoked FROM oauth_access_tokens'))
            ->will([$statement, 'reveal']);

        $this->assertFalse($this->repo->isAccessTokenRevoked('token_id'));
    }

    public function testIsAccessTokenRevokedReturnsFalseWhenRowRevokedFlagIsFalse()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':tokenId', 'token_id')->shouldBeCalled();
        $statement->execute()->willReturn(null)->shouldBeCalled();
        $statement->fetch()->willReturn(['revoked' => 0])->shouldBeCalled();

        $this->pdo
            ->prepare(Argument::containingString('SELECT revoked FROM oauth_access_tokens'))
            ->will([$statement, 'reveal']);

        $this->assertFalse($this->repo->isAccessTokenRevoked('token_id'));
    }

    public function testIsAccessTokenRevokedReturnsTrueWhenRowRevokedFlagIsTrue()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':tokenId', 'token_id')->shouldBeCalled();
        $statement->execute()->willReturn(null)->shouldBeCalled();
        $statement->fetch()->willReturn(['revoked' => 1])->shouldBeCalled();

        $this->pdo
            ->prepare(Argument::containingString('SELECT revoked FROM oauth_access_tokens'))
            ->will([$statement, 'reveal']);

        $this->assertTrue($this->repo->isAccessTokenRevoked('token_id'));
    }
}
