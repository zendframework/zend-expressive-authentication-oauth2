<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */
namespace Zend\Expressive\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use Zend\Expressive\Authentication\OAuth2\Entity\ScopeEntity;

class ScopeRepository extends AbstractRepository implements ScopeRepositoryInterface
{
    public function getScopeEntityByIdentifier($identifier)
    {
        $sth = $this->pdo->prepare(
            'SELECT id FROM oauth_scopes WHERE id = :identifier'
        );
        $sth->bindParam(':identifier', $identifier);

        if (false === $sth->execute()) {
            return;
        }

        $row = $sth->fetch();
        if (! isset($row['id'])) {
            return;
        }

        $scope = new ScopeEntity();
        $scope->setIdentifier($row['id']);
        return $scope;
    }

    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ) {
        return $scopes;
    }
}
