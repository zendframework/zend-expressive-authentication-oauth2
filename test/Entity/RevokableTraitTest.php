<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Authentication\OAuth2\Entity;

use PHPUnit\Framework\TestCase;
use Zend\Expressive\Authentication\OAuth2\Entity\RevokableTrait;

class RevokableTraitTest extends TestCase
{
    public function testRevoked()
    {
        $mock = $this->getMockForTrait(RevokableTrait::class);
        
        $mock->setRevoked(true);
        $this->assertTrue($mock->isRevoked());
        $mock->setRevoked(false);
        $this->assertFalse($mock->isRevoked());
    }
}
