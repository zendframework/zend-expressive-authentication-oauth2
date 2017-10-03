CREATE TABLE oauth_scopes (
    id VARCHAR(40) NOT NULL,
    description VARCHAR(255) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    PRIMARY KEY (id)
);

CREATE TABLE oauth_grants (
    id VARCHAR(40) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    PRIMARY KEY (id)
);

CREATE TABLE oauth_grant_scopes (
    id INTEGER AUTOINCREMENT,
    grant_id VARCHAR(40) NOT NULL,
    scope_id VARCHAR(40) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (grant_id) REFERENCES oauth_grants(id)
    ON DELETE CASCADE,
    FOREIGN KEY (scope_id) REFERENCES oauth_scopes(id)
    ON DELETE CASCADE
);
CREATE INDEX idx1_oauth_grant_scopes ON oauth_grant_scopes(grant_id);
CREATE INDEX idx2_oauth_grant_scopes ON oauth_grant_scopes(scope_id);

CREATE TABLE oauth_clients (
    id VARCHAR(40) NOT NULL,
    secret VARCHAR(40) NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE (id, secret)
);

CREATE TABLE oauth_client_endpoints (
    id INTEGER AUTOINCREMENT,
    client_id VARCHAR(40) NOT NULL,
    redirect_uri VARCHAR(255) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE (client_id, redirect_uri),
    FOREIGN KEY (client_id) REFERENCES oauth_clients(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

CREATE TABLE oauth_client_scopes (
    id INTEGER AUTOINCREMENT,
    client_id VARCHAR(40) NOT NULL,
    scope_id VARCHAR(40) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES oauth_clients(id)
    ON DELETE CASCADE,
    FOREIGN KEY (scope_id) REFERENCES oauth_scopes(id)
    ON DELETE CASCADE
);
CREATE INDEX idx1_oauth_client_scopes ON oauth_client_scopes(client_id);
CREATE INDEX idx2_oauth_client_scopes ON oauth_client_scopes(scope_id);

CREATE TABLE oauth_client_grants (
    id INTEGER AUTOINCREMENT,
    client_id VARCHAR(40) NOT NULL,
    grant_id VARCHAR(40) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES oauth_clients(id)
    ON DELETE CASCADE,
    FOREIGN KEY (grant_id) REFERENCES oauth_grants(id)
    ON DELETE CASCADE
);
CREATE INDEX idx1_oauth_client_grants ON oauth_client_grants(client_id);
CREATE INDEX idx2_oauth_client_grants ON oauth_client_grants(grant_id);

CREATE TABLE oauth_sessions (
    id INTEGER AUTOINCREMENT,
    client_id VARCHAR(40) NOT NULL,
    owner_type VARCHAR(6) NOT NULL DEFAULT ('user'),
    owner_id VARCHAR(255) NOT NULL,
    client_redirect_uri VARCHAR(255) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES oauth_clients(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);
CREATE INDEX idx1_oauth_sessions ON oauth_sessions(client_id, owner_type, owner_id);

CREATE TABLE oauth_session_scopes (
    id INTEGER AUTOINCREMENT,
    session_id INTEGER NOT NULL,
    scope_id VARCHAR(40) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES oauth_sessions(id)
    ON DELETE CASCADE,
    FOREIGN KEY (scope_id) REFERENCES oauth_scopes(id)
    ON DELETE CASCADE
);
CREATE INDEX idx1_oauth_session_scopes ON oauth_session_scopes(session_id);
CREATE INDEX idx2_oauth_session_scopes ON oauth_session_scopes(scope_id);

CREATE TABLE oauth_auth_codes (
    id VARCHAR(40) NOT NULL,
    session_id INTEGER NOT NULL,
    redirect_uri VARCHAR(255) NOT NULL,
    expire_time INTEGER,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (session_id) REFERENCES oauth_sessions(id)
    ON DELETE CASCADE,
);
CREATE INDEX idx1_oauth_auth_codes ON oauth_auth_codes(session_id);

CREATE TABLE oauth_auth_code_scopes (
    id VARCHAR(40) NOT NULL,
    auth_code_id VARCHAR(40) NOT NULL,
    scope_id VARCHAR(40) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (auth_code_id) REFERENCES oauth_auth_codes(id)
    ON DELETE CASCADE,
    FOREIGN KEY (scope_id) REFERENCES oauth_scopes(id)
    ON DELETE CASCADE
);
CREATE INDEX idx1_oauth_auth_code_scopes ON oauth_auth_code_scopes(auth_code_id);
CREATE INDEX idx2_oauth_auth_code_scopes ON oauth_auth_code_scopes(scope_id);

CREATE TABLE oauth_access_tokens (
    id VARCHAR(40) NOT NULL,
    session_id INTEGER NOT NULL,
    expire_time INTEGER,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE (id, session_id),
    FOREIGN KEY (session_id) REFERENCES oauth_sessions(id)
    ON DELETE CASCADE
);
CREATE INDEX idx1_oauth_access_tokens ON oauth_access_tokens(session_id);

CREATE TABLE oauth_access_token_scopes (
    id INTEGER AUTOINCREMENT,
    access_token_id VARCHAR(40) NOT NULL,
    scope_id VARCHAR(40) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (access_token_id) REFERENCES oauth_access_tokens(id)
    ON DELETE CASCADE,
    FOREIGN KEY (scope_id) REFERENCES oauth_scopes(id)
    ON DELETE CASCADE
);
CREATE INDEX idx1_oauth_access_token_scopes ON oauth_access_token_scopes(access_token_id);
CREATE INDEX idx2_oauth_access_token_scopes ON oauth_access_token_scopes(scope_id);

CREATE TABLE oauth_refresh_tokens (
    id VARCHAR(40) NOT NULL,
    access_token_id VARCHAR(40) NOT NULL,
    expire_time INTEGER,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    PRIMARY KEY (access_token_id),
    UNIQUE (id),
    FOREIGN KEY (access_token_id) REFERENCES oauth_access_tokens(id)
    ON DELETE CASCADE
);
