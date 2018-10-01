# Password

This use case can be used to authenticate an API with user's password grant.
The typical scenario includes a Login web page with username and password that
is used to authenticate against a first-party API. Password grant is only
appropriate for **trusted clients**. If you build your own website as a client
of your API, then this is a great way to handle logging in.

The client sends a POST request with following parameters:

- `grant_type` = password;
- `client_id` with the client’s ID;
- `client_secret` with the client’s secret;
- `scope` with a space-delimited list of requested scope permissions;
- `username` with the user’s username;
- `password` with the user’s password.

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
The `access_token` contains a `JWT` signed with the authorization server’s
private key. This token must be used in the `Authorization` request HTTP header.
