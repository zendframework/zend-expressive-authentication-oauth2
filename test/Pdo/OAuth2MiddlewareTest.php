<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Authentication\OAuth2\Pdo;

use DateInterval;
use Interop\Http\ServerMiddleware\DelegateInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use PDO;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;
use Zend\Diactoros\Request\Serializer as RequestSerializer;
use Zend\Diactoros\Response\Serializer as ResponseSerializer;
use Zend\Expressive\Authentication\OAuth2\OAuth2Middleware;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\AccessTokenRepository;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\ClientRepository;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\PdoService;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\ScopeRepository;

class OAuth2MiddlewareTest extends TestCase
{
    const DB_FILE        = __DIR__ . '/TestAsset/test_oauth2.sq3';
    const DB_SCHEMA      = __DIR__ . '/../../data/oauth2.sql';
    const DB_DATA        = __DIR__ . '/TestAsset/test_data.sql';
    const PRIVATE_KEY    = __DIR__ .'/TestAsset/private.key';
    const ENCRYPTION_KEY = 'T2x2+1OGrEzfS+01OUmwhOcJiGmE58UD1fllNn6CGcQ=';

    public static function setUpBeforeClass()
    {
        self::tearDownAfterClass();

        // Generate the OAuth2 database
        $pdo = new PDO('sqlite:' . self::DB_FILE);
        if (false === $pdo->exec(file_get_contents(self::DB_SCHEMA))) {
            throw new \Exception(sprintf(
                "The test cannot be executed without the %s db",
                $dbSchema
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
        $this->stream = new Stream('php://temp', 'w');

        $this->authServer = new AuthorizationServer(
            $this->clientRepository,
            $this->accessTokenRepository,
            $this->scopeRepository,
            self::PRIVATE_KEY,
            self::ENCRYPTION_KEY
        );

        $this->delegate = $this->prophesize(DelegateInterface::class);
    }

    public function testConstructor()
    {
        $authMiddleware = new OAuth2Middleware($this->authServer, $this->response);
        $this->assertInstanceOf(OAuth2Middleware::class, $authMiddleware);
    }

    public function testProcessClientCredentialGrant()
    {
        // Enable the client credentials grant on the server
        $this->authServer->enableGrantType(
            new ClientCredentialsGrant(),
            new DateInterval('PT1H') // access tokens will expire after 1 hour
        );

        // Build the server request
        $params = [
            'grant_type'    => 'client_credentials',
            'client_id'     => 'client_test',
            'client_secret' => 'test',
            'scope'         => 'test'
        ];
        $this->stream->write(http_build_query($params));
        $request = $this->buildRequest(
            'POST',
            '/access_token',
            $this->stream,
            [ 'Content-Type' => 'application/x-www-form-urlencoded' ],
            $params
        );
        $authMiddleware = new OAuth2Middleware($this->authServer, $this->response);
        $response = $authMiddleware->process($request, $this->delegate->reveal());

        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode((string) $response->getBody());
        $this->assertEquals('Bearer', $content->token_type);
        $this->assertEquals(3600, $content->expires_in);
        $this->assertNotEmpty($content->access_token);
    }

    protected function buildRequest($method, $url, $stream, $headers, $params)
    {
        return new ServerRequest(
            [],
            [],
            $url,
            $method,
            $stream,
            $headers,
            [],
            [],
            $params
        );
    }
}
