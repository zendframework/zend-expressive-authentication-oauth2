# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.0.0alpha3 - 2018-02-27

### Added

- Nothing.

### Changed

- [#17](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/17)
  changes the constructor of each of the `Zend\Expressive\Authentication\OAuth2\OAuth2Adapter`
  and `Zend\Expressive\Authentication\OAuth2\OAuth2Middleware` classes to accept
  a callable `$responseFactory` instead of a `Psr\Http\Message\ResponseInterface` 
  response prototype. The `$responseFactory` should produce a
  `ResponseInterface` implementation when invoked.

- [#17](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/17)
  updates the `OAuth2AdapterFactory` and `OAuth2MiddlewareFactory` classes to no
  longer use `Zend\Expressive\Authentication\ResponsePrototypeTrait`, and
  instead always depend on the `Psr\Http\Message\ResponseInterface` service to
  correctly return a PHP callable capable of producing a `ResponseInterface`
  instance.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.0alpha2 - 2018-02-26

### Added

- [#13](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/13)
  adds `Zend\Expressive\Authentication\OAuth2\Entity\RevokableTrait`, which
  provides a way to flag whether or not a token has been revoked, and mimics
  traits from the upstream league/oauth2-server implementation.

- [#13](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/13)
  adds `Zend\Expressive\Authentication\OAuth2\Entity\TimestampableTrait`, which
  provides methods for setting and retrieving `DateTime` values representing
  creation and update timestamps for a token; it mimics traits from the upstream
  league/oauth2-server implementation.

### Changed

- [#15](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/15)
  updates the repository to pin to zend-expressive-authentication `^1.0.0alpha3`.

- [#13](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/13)
  updates `Zend\Expressive\Authentication\OAuth2\Entity\AccessTokenEntity` to
  use the `RevokableTrait` and `TimestampableTrait`.

- [#13](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/13)
  updates `Zend\Expressive\Authentication\OAuth2\Entity\AuthCodeEntity` to
  use the `RevokableTrait`.

- [#13](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/13)
  updates `Zend\Expressive\Authentication\OAuth2\Entity\RefreshTokenEntity` to
  use the `RevokableTrait`.

- [#13](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/13)
  updates `Zend\Expressive\Authentication\OAuth2\Entity\ClientEntity` to
  use the `RevokableTrait` and `TimestampableTrait`. It also adds methods for
  setting and retrieving the client secret, personal access client, and password
  client.
  

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.0alpha1 - 2018-02-07

### Added

- [#9](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/9)
  adds support for PSR-15.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [#9](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/9) and
  [#5](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/5)
  remove support for http-interop/http-middleware and
  http-interop/http-server-middleware.

### Fixed

- Nothing.

## 0.3.0 - 2018-02-07

### Added

- [#11](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/11)
  adds support for zend-expressive-authentication 0.3.0.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.2.1 - 2017-12-11

### Added

- [#1](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/1)
  adds support for providing configuration for the cryptographic key. This may
  be done by providing any of the following via the `authentication.private_key`
  configuration:

  - A string representing the key.
  - An array with the following key/value pairs:
    - `key_or_path` representing either the key or a path on the filesystem to a key.
    - `pass_phrase` with the pass phrase to use with the key, if needed.
    - `key_permissions_check`, a boolean for indicating whether or not to verify
      permissions of the key file before attempting to load it.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.2.0 - 2017-11-28

### Added

- Adds support for zend-expressive-authentication 0.2.0.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Removes support for zend-expressive-authentication 0.1.0.

### Fixed

- Nothing.

## 0.1.0 - 2017-11-20

Initial release.

### Added

- Everything.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
