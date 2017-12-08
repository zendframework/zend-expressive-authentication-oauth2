<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

namespace ZendTest\Expressive\Authentication\OAuth2\Repository\Pdo;

use DateTime;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\RefreshTokenRepository;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\PdoService;

class RefreshTokenRepositoryTest extends TestCase
{
    public function setUp()
    {
        $this->pdo = $this->prophesize(PdoService::class);
        $this->repo = new RefreshTokenRepository($this->pdo->reveal());
    }

    public function testPersistNewRefreshTokenRaisesExceptionWhenStatementExecutionFails()
    {
        $accessToken = $this->prophesize(AccessTokenEntityInterface::class);
        $accessToken->getIdentifier()->willReturn('access_token_id');

        $time = time();
        $date = $this->prophesize(DateTime::class);
        $date->getTimestamp()->willReturn($time);

        $refreshToken = $this->prophesize(RefreshTokenEntityInterface::class);
        $refreshToken->getIdentifier()->willReturn('id');
        $refreshToken->getAccessToken()->will([$accessToken, 'reveal']);
        $refreshToken->getExpiryDateTime()->will([$date, 'reveal']);

        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindValue(':id', 'id')->shouldBeCalled();
        $statement->bindValue(':access_token_id', 'access_token_id')->shouldBeCalled();
        $statement->bindValue(':revoked', false)->shouldBeCalled();
        $statement->bindValue(':expires_at', $time)->shouldBeCalled();
        $statement->execute()->willReturn(false)->shouldBeCalled();

        $this->pdo
            ->prepare(Argument::containingString('INSERT INTO oauth_refresh_tokens'))
            ->will([$statement, 'reveal']);

        $this->expectException(UniqueTokenIdentifierConstraintViolationException::class);
        $this->repo->persistNewRefreshToken($refreshToken->reveal());
    }

    public function testIsRefreshTokenRevokedReturnsFalseWhenStatementFailsExecution()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':tokenId', 'token_id')->shouldBeCalled();
        $statement->execute()->willReturn(false)->shouldBeCalled();
        $statement->fetch()->shouldNotBeCalled();

        $this->pdo
            ->prepare(Argument::containingString('SELECT revoked FROM oauth_refresh_tokens'))
            ->will([$statement, 'reveal']);

        $this->assertFalse($this->repo->isRefreshTokenRevoked('token_id'));
    }
}
