<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TypeError;
use Zend\Expressive\Authentication\OAuth2\TokenEndpointHandler;
use Zend\Expressive\Authentication\OAuth2\TokenEndpointHandlerFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Zend\Expressive\Authentication\OAuth2\TokenEndpointHandlerFactory
 */
class TokenEndpointHandlerFactoryTest extends TestCase
{
    /**
     * @var TokenEndpointHandlerFactory
     */
    private $subject;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->subject = new TokenEndpointHandlerFactory();
        parent::setUp();
    }

    public function testEmptyContainerThrowsTypeError()
    {
        $container = $this->prophesize(ContainerInterface::class);

        $this->expectException(TypeError::class);
        ($this->subject)($container);
    }

    public function testCreatesTokenEndpointHandler()
    {
        $server = $this->prophesize(AuthorizationServer::class);
        $responseFactory = function() {};
        $container = $this->prophesize(ContainerInterface::class);

        $container->get(AuthorizationServer::class)
            ->willReturn($server->reveal());
        $container->get(ResponseInterface::class)
            ->willReturn($responseFactory);

        self::assertInstanceOf(TokenEndpointHandler::class, ($this->subject)($container->reveal()));
    }

    public function testDirectResponseInstanceFromContainerThrowsTypeError()
    {
        $server = $this->prophesize(AuthorizationServer::class);
        $container = $this->prophesize(ContainerInterface::class);

        $container->get(AuthorizationServer::class)
            ->willReturn($server->reveal());
        $container->get(ResponseInterface::class)
            ->willReturn($this->prophesize(ResponseInterface::class)->reveal());

        $this->expectException(TypeError::class);
        ($this->subject)($container->reveal());
    }
}
