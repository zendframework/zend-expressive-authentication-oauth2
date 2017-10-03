<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Authentication\OAuth2\Pdo;

use Interop\Http\ServerMiddleware\DelegateInterface;
use League\OAuth2\Server\AuthorizationServer;
use PDO;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Authentication\OAuth2\OAuth2Middleware;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\AccessTokenRepository;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\ClientRepository;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\PdoService;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\ScopeRepository;

class OAuth2MiddlewareTest extends TestCase
{
    const DB_FILE        = __DIR__ . '/TestAsset/oauth2.sq3';
    const DB_SCHEMA      = __DIR__ . '/../../data/oauth2.sql';
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

    public function testProcess()
    {
        $request = new ServerRequest(
            [],
            [],
            '/oauth/token',
            'GET'
        );
        $authMiddleware = new OAuth2Middleware($this->authServer, $this->response);
        $response = $authMiddleware->process($request, $this->delegate->reveal());
        $this->assertEquals(400, $response->getStatusCode());
    }
}
