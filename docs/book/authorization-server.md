# Implement an authorization server

This library provides the basics to implement an authorization server 
for your application.

Since there are authorization flows that require user interaction,
your application is expected to provide the middleware to handle this.

## Add the token endpoint

This is the most simple part, since this library provides 
`Zend\Expressive\Authentication\OAuth2\TokenHandler` to deal with it.

This endpoint must accept POST requests.

For example:

```php
use Zend\Expressive\Authentication\OAuth2;

$app->route('/oauth2/token', OAuth2\TokenHandler::class, ['POST']);
```

## Add the authorization endpoint

The authorization endpoint is an url of to which the client redirects
to obtain an access token or authorization code.

This endpoint must accept GET requests and should:

 - Validate the request (especially for a valid client id and redirect url)
 - Make sure the User is authenticated (for example by showing a login 
   prompt if needed)
 - Optionally request the users consent to grant access to the client
 - Redirect to a specified url of the client with success or error information
 
The first and the last part is provided by this library.

For example, to add the authorization endpoint you can declare a middleware pipe
to compose these parts:

```php
use Zend\Expressive\Authentication\OAuth2;

$app->route('/oauth2/authorize', [
    OAuth2\AuthorizatonMiddleware,
    
    // The followig middleware is provided by your application (see below)
    Application\OAuthAuthorizationMiddleware::class, 
    
    OAuth2\AuthorizationHandler
], ['GET', 'POST']);
```

In your `Application\OAuthAuthorizationMiddleware`, you'll have access
to the `League\OAuth2\Server\RequestTypes\AuthorizationRequest` via the
psr-7 request. Your middleware should populate the user entity with `setUser()` and the
user's consent decision with `setAuthorizationApproved()` to this authorization
request instance.

```php
<?php

namespace Application;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;

class OAuthAuthorizationMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface 
    {
        // Assume a middleware handled the authentication check and
        // populates the user object which also implements the
        // OAuth2 UserEntityInterface
        $user = $request->getAttribute('authenticated_user');
        
        // Assume some middleware handles and populates a session
        // container
        $session = $request->getAttribute('session');
        
        // This is populated by the previous middleware
        /** @var AuthorizationRequest $authRequest */
        $authRequest = $request->getAttribute(AuthorizationRequest::class);
 
        // the user is authenticated
        if ($user) {
            $authRequest->setUser($user);
            
            // Assume all clients are trusted, but you could
            // handle consent here or within the next middleware
            // as needed
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

