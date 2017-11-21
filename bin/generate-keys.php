<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-oauth2 for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/LICENSE.md
 *     New BSD License
 */

/**
 * Script to generate public, private and encryption keys for thephpleague/oauth2-server
 * @see https://oauth2.thephpleague.com/installation/
 */

$filePrivateKey = dirname(__DIR__) . '/data/private.key';
$filePublicKey = dirname(__DIR__) . '/data/public.key';

// Generate public/private keys with OpenSSL
$config = [
    'private_key_bits' => 1024,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
];

// Private key
$res = openssl_pkey_new($config);
openssl_pkey_export($res, $privateKey);
file_put_contents($filePrivateKey, $privateKey);

// Public key
$publicKey = openssl_pkey_get_details($res);
file_put_contents($filePublicKey, $publicKey["key"]);

printf("Private key stored in:\n%s\n", $filePrivateKey);
printf("Public key stored in:\n%s\n", $filePublicKey);
printf(
    "Encryption key (copy & paste in config/oauth2.php):\n%s\n",
    base64_encode(random_bytes(32))
);
