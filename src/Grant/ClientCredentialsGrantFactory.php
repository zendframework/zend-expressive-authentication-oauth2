<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Authentication\OAuth2\Grant;

use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use Psr\Container\ContainerInterface;

class ClientCredentialsGrantFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ClientCredentialsGrant();
    }
}
