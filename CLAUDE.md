# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Passwordless email-based authentication service for andrewcromar.org. Users log in by requesting a one-time code sent to their email, then exchange it for a session token stored as a cookie. The session cookie is set on `.andrewcromar.org` (production) so it works across subdomains.

## Architecture

**Runtime:** PHP on XAMPP (Apache + MySQL), no framework, no package manager.

**Two-layer structure:**
- `public/` — Web-accessible document root (HTML pages, JS, CSS, API endpoints)
- `backend/` — Server-only PHP (not directly web-accessible)

**Backend bootstrap chain:** `backend/bootstrap.php` loads `config/global.php` (DB credentials + `$live` flag), then all function files. Every API endpoint starts with `require_once '../../backend/bootstrap.php'`.

**Key global:** `$live` (bool) — controls dev vs production behavior. When false: emails are skipped and login codes are returned in API responses; cookies are set without `secure` flag and without domain restriction.

**Auth flow:**
1. `POST /api/request_code.php` — takes email, creates hashed code in `login_codes` table, emails it (or returns it in dev mode)
2. `POST /api/verify_code.php` — takes email + code, validates against DB, marks code used, calls `create_session()`
3. `GET /api/validate_session.php` — checks `session_token` cookie, returns user info
4. `GET /api/logout.php` — revokes session

**Cross-site auth middleware:** `backend/middleware/require_auth.php` can be included by other subdomains to gate pages behind auth. Redirects unauthenticated users to the login page with a `redirect` param.

**Database:** MySQL `auth` database with three tables: `users`, `login_codes`, `sessions`. Schema in `backend/database/schema.sql`. Tokens and codes are stored as SHA-256 hashes.

## Development

**Local setup:** Requires XAMPP with Apache and MySQL running. The `config/global.php` in the repo is a dev stub (`$live = false`); the production server has a different version.

**No build step, no tests, no linter.** Just edit files and reload in browser.

**Deployment:** Push to `main` triggers GitHub Actions workflow that SSHes into the production server and runs `git pull`. The production `config/global.php` is not tracked in git.

## Conventions

- API endpoints return JSON via `json_response()` helper
- DB access uses `mysqli` prepared statements via singleton `get_db()`
- `public/dev/` is restricted to local access only (`.htaccess: Require local`)
- Frontend JS files in `public/scripts/` correspond to their HTML pages in `public/pages/`
- CSS is split into reusable partials in `public/styles/` (root, form, content, center-center, spinner)
