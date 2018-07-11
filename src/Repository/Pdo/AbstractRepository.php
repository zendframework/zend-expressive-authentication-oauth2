<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Authentication\OAuth2\Repository\Pdo;

use function array_reduce;
use function trim;

class AbstractRepository
{
    /**
     * @var PdoService
     */
    protected $pdo;

    /**
     * Constructor
     *
     * @param PdoService $pdo
     */
    public function __construct(PdoService $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Return a string of scopes, separated by space
     * from ScopeEntityInterface[]
     *
     * @param ScopeEntityInterface[] $scopes
     * @return string
     */
    protected function scopesToString(array $scopes) : string
    {
        if (empty($scopes)) {
            return '';
        }

        return trim(array_reduce($scopes, function ($result, $item) {
            return $result . ' ' . $item->getIdentifier();
        }));
    }
}
