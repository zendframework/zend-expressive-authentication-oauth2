# Implicit grant

The implicit grant is similar to the [authorization code](auth_code.md) grant,
with two differences: it's used for user-agent-based clients (e.g. single page
applications) that cannot store a secret in a secure way; additionally, the
authorization server returns the access token directly, without the need of an
authorization code.

The client sends the following parameter via a query string argument to the
authorization server:

- `response_type` = token.
- `client_id`, with the client’s ID.
- `redirect_uri`, with the URI to which to redirect the client after completing
  authorization. This parameter is optional; if not provided, however, the user
  will be redirected to a default location.
- `scope`, with a space-delimited list of requested scope permissions.
- `state`, with a Cross-Site Request Forgery (CSRF) token. This parameter is
  optional but highly recommended. You can store the value of CSRF token in the
  user’s session to be validated in the next step.

The user will then be asked to login to the authorization server and approve the
client request. If the user approves the request they will be redirected to the
redirect URI with the following parameters in the query string arguments:

- `token_type` = Bearer.
- `expires_in`, an integer representing the time-to-live (in seconds) of the
  access token.
- `access_token`, the access token represented by a JSON Web Token (JWT) signed
  with the authorization server’s private key.
- `state`, with the CSRF parameter sent in the original request. You can compare
  this value with the one stored in the user’s session.

Refresh tokens are not to be issued for `implicit` grants. This is a security
restriction coming from the OAuth2 specification, [RFC 6749](https://tools.ietf.org/html/rfc6749#page-35).
