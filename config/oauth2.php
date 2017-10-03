<?php
/**
 * To generate a private key run this command:
 * openssl genrsa -out private.key 1024
 *
 * To generate the encryption key use this command:
 * php -r 'echo base64_encode(random_bytes(32)), PHP_EOL;'
 */
return [
    'private-key' => __DIR__ . '/../data/private.key',
    'encryption-key' => file_get_contents(__DIR__ . '/../data/encryption.key'),
    'pdo' => [
        'dsn' => '',
        'username' => '',
        'password' => ''
    ]
];
