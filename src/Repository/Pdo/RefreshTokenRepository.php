<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

namespace Zend\Expressive\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Zend\Expressive\Authentication\OAuth2\Entity\RefreshTokenEntity;

class RefreshTokenRepository extends AbstractRepository implements RefreshTokenRepositoryInterface
{
    public function getNewRefreshToken()
    {
        return new RefreshTokenEntity;
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $sth = $this->pdo->prepare(
            'INSERT INTO oauth_refresh_tokens (id, access_token_id, revoked, expires_at) ' .
            'VALUES (:id, :access_token_id, :revoked, :expires_at)'
        );

        $sth->bindValue(':id', $refreshTokenEntity->getIdentifier());
        $sth->bindValue(':access_token_id', $refreshTokenEntity->getAccessToken()->getIdentifier());
        $sth->bindValue(':revoked', false);
        $sth->bindValue(':expires_at', $refreshTokenEntity->getExpiryDateTime()->getTimestamp());

        if (false === $sth->execute()) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }
    }

    public function revokeRefreshToken($tokenId)
    {
        $sth = $this->pdo->prepare(
            'UPDATE oauth_refresh_tokens SET revoked=:revoked WHERE id = :tokenId'
        );
        $sth->bindValue(':revoked', true);
        $sth->bindParam(':tokenId', $tokenId);

        $sth->execute();
    }

    public function isRefreshTokenRevoked($tokenId)
    {
        $sth = $this->pdo->prepare(
            'SELECT revoked FROM oauth_refresh_tokens WHERE id = :tokenId'
        );
        $sth->bindParam(':tokenId', $tokenId);

        if (false === $sth->execute()) {
            return false;
        }
        $row = $sth->fetch();

        return isset($row['revoked']) ? (bool) $row['revoked'] : false;
    }
}
