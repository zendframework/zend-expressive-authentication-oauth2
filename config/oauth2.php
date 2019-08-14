<?php
 /**
 * To generate a private key run this command:
 * openssl genrsa -out private.key 1024
 *
 * To generate the encryption key use this command:
 * php -r 'echo base64_encode(random_bytes(32)), PHP_EOL;'
 *
 * The expire values must be a valid DateInterval format
 * @see http://php.net/manual/en/class.dateinterval.php
 */

$config = [
    'private_key'    => getcwd() . '/data/private.key',
    'public_key'     => getcwd() . '/data/public.key',
    'access_token_expire'  => 'P1D', // 1 day in DateInterval format
    'refresh_token_expire' => 'P1M', // 1 month in DateInterval format
    'auth_code_expire'     => 'PT10M', // 10 minutes in DateInterval format
    'pdo' => [
        'dsn'      => '',
        'username' => '',
        'password' => ''
    ],

    // Set value to null to disable a grant
    'grants' => [
        \League\OAuth2\Server\Grant\ClientCredentialsGrant::class
            => \League\OAuth2\Server\Grant\ClientCredentialsGrant::class,
        \League\OAuth2\Server\Grant\PasswordGrant::class
            => \League\OAuth2\Server\Grant\PasswordGrant::class,
        \League\OAuth2\Server\Grant\AuthCodeGrant::class
            => \League\OAuth2\Server\Grant\AuthCodeGrant::class,
        \League\OAuth2\Server\Grant\ImplicitGrant::class
            => \League\OAuth2\Server\Grant\ImplicitGrant::class,
        \League\OAuth2\Server\Grant\RefreshTokenGrant::class
            => \League\OAuth2\Server\Grant\RefreshTokenGrant::class
    ],
];

// Conditionally include the encryption_key config setting, based on presence of file.
$encryptionKeyFile = getcwd() . '/data/encryption.key';
if (is_readable($encryptionKeyFile)) {
    $config['encryption_key'] = require $encryptionKeyFile;
}

return $config;
