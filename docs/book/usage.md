# Usage

If you successfully configured the OAuth2 server following the document reported
in the [installation](intro.md) section, you can request an access token using
the default `/oauth` route.

You can require an access token using one of the following scenarios:

- [client credentials](grant/client_credentials.md);
- [password](grant/password.md);
- [authorization code](grant/auth_code.md);
- [implicit](grant/implicit.md);
- [refresh token](grant/refresh_token.md).

## Authenticate a middleware

This library uses the authentication abstraction of `Zend\Expressive\Authentication\AuthenticationMiddleware`
class provided by [zend-expressive-authentication](https://github.com/zendframework/zend-expressive-authentication).

In order to use OAuth2 we need to configure the service `Zend\Expressive\Authentication\AuthenticationInterface`
to resolve in `Zend\Expressive\Authentication\OAuth2\OAuth2Adapter`. Using the
[zend-servicemanager](https://github.com/zendframework/zend-servicemanager) this
can be achieved using `aliases` with the following configuration:

```php
use Zend\Expressive\Authentication;

return [
    'dependencies' => [
        'aliases' => [
            Authentication\AuthenticationInterface::class =>
                Authentication\OAuth2\OAuth2Adapter::class
        ]
    ]
];
```

The previous configuration will instruct `zend-expressive-authentication` to use
the OAuth2 adapter. This adapter does not require a `Zend\Expressive\Authentication\UserRepositoryInterface`.
The OAuth2 database with user and client credentials is managed by the component
itself.

When the service alias is configured you can use start authenticate your
application/API adding the `AuthenticationMiddleware` as first middleware to be
executed in the pipeline. For instance, using an [Expressive](https://docs.zendframework.com/zend-expressive/)
application you can add it to a specific route, as follows:

```php
$app->post('/api/users', [
    Zend\Expressive\Authentication\AuthenticationMiddleware::class,
    App\Action\AddUserAction::class
], 'api.add.user');
```
