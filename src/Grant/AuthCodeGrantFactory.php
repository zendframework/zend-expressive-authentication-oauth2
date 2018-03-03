<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Authentication\OAuth2\Grant;

use League\OAuth2\Server\Grant\AuthCodeGrant;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Authentication\OAuth2\ConfigTrait;
use Zend\Expressive\Authentication\OAuth2\RepositoryTrait;

class AuthCodeGrantFactory
{
    use RepositoryTrait;

    use ConfigTrait;

    public function __invoke(ContainerInterface $container)
    {
        $grant = new AuthCodeGrant(
            $this->getAuthCodeRepository($container),
            $this->getRefreshTokenRepository($container),
            new \DateInterval($this->getAuthCodeExpire($container))
        );

        $grant->setRefreshTokenTTL(
            new \DateInterval($this->getRefreshTokenExpire($container))
        );

        return $grant;
    }
}
