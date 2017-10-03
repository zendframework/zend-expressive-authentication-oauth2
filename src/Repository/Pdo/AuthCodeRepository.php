<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use Zend\Expressive\Authentication\OAuth2\Entity\AuthCodeEntity;

class AuthCodeRepository extends AbstractRepository
    implements AuthCodeRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getNewAuthCode()
    {
        return new AuthCodeEntity;
    }

    /**
     * {@inheritDoc}
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $sth = $this->pdo->prepare(
            'INSERT INTO oauth_auth_codes (id, user_id, client_id, scopes, revoked, expires_at) ' .
            'VALUES (:id, :user_id, :client_id, :scopes, :revoked, :expires_at)'
        );

        $sth->bindValue(':id', $authCodeEntity->getIdentifier());
        $sth->bindValue(':user_id', $authCodeEntity->getUserIdentifier());
        $sth->bindValue(':client_id', $authCodeEntity->getClient()->getIdentifier());
        $sth->bindValue(':scopes', $this->formatScopesForStorage($authCodeEntity->getScopes()));
        $sth->bindValue(':revoked', false);
        $sth->bindValue(':expires_at',  $authCodeEntity->getExpiryDateTime());

        if (false === $sth->execute()) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function revokeAuthCode($codeId)
    {
        $sth = $this->pdo->prepare(
            'UPDATE oauth_auth_codes SET revoked=:revoked WHERE id = :codeId'
        );
        $sth->bindValue(':revoked', true);
        $sth->bindParam(':codeId', $codeId);

        $sth->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function isAuthCodeRevoked($codeId)
    {
        $sth = $this->pdo->prepare(
            'SELECT revoked FROM oauth_auth_codes WHERE id = :codeId'
        );
        $sth->bindParam(':codeId', $codeId);

        if (false === $sth->execute()) {
            return false;
        }
        $row = $sth->fetch();

        return isset($row['revoked']) ? (bool) $row['revoked'] : false;
    }
}
