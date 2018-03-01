# Authorization code

The authorization code is used to authenticate a web application with a
third-party service (e.g., imagine you built a web application that needs to
consume the API of Facebook). You can authenticate your application using the
third-party server with a 3-step flow as illustrated in this diagram:

![Authorization code diagram](auth_code.png)

The web application sends a request (including the `client_id` and the
`redirect_uri`) to the authorization server asking for an Authorization code (1).
The authorization server shows an Allow/Deny page to the end-user requesting
authorization for access. If the user clicks on "Allow", the server sends the
authorization code to the web application using the `redirect_uri` (2).
The web application can now perform a token request, passing the `client_id`,
the `redirect_uri`, the `client_secret` and the authentication code to prove
that it is authorized to perform the request (3). The authorization server sends
the access token as response if the request is valid (4).

## Request the authorization code

The client sends the following parameter (query string) to the authorization
server:

- `response_type` = code;
- `client_id` with the client’s ID;
- `redirect_uri` with the client redirect URI. This parameter is optional,
  but if not send the user will be redirected to a pre-registered redirect URI;
- `scope` with a space-delimited list of requested scope permissions;
- `state` with a CSRF token. This parameter is optional but highly recommended.
  You can store the value of CSRF token in the user’s session to be validated
  in the next step.

The user will then be asked to login to the authorization server and approve
the client request. If the user approves the request they will be redirected
to the redirect URI with the following parameters in the query string:

- `code` with the authorization code;
- `state` with the CSRF parameter sent in the original request. You can compare
  this value with the one stored in the user’s session.

## Request the access token

The client send a POST request to the authorization server with the following
parameters:

- `grant_type` = authorization_code;
- `client_id` with the client’s ID;
- `client_secret` with the client’s secret;
- `redirect_uri` with the previous client redirect URI;
- `code` with the authorization code from the query string.

The authorization server responds with a JSON as follows:

```json
{
    "token_type" : "Bearer",
    "expires_in" : "3600",
    "refresh_token" : "YWYwNjhmNmZmMDhmZjkyOGJj...",
    "access_token" : "eyJ0eXAiOiJKV1Q..."
}
```

The `token_type` is the type of generated token (Bearer). The `expires_in` is
an integer representing the TTL (in seconds) of the access token.
The `refresh_token` a token that can be used to refresh the `access_token` when
expired.
The `access_token` contains the a `JWT` signed with the authorization server’s
private key. This token must be used in the `Authorization` request HTTP header.
