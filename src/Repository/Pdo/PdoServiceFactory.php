<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Authentication\OAuth2\Repository\Pdo;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Authentication\OAuth2\Exception;

class PdoServiceFactory
{
    public function __invoke(ContainerInterface $container) : PdoService
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = $config['authentication']['pdo'] ?? null;
        if (null === $config) {
            throw new Exception\InvalidConfigException(
                'The PDO configuration is missing'
            );
        }
        if (! isset($config['dsn'])) {
            throw new Exception\InvalidConfigException(
                'The DSN configuration is missing for PDO'
            );
        }
        $username = $config['username'] ?? null;
        $password = $config['password'] ?? null;
        return new PdoService($config['dsn'], $username, $password);
    }
}
