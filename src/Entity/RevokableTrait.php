<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 21/02/2018
 * Time: 10:14
 */

namespace Zend\Expressive\Authentication\OAuth2\Entity;

trait RevokableTrait
{
    /**
     * @var bool
     */
    protected $revoked;

    /**
     * @return bool
     */
    public function isRevoked(): bool
    {
        return $this->revoked;
    }

    /**
     * @param bool $revoked
     */
    public function setRevoked(bool $revoked): void
    {
        $this->revoked = $revoked;
    }
}
