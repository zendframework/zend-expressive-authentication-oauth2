<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Authentication\Adapter;

use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Authentication\AuthenticationInterface;
use Zend\Expressive\Authentication\UserInterface;

class OAuth2 implements AuthenticationInterface
{
    protected $oauth2;
    protected $config;

    public function __construct(ResourceServer $oauth2, array $config)
    {
        $this->oauth2 = $oauth2;
        $this->config = $config;
    }

    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        try {
            $result = $this->oauth2->validateAuthenticatedRequest($request);
        } catch (OAuthServerException $exception) {
            return null;
        }
        return null;
    }
}
