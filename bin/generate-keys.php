<?php
// Generate public, private and encryption keys for thephpleague/oauth2-server
// @see https://oauth2.thephpleague.com/installation/

$filePrivateKey = dirname(__DIR__) . '/data/private.key';
$filePublicKey = dirname(__DIR__) . '/data/public.key';
$fileEncryptionKey = dirname(__DIR__) . '/data/encryption.key';

// Generate public/private keys with OpenSSL
$config = array(
    "private_key_bits" => 1024,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
);

// Private key
$res = openssl_pkey_new($config);
openssl_pkey_export($res, $privateKey);
file_put_contents($filePrivateKey, $privateKey);

// Public key
$publicKey = openssl_pkey_get_details($res);
file_put_contents($filePublicKey, $publicKey["key"]);

// Encryption key
$encryptionKey = base64_encode(random_bytes(32));
file_put_contents($fileEncryptionKey, $encryptionKey);

printf("Private key stored in:\n%s\n", $filePrivateKey);
printf("Public key stored in:\n%s\n", $filePublicKey);
printf("Encryption key stored in:\n%s\n", $fileEncryptionKey);
