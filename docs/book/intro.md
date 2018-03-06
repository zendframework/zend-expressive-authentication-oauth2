# zendframework/zend-expressive-authentication-oauth2

This component provides [OAuth2](https://oauth.net/2/) (server) authentication
for [Expressive](https://docs.zendframework.com/zend-expressive/) and
[PSR-7](https://www.php-fig.org/psr/psr-7/) applications. It implements
`Zend\Expressive\Authentication\AuthenticationInterface`, and it be used as
an adapter for [zend-expressive-authentication](https://github.com/zendframework/zend-expressive-authentication).

This library uses the [league/oauth2-server](https://oauth2.thephpleague.com/)
package for implementing the OAuth2 server.

If you need an introduction to OAuth2, you can read the following references:

- [OAuth2 documentation](https://apigility.org/documentation/auth/authentication-oauth2)
  from the Apigility project.
- [An Introduction to OAuth 2](https://www.digitalocean.com/community/tutorials/an-introduction-to-oauth-2)
  by Digital Ocean.
- The [OAuth2 specification](https://oauth.net/2/) itself, via its official
  website.

## Installation

In order to implement the OAuth2 server, we first need to configure it. The
first step is to generate new cryptographic keys. We need to execute the script
`bin/generate-keys.php` in order to generate these keys.

```bash
$ php vendor/bin/generate-keys.php
```

This script will store the keys in the `data` folder:

```
Private key stored in:
./data/private.key
Public key stored in:
./data/public.key
Encryption key stored in:
./data/encryption.key
```

The script will generate public and private keys, and an encryption key.
These keys are used by [league/oauth2-server](https://oauth2.thephpleague.com/)
as security settings for the OAuth2 server infrastructure.

## Configuration

The OAuth2 server is configured by the `authentication` configuration key in the
PSR-11 container (e.g. [zend-servicemanager](https://github.com/zendframework/zend-servicemanager)).

The default values are:

```php
return [
    'private_key'    => __DIR__ . '/../data/private.key',
    'public_key'     => __DIR__ . '/../data/public.key',
    'encryption_key' => require __DIR__ . '/../data/encryption.key',
    'access_token_expire'  => 'P1D',
    'refresh_token_expire' => 'P1M',
    'auth_code_expire'     => 'PT10M',
    'pdo' => [
        'dsn'      => '',
        'username' => '',
        'password' => ''
    ]
];
```

The `private_key` and `public_key` values contains the paths to the previous
generated pair of keys. The `encryption_key` contains the encryption key value
as a string, as stored in the `data/encryption.key` file.

The `access_token_expire` value is the time-to-live (TTL) value of the access
token. The time period is represented using the [DateInterval](http://php.net/manual/en/class.dateinterval.php)
format in PHP.  The default value is `P1D` (1 day).

The `refresh_token_expire` value is the TTL used for the refresh token. The
default value is 1 month.

The `auth_code_expire` value is th TTL of the authentication code, used in
the [authorization code grant](https://oauth2.thephpleague.com/authorization-server/auth-code-grant/)
scenario. The default value is 10 minutes.

The last parameter is the PDO database configuration. Here we need to insert
the parameters to access the OAuth2 database. These parameters are the `dsn`,
the `username`, and the `password`, if required. The SQL structure of this
database is stored in the [data/oauth2.sql](https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/data/oauth2.sql)
file.

You need to provide an OAuth2 database yourself, or generate a [SQLite](https://www.sqlite.org)
database with the following command (using `sqlite3` for GNU/Linux):

```bash
$ sqlite3 data/oauth2.sqlite < data/oauth2.sql
```

You can also create some testing values using the `data/oauth2_test.sql` file:

```bash
$ sqlite3 data/oauth2.sqlite < data/oauth2_test.sql
```

These commands will insert the following testing values:

- a client `client_test` with secret `test`, used for [client_credentials](grant/client_credentials.md)
  and the [password](grant/password.md) grant type.
- a client `client_test2` with secret `test`, used for [authorization code](grant/auth_code.md)
  and [implicit](grant/implicit.md) grant type.
- a user `user_test` with password `test`.
- a `test` scope.

For security reason, the client `secret` and the user `password` are stored
using the `bcrypt` algorithm provided by [password_hash](http://php.net/manual/en/function.password-hash.php)
function of PHP.
