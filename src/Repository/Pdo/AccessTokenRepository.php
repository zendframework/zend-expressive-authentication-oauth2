<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Zend\Expressive\Authentication\OAuth2\Entity\AccessTokenEntity;
use PDO;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

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
        // 'id' => $accessTokenEntity->getIdentifier(),
        //     'user_id' => $accessTokenEntity->getUserIdentifier(),
        //     'client_id' => $accessTokenEntity->getClient()->getIdentifier(),
        //     'scopes' => $this->scopesToArray($accessTokenEntity->getScopes()),
        //     'revoked' => false,
        //     'created_at' => new DateTime,
        //     'updated_at' => new DateTime,
        //     'expires_at' => $accessTokenEntity->getExpiryDateTime(),
        $this->pdo->prepare(
            'INSERT INTO oauth_access_tokens (id, user_id, client_id, scopes, revoked, created_at, updated_at, expires_at) ' .
            'VALUES (:id, :user_id, :client_id, :scopes, :revoked, :created_at, :updated_at, :expires_at)'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function revokeAccessToken($tokenId)
    {

    }

    /**
     * {@inheritDoc}
     */
    public function isAccessTokenRevoked($tokenId)
    {

    }
}
