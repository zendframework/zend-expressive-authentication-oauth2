<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Authentication\OAuth2;

use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Authentication\AuthenticationInterface;
use Zend\Expressive\Authentication\DefaultUser;
use Zend\Expressive\Authentication\OAuth2\OAuth2Adapter;
use Zend\Expressive\Authentication\UserInterface;

class OAuth2AdapterTest extends TestCase
{
    /** @var ResourceServer|ObjectProphecy */
    private $resourceServer;

    /** @var ResponseInterface|ObjectProphecy */
    private $response;

    /** @var callable */
    private $responseFactory;

    public function setUp()
    {
        $this->resourceServer  = $this->prophesize(ResourceServer::class);
        $this->response        = $this->prophesize(ResponseInterface::class);
        $this->responseFactory = function () {
            return $this->response->reveal();
        };
        $this->userFactory = function (
            string $identity,
            array $roles = [],
            array $details = []
        ) {
            return new DefaultUser($identity, $roles, $details);
        };
    }

    public function testConstructor()
    {
        $adapter = new OAuth2Adapter(
            $this->resourceServer->reveal(),
            $this->responseFactory,
            $this->userFactory
        );
        $this->assertInstanceOf(OAuth2Adapter::class, $adapter);
        $this->assertInstanceOf(AuthenticationInterface::class, $adapter);
    }

    public function testOAuthServerExceptionRaisedDuringAuthenticateResultsInInvalidAuthentication()
    {
        $request = $this->prophesize(ServerRequestInterface::class);

        $exception = $this->prophesize(OAuthServerException::class);

        $this->resourceServer
            ->validateAuthenticatedRequest(Argument::that([$request, 'reveal']))
            ->willThrow($exception->reveal());

        $adapter = new OAuth2Adapter(
            $this->resourceServer->reveal(),
            $this->responseFactory,
            $this->userFactory
        );

        $this->assertNull($adapter->authenticate($request->reveal()));
    }

    public function testAuthenticateReturnsNullIfResourceServerDoesNotProduceAUserIdOrClientId()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute('oauth_user_id', null)->willReturn(null);
        $request->getAttribute('oauth_client_id', null)->willReturn(null);

        $this->resourceServer
            ->validateAuthenticatedRequest(Argument::that([$request, 'reveal']))
            ->will([$request, 'reveal']);

        $adapter = new OAuth2Adapter(
            $this->resourceServer->reveal(),
            $this->responseFactory,
            $this->userFactory
        );

        $this->assertNull($adapter->authenticate($request->reveal()));
    }

    public function testAuthenticateReturnsAUserIfTheResourceServerProducesAUserId()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute('oauth_user_id', null)->willReturn('some-identifier');
        $request->getAttribute('oauth_client_id', null)->willReturn(null);
        $request->getAttribute('oauth_access_token_id', null)->willReturn(null);
        $request->getAttribute('oauth_scopes', null)->willReturn(null);

        $this->resourceServer
            ->validateAuthenticatedRequest(Argument::that([$request, 'reveal']))
            ->will([$request, 'reveal']);

        $adapter = new OAuth2Adapter(
            $this->resourceServer->reveal(),
            $this->responseFactory,
            $this->userFactory
        );

        $user = $adapter->authenticate($request->reveal());

        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertSame('some-identifier', $user->getIdentity());
        $this->assertSame([], $user->getRoles());
    }

    public function testAuthenticateReturnsAClientIfTheResourceServerProducesAClientId()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute('oauth_user_id', null)->willReturn(null);
        $request->getAttribute('oauth_client_id', null)->willReturn('some-identifier');
        $request->getAttribute('oauth_access_token_id', null)->willReturn(null);
        $request->getAttribute('oauth_scopes', null)->willReturn(null);

        $this->resourceServer
            ->validateAuthenticatedRequest(Argument::that([$request, 'reveal']))
            ->will([$request, 'reveal']);

        $adapter = new OAuth2Adapter(
            $this->resourceServer->reveal(),
            $this->responseFactory,
            $this->userFactory
        );

        $user = $adapter->authenticate($request->reveal());

        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertSame('some-identifier', $user->getIdentity());
        $this->assertSame([], $user->getRoles());
    }

    public function testUnauthorizedResponseProducesAResponseWithAWwwAuthenticateHeader()
    {
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();

        $this->response
            ->withHeader('WWW-Authenticate', 'Bearer token-example')
            ->will([$this->response, 'reveal']);
        $this->response
            ->withStatus(401)
            ->will([$this->response, 'reveal']);

        $adapter = new OAuth2Adapter(
            $this->resourceServer->reveal(),
            $this->responseFactory,
            $this->userFactory
        );

        $this->assertSame(
            $this->response->reveal(),
            $adapter->unauthorizedResponse($request)
        );
    }
}
