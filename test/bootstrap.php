<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

// change the permission of private and public keys to 0600
chmod(__DIR__ . '/TestAsset/private.key', 0600);
chmod(__DIR__ . '/TestAsset/public.key', 0600);

require __DIR__ . '/../vendor/autoload.php';
