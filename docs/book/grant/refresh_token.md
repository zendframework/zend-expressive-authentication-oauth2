# Refresh token

The OAuth2 framework provides the ability to _refresh_ the access token,
generating a new one with a new lifetime. This action can be performed using
the `refresh_token` value, if present in the access token response.

To request a token refresh, the client needs to send a `POST` request with
the following parameters:

- `grant_type` = refresh_token.
- `refresh_token` with the refresh token.
- `client_id` with the client’s ID.
- `client_secret` with the client’s secret.
- `scope` with a space-delimited list of requested scope permissions. This is
  optional; if not sent, the original scopes will be used. Otherwise you can
  request a _reduced_ scope; you may never _expand_ scope during a refresh
  operation.

The authorization server responds with a JSON payload as follows:

```json
{
    "token_type" : "Bearer",
    "expires_in" : "3600",
    "refresh_token" : "YWYwNjhmNmZmMDhmZjkyOGJj...",
    "access_token" : "eyJ0eXAiOiJKV1Q..."
}
```

The values are as follows:

- The `token_type` is the type of generated token (here, and generally, Bearer).
- `expires_in` is an integer representing the time-to-live (in seconds) of the
  access token.
- The `refresh_token` a token that can be used to refresh the `access_token`
  when expired.
- The `access_token` contains a JSON Web Token (JWT) signed with the
  authorization server’s private key. This token must be used in the
  `Authorization` request HTTP header on all subsequent requests.
