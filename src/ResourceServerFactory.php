<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Authentication\OAuth2;

use League\OAuth2\Server\ResourceServer;
use Psr\Container\ContainerInterface;

class ResourceServerFactory
{
    use RepositoryTrait;

    public function __invoke(ContainerInterface $container) : ResourceServer
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = $config['authentication'] ?? [];

        if (! isset($config['public_key'])) {
            throw new Exception\InvalidConfigException(
                'The public_key value is missing in config authentication'
            );
        }

        return new ResourceServer(
            $this->getAccessTokenRepository($container),
            $config['public_key']
        );
    }
}
