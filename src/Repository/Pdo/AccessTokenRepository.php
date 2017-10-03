<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Zend\Expressive\Authentication\OAuth2\Entity\AccessTokenEntity;

class AccessTokenRepository extends AbstractRepository
    implements AccessTokenRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        $accessToken = new AccessTokenEntity();
        $accessToken->setClient($clientEntity);
        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }
        $accessToken->setUserIdentifier($userIdentifier);
        return $accessToken;
    }

    /**
     * {@inheritDoc}
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        $sth = $this->pdo->prepare(
            'INSERT INTO oauth_access_tokens (id, user_id, client_id, scopes, revoked, created_at, updated_at, expires_at) ' .
            'VALUES (:id, :user_id, :client_id, :scopes, :revoked, :created_at, :updated_at, :expires_at)'
        );

        $sth->bindValue(':id', $accessTokenEntity->getIdentifier());
        $sth->bindValue(':user_id', $accessTokenEntity->getUserIdentifier());
        $sth->bindValue(':client_id', $accessTokenEntity->getClient()->getIdentifier());
        $sth->bindValue(':scopes', $this->scopesToArray($accessTokenEntity->getScopes()));
        $sth->bindValue(':revoked', false);
        $sth->bindValue(':created_at', date(DATE_RFC3339));
        $sth->bindValue(':updated_at', date(DATE_RFC3339));
        $sth->bindValue(':expires_at',  $accessTokenEntity->getExpiryDateTime());

        if (false === $sth->execute()) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function revokeAccessToken($tokenId)
    {
        $sth = $this->pdo->prepare(
            'UPDATE oauth_access_tokens SET revoked=:revoked WHERE id = :tokenId'
        );
        $sth->bindValue(':revoked', true);
        $sth->bindParam(':tokenId', $tokenId);

        $sth->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function isAccessTokenRevoked($tokenId)
    {
        $sth = $this->pdo->prepare(
            'SELECT revoked FROM oauth_access_tokens WHERE id = :tokenId'
        );
        $sth->bindParam(':tokenId', $tokenId);

        if (false === $sth->execute()) {
            return false;
        }
        $row = $sth->fetch();

        return isset($row['revoked']) ? (bool) $row['revoked'] : false;
    }
}
