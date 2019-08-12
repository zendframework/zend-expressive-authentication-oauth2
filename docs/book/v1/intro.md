# zendframework/zend-expressive-authentication-oauth2

This component provides [OAuth2](https://oauth.net/2/) (server) authentication
for [Expressive](https://docs.zendframework.com/zend-expressive/) and
[PSR-7](https://www.php-fig.org/psr/psr-7/)/[PSR-15](https://www.php-fig.org/psr/psr-15/)
applications. It implements `Zend\Expressive\Authentication\AuthenticationInterface`,
and it can be used as an adapter for [zend-expressive-authentication](https://github.com/zendframework/zend-expressive-authentication).

This library uses the [league/oauth2-server](https://oauth2.thephpleague.com/)
package for implementing the OAuth2 server.

If you need an introduction to OAuth2, you can read the following references:

- [OAuth2 documentation](https://apigility.org/documentation/auth/authentication-oauth2)
  from the Apigility project.
- [An Introduction to OAuth 2](https://www.digitalocean.com/community/tutorials/an-introduction-to-oauth-2)
  by DigitalOcean.
- The [OAuth2 specification](https://oauth.net/2/) itself, via its official
  website.

## Installation

In order to implement the OAuth2 server, we first need to configure it. The
first step is to generate new cryptographic keys. We need to execute the script
`./vendor/bin/generate-oauth2-keys` in order to generate these keys.

```bash
$ ./vendor/bin/generate-oauth2-keys
```

This script will store the keys in the application's `data` folder if found:

```text
Private key stored in:
./data/oauth/private.key
Public key stored in:
./data/oauth/public.key
Encryption key stored in:
./data/oauth/encryption.key
```

The script will generate public and private keys, and an encryption key.
These keys are used by [league/oauth2-server](https://oauth2.thephpleague.com/)
as security settings for the OAuth2 server infrastructure.

## Configuration

The OAuth2 server is configured by the `authentication` configuration key in the
PSR-11 container (e.g. [zend-servicemanager](https://github.com/zendframework/zend-servicemanager)).

The default values are:

```php
use League\OAuth2\Server\Grant;

return [
    'private_key'    => __DIR__ . '/../data/oauth/private.key',
    'public_key'     => __DIR__ . '/../data/oauth/public.key',
    'encryption_key' => require __DIR__ . '/../data/oauth/encryption.key',
    'access_token_expire'  => 'P1D',
    'refresh_token_expire' => 'P1M',
    'auth_code_expire'     => 'PT10M',
    'pdo' => [
        'dsn'      => '',
        'username' => '',
        'password' => ''
    ],

    // Set value to null to disable a grant
    'grants' => [
        Grant\ClientCredentialsGrant::class => Grant\ClientCredentialsGrant::class,
        Grant\PasswordGrant::class          => Grant\PasswordGrant::class,
        Grant\AuthCodeGrant::class          => Grant\AuthCodeGrant::class,
        Grant\ImplicitGrant::class          => Grant\ImplicitGrant::class,
        Grant\RefreshTokenGrant::class      => Grant\RefreshTokenGrant::class
    ],
];
```

The `private_key` and `public_key` values contains the paths to the previous
generated pair of keys. The `encryption_key` contains the encryption key value
as a string, as stored in the `data/oauth/encryption.key` file.

By default both key files are checked for correct permissions (chmod 400, 440,
600, 640 or 660 is expected, and 600 or 660 is recommended). In case the
environment/operating system (e.g. Windows) does not support such a permissions,
the check can be disabled:

```php
    // ...
    'private_key' => [
        'key_or_path' => __DIR__ . '/../data/oauth/private.key',
        'key_permissions_check' => false,
    ],
    // ...
```

The `access_token_expire` value is the time-to-live (TTL) value of the access
token. The time period is represented using the [DateInterval](http://php.net/manual/en/class.dateinterval.php)
format in PHP.  The default value is `P1D` (1 day).

The `refresh_token_expire` value is the TTL used for the refresh token. The
default value is 1 month.

The `auth_code_expire` value is the TTL of the authentication code, used in
the [authorization code grant](https://oauth2.thephpleague.com/authorization-server/auth-code-grant/)
scenario. The default value is 10 minutes.

The `pdo` value is for the PDO database configuration. Here we need to insert
the parameters to access the OAuth2 database. These parameters are the `dsn`,
the `username`, and the `password`, if required. The SQL structure of this
database is stored in the [data/oauth2.sql](https://github.com/zendframework/zend-expressive-authentication-oauth2/blob/master/data/oauth2.sql)
file.

If you already have a PDO service configured, you can instead pass the service
name to the `pdo` key as follows:

```php
return [
    'pdo' => 'myServiceName',
];
```

The `grants` array is for enabling/disabling grants. By default, all the supported
grants are configured to be available. If you would like to disable any of the
supplied grants, change the value for the grant to `null`. Additionally,
you can extend this array to add your own custom grants.

### Configure Event Listeners

_Optional_ The `event-listeners` and `event-listener-providers` arrays may be used to enable event listeners for events published by `league\oauth2-server`. See the [Authorization Server Domain Events documentation](https://oauth2.thephpleague.com/authorization-server/events/). The possible event names can be found [in `League\OAuth2\Server\RequestEvent`](https://github.com/thephpleague/oauth2-server/blob/0b0b43d43342c0909b3b32fb7a09d502c368d2ec/src/RequestEvent.php#L17-L22).

#### Event Listeners

The `event-listeners` key must contain an array of arrays. Each array element must contain at least 2 elements and may include a 3rd element. These roughly correspond to the arguments passed to [`League\Event\ListenerAcceptorInterface::addListener()`](https://github.com/thephpleague/event/blob/d2cc124cf9a3fab2bb4ff963307f60361ce4d119/src/ListenerAcceptorInterface.php#L43). The first element must be a string -- either the [wildcard (`*`)](https://event.thephpleague.com/2.0/listeners/wildcard/) or a [single event name](https://event.thephpleague.com/2.0/events/named/). The second element must be either a callable, a concrete instance of `League\Event\ListenerInterface`, or a string pointing to your listener service instance in the container. The third element is optional, and must be an integer if provided.

See the [documentation for callable listeners](https://event.thephpleague.com/2.0/listeners/callables/).

#### Event Listener Providers

The `event-listener-providers` key must contain an array. Each array element must contain either a concrete instance of `League\Event\ListenerProviderInterface` or a string pointing to your container service instance of a listener provider.

See the [documentation for listener providers](https://event.thephpleague.com/2.0/listeners/providers/).

Example config:

```php
return [    
    'event-listeners' => [
        // using a container service
        [
            \League\OAuth2\Server\RequestEvent::CLIENT_AUTHENTICATION_FAILED,
            \My\Event\Listener\Service::class,
        ],
        // using a callable
        [
            \League\OAuth2\Server\RequestEvent::ACCESS_TOKEN_ISSUED,
            function (\League\OAuth2\Server\RequestEvent $event) {
                // do something
            },
        ],
    ],
    'event-listener-providers' => [
        \My\Event\ListenerProvider\Service::class,
    ],
];
```

## OAuth2 Database

You need to provide an OAuth2 database yourself, or generate a [SQLite](https://www.sqlite.org)
database with the following command (using `sqlite3` for GNU/Linux):

```bash
$ sqlite3 data/oauth2.sqlite < vendor/zendframework/zend-expressive-authentication-oauth2/data/oauth2.sql
```

You can also create some testing values using the `data/oauth2_test.sql` file:

```bash
$ sqlite3 data/oauth2.sqlite < vendor/zendframework/zend-expressive-authentication-oauth2/data/oauth2_test.sql
```

These commands will insert the following testing values:

- a client `client_test` with secret `test`, used for [client_credentials](grant/client_credentials.md)
  and the [password](grant/password.md) grant type.
- a client `client_test2` with secret `test`, used for [authorization code](grant/auth_code.md)
  and [implicit](grant/implicit.md) grant type.
- a user `user_test` with password `test`.
- a `test` scope.

For security reason, the client `secret` and the user `password` are stored
using the `bcrypt` algorithm as used by the [password_hash](http://php.net/manual/en/function.password-hash.php)
function.

## Configure OAuth2 Routes

As the final step, in order to use the OAuth2 server you need to configure the routes
for the **token endpoint** and **authorization**.

You can read how add the **token endpoint** and the **authorization** routes in
the [Implement an authorization server](authorization-server.md) section.
