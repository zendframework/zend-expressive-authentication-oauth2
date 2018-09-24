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
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Zend\Expressive\Authentication\OAuth2\Entity\AuthCodeEntity;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\AuthCodeRepository;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\PdoService;

use function time;

class AuthCodeRepositoryTest extends TestCase
{
    public function setUp()
    {
        $this->pdo = $this->prophesize(PdoService::class);
        $this->repo = new AuthCodeRepository($this->pdo->reveal());
    }

    public function testPersistNewAuthCodeRaisesExceptionWhenStatementExecutionFails()
    {
        $client = $this->prophesize(ClientEntityInterface::class);
        $client->getIdentifier()->willReturn('client_id');

        $scope = $this->prophesize(ScopeEntityInterface::class);
        $scope->getIdentifier()->willReturn('authentication');

        $time = time();
        $date = $this->prophesize(DateTime::class);
        $date->getTimestamp()->willReturn($time);

        $authCode = $this->prophesize(AuthCodeEntity::class);
        $authCode->getIdentifier()->willReturn('id');
        $authCode->getUserIdentifier()->willReturn('user_id');
        $authCode->getClient()->will([$client, 'reveal']);
        $authCode->getScopes()->willReturn([$scope->reveal()]);
        $authCode->getExpiryDateTime()->will([$date, 'reveal']);

        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindValue(':id', 'id')->shouldBeCalled();
        $statement->bindValue(':user_id', 'user_id')->shouldBeCalled();
        $statement->bindValue(':client_id', 'client_id')->shouldBeCalled();
        $statement->bindValue(':scopes', 'authentication')->shouldBeCalled();
        $statement->bindValue(':revoked', 0)->shouldBeCalled();
        $statement->bindValue(':expires_at', date('Y-m-d H:i:s', $time))
            ->shouldBeCalled();
        $statement->execute()->willReturn(false);

        $this->pdo
            ->prepare(Argument::containingString('INSERT INTO oauth_auth_codes'))
            ->will([$statement, 'reveal']);

        $this->expectException(UniqueTokenIdentifierConstraintViolationException::class);
        $this->repo->persistNewAuthCode($authCode->reveal());
    }

    public function testIsAuthCodeRevokedReturnsFalseForStatementExecutionFailure()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':codeId', 'code_identifier')->shouldBeCalled();
        $statement->execute()->willReturn(false);
        $statement->fetch()->shouldNotBeCalled();

        $this->pdo
            ->prepare(Argument::containingString('SELECT revoked FROM oauth_auth_codes'))
            ->will([$statement, 'reveal']);

        $this->assertFalse($this->repo->isAuthCodeRevoked('code_identifier'));
    }

    public function testIsAuthCodeRevokedReturnsTrue()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':codeId', 'code_identifier')->shouldBeCalled();
        $statement->execute()->willReturn(true);
        $statement->fetch()->willReturn(['revoked' => true]);

        $this->pdo
            ->prepare(Argument::containingString('SELECT revoked FROM oauth_auth_codes'))
            ->will([$statement, 'reveal']);

        $this->assertTrue($this->repo->isAuthCodeRevoked('code_identifier'));
    }

    public function testNewAuthCode()
    {
        $result = $this->repo->getNewAuthCode();
        $this->assertInstanceOf(AuthCodeEntity::class, $result);
    }

    public function testRevokeAuthCode()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':codeId', 'code_identifier')->shouldBeCalled();
        $statement->bindValue(':revoked', 1)->shouldBeCalled();
        $statement->execute()->willReturn(true);

        $this->pdo
            ->prepare(Argument::containingString('UPDATE oauth_auth_codes SET revoked=:revoked WHERE id = :codeId'))
            ->will([$statement, 'reveal']);

        $this->repo->revokeAuthCode('code_identifier');
    }
}
