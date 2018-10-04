# OAuth2 server middleware for Expressive and PSR-7 applications

[![Build Status](https://secure.travis-ci.org/zendframework/zend-expressive-authentication-oauth2.svg?branch=master)](https://secure.travis-ci.org/zendframework/zend-expressive-authentication-oauth2)
[![Coverage Status](https://coveralls.io/repos/github/zendframework/zend-expressive-authentication-oauth2/badge.svg?branch=master)](https://coveralls.io/github/zendframework/zend-expressive-authentication-oauth2?branch=master)

Zend-expressive-authentication-oauth2 is middleware for [Expressive](https://github.com/zendframework/zend-expressive)
and [PSR-7](http://www.php-fig.org/psr/psr-7/) applications providing an OAuth2
server for authentication.

This library uses the [league/oauth2-server](https://oauth2.thephpleague.com/)
package for implementing the OAuth2 server. It supports all the following grant
types:

- client credentials;
- password;
- authorization code;
- implicit;
- refresh token;

## Installation

You can install the *zend-expressive-authentication-oauth2* library with
composer:

```bash
$ composer require zendframework/zend-expressive-authentication-oauth2
```

## Documentation

Documentation is [in the doc tree](doc/book/), and can be compiled using [mkdocs](http://www.mkdocs.org):

```bash
$ mkdocs build
```

You may also [browse the documentation online](https://docs.zendframework.com/zend-expressive-authentication-oauth2/).
