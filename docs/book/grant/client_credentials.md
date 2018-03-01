# Client credentials

The client credentials grant is used in machine-to-machine scenarios, for
example a client making API requests that do not require user’s permission.

The client sends a POST request with following body parameters to the
authorization server:

- `grant_type` = client_credentials;
- `client_id` with the client’s ID;
- `client_secret` with the client’s secret;
- `scope` with a space-delimited list of requested scope permissions.

The authorization server responds with a JSON as follows:

```json
{
    "token_type" : "Bearer",
    "expires_in" : "3600",
    "access_token" : "eyJ0eXAiOiJKV1Q..."
}
```

The `token_type` is the type of generated token (Bearer). The `expires_in` is
an integer representing the TTL (in seconds) of the access token.
The `access_token` contains the a `JWT` signed with the authorization server’s
private key. This token must be used in the `Authorization` request HTTP header.
