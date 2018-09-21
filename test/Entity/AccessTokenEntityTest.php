<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Authentication\OAuth2\Entity;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use PHPUnit\Framework\TestCase;
use Zend\Expressive\Authentication\OAuth2\Entity\AccessTokenEntity;

class AccessTokenEntityTest extends TestCase
{
    public function testConstructor()
    {
        $entity = new AccessTokenEntity();
        $this->assertInstanceOf(AccessTokenEntityInterface::class, $entity);
    }
}
