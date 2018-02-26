<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Authentication\OAuth2\Pdo;

use DateInterval;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;
use Zend\Expressive\Authentication\OAuth2\OAuth2Middleware;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\AccessTokenRepository;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\AuthCodeRepository;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\ClientRepository;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\PdoService;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\RefreshTokenRepository;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\ScopeRepository;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\UserRepository;

class OAuth2PdoMiddlewareTest extends TestCase
{
    const DB_FILE        = __DIR__ . '/TestAsset/test_oauth2.sq3';
    const DB_SCHEMA      = __DIR__ . '/../../data/oauth2.sql';
    const DB_DATA        = __DIR__ . '/TestAsset/test_data.sql';
    const PRIVATE_KEY    = __DIR__ .'/../TestAsset/private.key';
    const ENCRYPTION_KEY = 'T2x2+1OGrEzfS+01OUmwhOcJiGmE58UD1fllNn6CGcQ=';

    public static function setUpBeforeClass()
    {
        self::tearDownAfterClass();

        // Generate the OAuth2 database
        $pdo = new PDO('sqlite:' . self::DB_FILE);
        if (false === $pdo->exec(file_get_contents(self::DB_SCHEMA))) {
            throw new \Exception(sprintf(
                "The test cannot be executed without the %s db",
                self::DB_SCHEMA
            ));
        }
        // Insert the test values
        if (false === $pdo->exec(file_get_contents(self::DB_DATA))) {
            throw new \Exception(sprintf(
                "The test cannot be executed without the values in %s",
                self::DB_DATA
            ));
        }
    }

    public static function tearDownAfterClass()
    {
        if (file_exists(self::DB_FILE)) {
            unlink(self::DB_FILE);
        }
    }

    public function setUp()
    {
        $this->response = new Response();
        $this->pdoService = new PdoService('sqlite:' . self::DB_FILE);
        $this->clientRepository = new ClientRepository($this->pdoService);
        $this->accessTokenRepository = new AccessTokenRepository($this->pdoService);
        $this->scopeRepository = new ScopeRepository($this->pdoService);
        $this->userRepository = new UserRepository($this->pdoService);
        $this->refreshTokenRepository = new RefreshTokenRepository($this->pdoService);
        $this->authCodeRepository = new AuthCodeRepository($this->pdoService);

        $this->authServer = new AuthorizationServer(
            $this->clientRepository,
            $this->accessTokenRepository,
            $this->scopeRepository,
            self::PRIVATE_KEY,
            self::ENCRYPTION_KEY
        );

        $this->handler = $this->prophesize(RequestHandlerInterface::class);
    }

    public function testConstructor()
    {
        $authMiddleware = new OAuth2Middleware($this->authServer, $this->response);
        $this->assertInstanceOf(OAuth2Middleware::class, $authMiddleware);
    }

    /**
     * Test the Client Credential Grant
     *
     * @see https://oauth2.thephpleague.com/authorization-server/client-credentials-grant/
     */
    public function testProcessClientCredentialGrant()
    {
        // Enable the client credentials grant on the server
        $this->authServer->enableGrantType(
            new ClientCredentialsGrant(),
            new DateInterval('PT1H') // access tokens will expire after 1 hour
        );

        // Server request
        $params = [
            'grant_type'    => 'client_credentials',
            'client_id'     => 'client_test',
            'client_secret' => 'test',
            'scope'         => 'test'
        ];
        $request = $this->buildServerRequest(
            'POST',
            '/access_token',
            http_build_query($params),
            $params,
            [ 'Content-Type' => 'application/x-www-form-urlencoded' ]
        );
        $authMiddleware = new OAuth2Middleware($this->authServer, $this->response);
        $response = $authMiddleware->process($request, $this->handler->reveal());

        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode((string) $response->getBody());
        $this->assertEquals('Bearer', $content->token_type);
        $this->assertInternalType("int", $content->expires_in);
        $this->assertNotEmpty($content->access_token);
    }

    /**
     * Test the Password Grant
     *
     * @see https://oauth2.thephpleague.com/authorization-server/resource-owner-password-credentials-grant/
     */
    public function testProcessPasswordGrant()
    {
        $grant = new PasswordGrant(
            $this->userRepository,
            $this->refreshTokenRepository
        );
        $grant->setRefreshTokenTTL(new DateInterval('P1M')); // expire after 1 month
        // Enable the password grant on the server
        $this->authServer->enableGrantType(
            $grant,
            new DateInterval('PT1H') // access tokens will expire after 1 hour
        );
        // Server request
        $params = [
            'grant_type'    => 'password',
            'client_id'     => 'client_test',
            'client_secret' => 'test',
            'scope'         => 'test',
            'username'      => 'user_test',
            'password'      => 'test'
        ];
        $request = $this->buildServerRequest(
            'POST',
            '/access_token',
            http_build_query($params),
            $params,
            [ 'Content-Type' => 'application/x-www-form-urlencoded' ]
        );
        $authMiddleware = new OAuth2Middleware($this->authServer, $this->response);
        $response = $authMiddleware->process($request, $this->handler->reveal());

        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode((string) $response->getBody());
        $this->assertEquals('Bearer', $content->token_type);
        $this->assertInternalType("int", $content->expires_in);
        $this->assertNotEmpty($content->access_token);
        $this->assertNotEmpty($content->refresh_token);
    }

    /**
     * Test the Authorization Code Grant (Part One)
     *
     * @see https://oauth2.thephpleague.com/authorization-server/auth-code-grant/
     */
    public function testProcessGetAuthorizationCode()
    {
        $grant = new AuthCodeGrant(
            $this->authCodeRepository,
            $this->refreshTokenRepository,
            new DateInterval('PT10M') // authorization codes will expire after 10 minutes
        );
        $grant->setRefreshTokenTTL(new DateInterval('P1M')); // refresh tokens will expire after 1 month

        // Enable the authentication code grant on the server
        $this->authServer->enableGrantType(
            $grant,
            new DateInterval('PT1H') // access tokens will expire after 1 hour
        );
        $state = bin2hex(random_bytes(10)); // CSRF token
        // Server request
        $params = [
            'response_type' => 'code',
            'client_id'     => 'client_test2',
            'redirect_uri'  => '/redirect',
            'scope'         => 'test',
            'state'         => $state
        ];
        $request = $this->buildServerRequest(
            'GET',
            '/auth_code?' . http_build_query($params),
            '',
            [],
            [],
            $params
        );

        $authMiddleware = new OAuth2Middleware($this->authServer, $this->response);
        $response = $authMiddleware->process($request, $this->handler->reveal());

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        [$url, $queryString] = explode('?', $response->getHeader('Location')[0]);
        $this->assertEquals($params['redirect_uri'], $url);
        parse_str($queryString, $data);
        $this->assertTrue(isset($data['code']));
        $this->assertTrue(isset($data['state']));
        $this->assertEquals($state, $data['state']);

        return $data['code'];
    }

    /**
     * Test the Authorization Code Grant (Part Two)
     *
     * @see https://oauth2.thephpleague.com/authorization-server/auth-code-grant/
     * @depends testProcessGetAuthorizationCode
     */
    public function testProcessFromAuthorizationCode(string $code)
    {
        $grant = new AuthCodeGrant(
            $this->authCodeRepository,
            $this->refreshTokenRepository,
            new DateInterval('PT10M') // authorization codes will expire after 10 minutes
        );
        $grant->setRefreshTokenTTL(new DateInterval('P1M')); // refresh tokens will expire after 1 month

        // Enable the authentication code grant on the server
        $this->authServer->enableGrantType(
            $grant,
            new DateInterval('PT1H') // access tokens will expire after 1 hour
        );

        // Server request
        $params = [
            'grant_type'    => 'authorization_code',
            'client_id'     => 'client_test2',
            'client_secret' => 'test',
            'redirect_uri'  => '/redirect',
            'code'          => $code
        ];
        $request = $this->buildServerRequest(
            'POST',
            '/access_token',
            http_build_query($params),
            $params,
            [ 'Content-Type' => 'application/x-www-form-urlencoded' ]
        );
        $authMiddleware = new OAuth2Middleware($this->authServer, $this->response);
        $response = $authMiddleware->process($request, $this->handler->reveal());

        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode((string) $response->getBody());
        $this->assertEquals('Bearer', $content->token_type);
        $this->assertInternalType("int", $content->expires_in);
        $this->assertNotEmpty($content->access_token);
        $this->assertNotEmpty($content->refresh_token);

        return $content->refresh_token;
    }

    /**
     * Test the Implicit Grant
     *
     * @see https://oauth2.thephpleague.com/authorization-server/implicit-grant/
     */
    public function testProcessImplicitGrant()
    {
        // Enable the implicit grant on the server
        $this->authServer->enableGrantType(
            new ImplicitGrant(new DateInterval('PT1H')),
            new DateInterval('PT1H') // access tokens will expire after 1 hour
        );
        $state = bin2hex(random_bytes(10)); // CSRF token
        // Server request
        $params = [
            'response_type' => 'token',
            'client_id'     => 'client_test2',
            'redirect_uri'  => '/redirect',
            'scope'         => 'test',
            'state'         => $state
        ];
        $request = $this->buildServerRequest(
            'GET',
            '/authorize?' . http_build_query($params),
            '',
            [],
            [],
            $params
        );
        $authMiddleware = new OAuth2Middleware($this->authServer, $this->response);
        $response = $authMiddleware->process($request, $this->handler->reveal());

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        [$url, $fragment] = explode('#', $response->getHeader('Location')[0]);
        $this->assertEquals($params['redirect_uri'], $url);
        parse_str($fragment, $data);
        $this->assertTrue(isset($data['access_token']));
        $this->assertTrue(isset($data['expires_in']));
        $this->assertTrue(isset($data['token_type']));
        $this->assertEquals('bearer', strtolower($data['token_type']));
        $this->assertFalse(isset($data['refresh_token']));
        $this->assertTrue(isset($data['state']));
        $this->assertEquals($state, $data['state']);
    }

    /**
     * Test the Refresh Token Grant
     *
     * @see https://oauth2.thephpleague.com/authorization-server/refresh-token-grant/
     * @depends testProcessFromAuthorizationCode
     */
    public function testProcessRefreshTokenGrant(string $refreshToken)
    {
        $grant = new RefreshTokenGrant($this->refreshTokenRepository);
        $grant->setRefreshTokenTTL(new DateInterval('P1M')); // new refresh tokens will expire after 1 month

        // Enable the refresh token grant on the server
        $this->authServer->enableGrantType(
            $grant,
            new DateInterval('PT1H') // new access tokens will expire after an hour
        );
        // Server request
        $params = [
            'grant_type'    => 'refresh_token',
            'client_id'     => 'client_test2',
            'client_secret' => 'test',
            'refresh_token' => $refreshToken,
            'scope'         => 'test'
        ];
        $request = $this->buildServerRequest(
            'POST',
            '/access_token',
            http_build_query($params),
            $params,
            [ 'Content-Type' => 'application/x-www-form-urlencoded' ]
        );

        $authMiddleware = new OAuth2Middleware($this->authServer, $this->response);
        $response = $authMiddleware->process($request, $this->handler->reveal());

        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode((string) $response->getBody());
        $this->assertEquals('Bearer', $content->token_type);
        $this->assertInternalType("int", $content->expires_in);
        $this->assertNotEmpty($content->access_token);
        $this->assertNotEmpty($content->refresh_token);
    }

    /**
     * Build a ServerRequest object
     */
    protected function buildServerRequest(
        string $method,
        string $url,
        string $body,
        array $params,
        array $headers = [],
        array $queryParams = []
    ) : ServerRequest {
        $stream = new Stream('php://temp', 'w');
        $stream->write($body);

        return new ServerRequest(
            [],
            [],
            $url,
            $method,
            $stream,
            $headers,
            [],
            $queryParams,
            $params
        );
    }
}
