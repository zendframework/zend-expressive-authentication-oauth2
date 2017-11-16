<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

namespace ZendTest\Expressive\Authentication\OAuth2;

use League\OAuth2\Server\ResourceServer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Authentication\AuthenticationInterface;
use Zend\Expressive\Authentication\OAuth2\OAuth2Adapter;

class OAuth2AdapterTest extends TestCase
{
    public function setUp()
    {
        $this->resourceServer = $this->prophesize(ResourceServer::class);
        $this->response       = $this->prophesize(ResponseInterface::class);
    }

    public function testConstructor()
    {
        $adapter = new OAuth2Adapter(
            $this->resourceServer->reveal(),
            $this->response->reveal()
        );
        $this->assertInstanceOf(OAuth2Adapter::class, $adapter);
        $this->assertInstanceOf(AuthenticationInterface::class, $adapter);
    }
}
