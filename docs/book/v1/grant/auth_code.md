# Authorization code

The authorization code is used to authenticate a web application with a
third-party service (e.g., imagine you built a web application that needs to
consume the API of Facebook). You can authenticate your application using the
third-party server with a 4-step flow as illustrated in this diagram:

![Authorization code diagram](auth_code.png)

The web application sends a request (including the `client_id` and the
`redirect_uri`) to the authorization server asking for an Authorization code (1).
The authorization server shows an Allow/Deny page to the end-user requesting
authorization for access. If the user clicks on "Allow", the server sends the
authorization code to the web application using the `redirect_uri` (2).
The web application can now perform a token request, passing the `client_id`,
the `redirect_uri`, the `client_secret`, and the authentication code to prove
that it is authorized to perform the request (3). The authorization server sends
the access token in response if the request is valid (4).

## Request the authorization code

The client sends the following parameters via query string arguments to the
authorization server:

- `response_type` = code.
- `client_id` with the client identifer.
- `redirect_uri` with the URI to which to redirect the client following
  successful authorization. This parameter is optional, but if it is not sent,
  the user will be redirected to a default location on completion.
- `scope` with a space-delimited list of requested scope permissions.
- `state` with a Cross-Site Request Forgery (CSRF) token. This parameter is
  optional, but highly recommended.  You can store the value of the CSRF token in
  the user’s session to be validated in the next step.

The user will then be asked to login to the authorization server and approve the
client request. If the user approves the request they will be redirected to the
redirect URI with the following parameters in the query string arguments:

- `code` with the authorization code.
- `state` with the CSRF parameter sent in the original request. You can compare
  this value with the one stored in the user’s session.

## Request the access token

The client sends a POST request to the authorization server with the following
parameters:

- `grant_type` = authorization_code.
- `client_id` with the client’s ID.
- `client_secret` with the client’s secret.
- `redirect_uri` with the previous client redirect URI.
- `code` with the authorization code as returned in the authorization code
  request (as detailed in the previous section).

The authorization server responds with a JSON payload similar to the following:

```json
{
    "token_type" : "Bearer",
    "expires_in" : "3600",
    "refresh_token" : "YWYwNjhmNmZmMDhmZjkyOGJj...",
    "access_token" : "eyJ0eXAiOiJKV1Q..."
}
```

The values are as follows:

- The `token_type` is the type of generated token (here, and generally,
  "Bearer").
- The `expires_in` value is an integer representing the time-to-live (in
  seconds) of the access token.
- The `refresh_token` is a token that can be used to refresh the `access_token`
  when expired.
- The `access_token` contains a JSON Web Token (JWT) signed with the
  authorization server’s private key. This token must be used in the
  `Authorization` request HTTP header on subsequent requests.
