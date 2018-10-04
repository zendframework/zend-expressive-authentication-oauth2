# Implement an authorization server

This library provides the basics for implementing an authorization server
for your application.

Since there are authorization flows that require user interaction,
**your application is expected to provide the middleware to handle this**.

## Add the token endpoint

Adding the token endpoint involves routing to the provided
`Zend\Expressive\Authentication\OAuth2\TokenEndpointHandler`.

This endpoint **MUST** accept `POST` requests.

For example:

```php
use Zend\Expressive\Authentication\OAuth2;

$app->post('/oauth2/token', OAuth2\TokenEndpointHandler::class);
```

## Add the authorization endpoint

The authorization endpoint is the URL to which the client redirects
to obtain an access token or authorization code.

This endpoint **MUST** accept `GET` requests and should:

- Validate the request (especially for a valid client id and redirect url).
 
- Make sure the user is authenticated (for example, by showing a login
  prompt if needed).

- Optionally, request the user's consent to grant access to the client.

- Redirect to a specified url of the client with success or error information.

The first and the last items are provided by this library.

For example, to add the authorization endpoint, you can declare a middleware
pipeline for the route as follows:

```php
use Zend\Expressive\Authentication\OAuth2;
use Zend\Expressive\Session\SessionMiddleware;

$app->route('/oauth2/authorize', [
    SessionMiddleware::class,

    OAuth2\AuthorizationMiddleware::class,

    // The following middleware is provided by your application (see below):
    App\OAuthAuthorizationMiddleware::class,

    OAuth2\AuthorizationHandler::class
], ['GET', 'POST']);
```

In your `App\OAuthAuthorizationMiddleware`, you'll have access to the
`League\OAuth2\Server\RequestTypes\AuthorizationRequest` via the PSR-7 request.
Your middleware should populate the `AuthorizationRequest`'s user entity via its
`setUser()` method, and the user's consent decision via the
`setAuthorizationApproved()`method.

As an example:

```php
namespace App;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Zend\Expressive\Authentication\UserInterface;

class OAuthAuthorizationMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        // Assume a middleware handled the authentication check and
        // populates the user object, which also implements the
        // OAuth2 UserEntityInterface
        $user = $request->getAttribute(UserInterface::class);

        // Assume the SessionMiddleware handles and populates a session
        // container
        $session = $request->getAttribute('session');

        // This is populated by the previous middleware:
        /** @var AuthorizationRequest $authRequest */
        $authRequest = $request->getAttribute(AuthorizationRequest::class);

        // The user is authenticated:
        if ($user) {
            $authRequest->setUser($user);

            // This assumes all clients are trusted, but you could
            // handle consent here, or within the next middleware
            // as needed.
            $authRequest->setAuthorizationApproved(true);

            return $handler->handle($request);
        }

        // The user is not authenticated, show login form ...

        // Store the auth request state
        // NOTE: Do not attempt to serialize or store the authorization
        // request object. Store the query parameters instead and redirect
        // with these to this endpoint again to replay the request.
        $session['oauth2_request_params'] = $request->getQueryParams();

        return new RedirectResponse('/oauth2/login');
    }
}
```
