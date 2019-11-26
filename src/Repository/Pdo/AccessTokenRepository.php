<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Zend\Expressive\Authentication\OAuth2\Entity\AccessTokenEntity;

use function array_key_exists;
use function implode;
use function sprintf;

class AccessTokenRepository extends AbstractRepository implements AccessTokenRepositoryInterface
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
        $columns = [
            'id',
            'user_id',
            'client_id',
            'scopes',
            'revoked',
            'created_at',
            'updated_at',
            'expires_at',
        ];

        $values = [
            ':id',
            ':user_id',
            ':client_id',
            ':scopes',
            ':revoked',
            'CURRENT_TIMESTAMP',
            'CURRENT_TIMESTAMP',
            ':expires_at',
        ];

        $sth = $this->pdo->prepare(sprintf(
            'INSERT INTO oauth_access_tokens (%s) VALUES (%s)',
            implode(', ', $columns),
            implode(', ', $values)
        ));

        $params = [
            ':id'         => $accessTokenEntity->getIdentifier(),
            ':user_id'    => $accessTokenEntity->getUserIdentifier(),
            ':client_id'  => $accessTokenEntity->getClient()->getIdentifier(),
            ':scopes'     => $this->scopesToString($accessTokenEntity->getScopes()),
            ':revoked'    => 0,
            ':expires_at' => date(
                'Y-m-d H:i:s',
                $accessTokenEntity->getExpiryDateTime()->getTimestamp()
            ),
        ];

        if (false === $sth->execute($params)) {
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
        $sth->bindValue(':revoked', 1);
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
        if (!is_array($row)) {
            throw OAuthServerException::invalidRefreshToken();
        }

        return array_key_exists('revoked', $row) ? (bool) $row['revoked'] : false;
    }
}
