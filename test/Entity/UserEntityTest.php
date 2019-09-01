<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Authentication\OAuth2\Entity;

use ArgumentCountError;
use League\OAuth2\Server\Entities\UserEntityInterface;
use PHPUnit\Framework\TestCase;
use Zend\Expressive\Authentication\OAuth2\Entity\UserEntity;

class UserEntityTest extends TestCase
{
    /** @var UserEntity */
    private $entity;

    protected function setUp() : void
    {
        $this->entity = new UserEntity('foo');
    }

    public function testConstructorWithoutParamWillResultInAnException()
    {
        $this->expectException(ArgumentCountError::class);
        $entity = new UserEntity();
    }

    public function testImplementsUserEntityInterface()
    {
        $this->assertInstanceOf(UserEntityInterface::class, $this->entity);
    }

    public function testGetIdentifier()
    {
        $this->assertEquals('foo', $this->entity->getIdentifier());
    }
}
