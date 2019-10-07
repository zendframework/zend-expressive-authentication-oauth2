<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Zend\Expressive\Authentication\OAuth2\Entity\ClientEntity;

use function password_verify;

class ClientRepository extends AbstractRepository implements ClientRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getClientEntity(
        $clientIdentifier,
        $grantType = null,
        $clientSecret = null,
        $mustValidateSecret = true
    ) {
        $sth = $this->pdo->prepare(
            'SELECT * FROM oauth_clients WHERE name = :clientIdentifier'
        );
        $sth->bindParam(':clientIdentifier', $clientIdentifier);

        if (false === $sth->execute()) {
            return null;
        }
        $row = $sth->fetch();
        if (empty($row) || ! $this->isGranted($row, $grantType)) {
            return null;
        }

        if ($mustValidateSecret
            && (empty($row['secret']) || ! password_verify((string) $clientSecret, $row['secret']))
        ) {
            return null;
        }

        return new ClientEntity($clientIdentifier, $row['name'], $row['redirect']);
    }

    /**
     * Check the grantType for the client value, stored in $row
     *
     * @param array $row
     * @param string $grantType
     * @return bool
     */
    protected function isGranted(array $row, string $grantType = null) : bool
    {
        switch ($grantType) {
            case 'authorization_code':
                return ! ($row['personal_access_client'] || $row['password_client']);
            case 'personal_access':
                return (bool) $row['personal_access_client'];
            case 'password':
                return (bool) $row['password_client'];
            default:
                return true;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType) : bool
    {
        $client = $this->getClientEntity(
            $clientIdentifier,
            $grantType,
            $clientSecret
        );

        return $client instanceof ClientEntityInterface;
    }
}
