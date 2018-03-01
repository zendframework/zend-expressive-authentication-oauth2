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
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Zend\Expressive\Authentication\OAuth2\Entity\UserEntity;

class UserRepository extends AbstractRepository implements UserRepositoryInterface
{
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
        $sth = $this->pdo->prepare(
            'SELECT password FROM oauth_users WHERE username = :username'
        );
        $sth->bindParam(':username', $username);

        if (false === $sth->execute()) {
            return;
        }

        $row = $sth->fetch();

        if (! empty($row) && password_verify($password, $row['password'])) {
            return new UserEntity($username);
        }

        return;
    }
}
