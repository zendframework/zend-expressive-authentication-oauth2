<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Zend\Expressive\Authentication\OAuth2\Entity\ClientEntity;

class ClientRepository extends AbstractRepository implements ClientRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true)
    {
        $sth = $this->pdo->prepare(
            'SELECT * FROM oauth_clients WHERE name = :clientIdentifier'
        );
        $sth->bindParam(':clientIdentifier', $clientIdentifier);

        if (false === $sth->execute()) {
            return;
        }
        $row = $sth->fetch();
        if (empty($row) || ! $this->isGranted($row, $grantType)) {
            return;
        }
        if ($mustValidateSecret && ! password_verify((string) $clientSecret, $row['secret'])) {
            return;
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
    protected function isGranted(array $row, string $grantType) : bool
    {
        switch ($grantType) {
            case 'authorization_code':
                return ! ($row['personal_access_client'] || $row['password_client']);
            case 'personal_access':
                return $row['personal_access_client'];
            case 'password':
                return $row['password_client'];
            default:
                return true;
        }
    }
}
