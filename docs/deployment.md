# CLOZEE Angsila deployment guide

**Status: PREPARED, NOT DEPLOYED.** No Angsila account, server, filesystem or production database has been accessed or modified. The user requires explicit approval before using credentials or changing the live system. Never use Google Sheets account/password data.

## Information required before an approval request

- Exact owner-approved Angsila URL and filesystem target; existing files and rollback owner.
- Permitted upload method (SFTP/SSH or another administrator-approved method); no assumption that SSH is available.
- Actual PHP version (application needs 8.1+), PDO MySQL, mbstring, sessions, HTTPS and cookie configuration.
- Actual MySQL/MariaDB version, utf8mb4/InnoDB and CHECK support, assigned database and permissions. Local tests used MariaDB 11.4.9; Angsila/MySQL has not been verified.
- Webroot mapping, private config location, session writable area, server error log location and backup method. Assigned DBs may not allow CREATE DATABASE.
- Sanitized connection configuration (host/port/database/user names as appropriate); password must be supplied privately through the approved server mechanism, never chat, source or shared sheets.

## Concrete upload mapping

Preferred layout:

```text
private CLOZEE root/                 (outside Apache public area)
  app/                              includes config/local.php, private
  storage/                          private, writable only as needed
  database/                         keep private; do not expose dumps
  public/                           Apache document root / mapped URL
    index.php, products.php, ...
    seller/
    assets/
```

If Angsila exposes a fixed `public_html/~...` directory, upload ONLY the contents of `public/` to an approved subdirectory and place the rest in a private sibling; adapt the relative bootstrap paths once to the verified filesystem mapping. Do not upload the entire repository into a public directory and rely on hidden filenames for privacy. If private placement is impossible, obtain an administrator-approved deny rule and test it before serving the site. The ZIP package is for source transfer/review, not an instruction to extract into webroot.

The app uses direct `.php` routes and does not require URL rewriting, Composer or Node on the server. Set `base_path` to the deployed URL prefix (for example `/~assigned-user/clozee`, as an illustrative path only), with no trailing slash. Set `secure_cookies=true` under HTTPS. Do not trust forwarded-proto headers without an administrator-defined proxy configuration.

## Safe installation sequence after target approval

1. Inventory target files/DB and save an administrator-approved backup. Agree on a rollback. Upload into a new versioned private directory so existing work is preserved.
2. Check PHP extensions and actual DB version using a temporary CLI/approved administrator method. Do not leave phpinfo or diagnostic pages public.
3. Configure `app/config/local.php` outside webroot or `CLOZEE_DB_*` environment variables. Grant the runtime DB user only SELECT/INSERT/UPDATE/DELETE; use a separate authorized setup account for DDL. Never use local root defaults.
4. Select the assigned EMPTY database in phpMyAdmin and review/import `database/schema.sql`. It has no CREATE/DROP DATABASE. It is not an upgrade migration and must not be rerun over existing tables. Alternatively use `php scripts/migrate.php --empty-database` with a DDL-authorized config; it refuses nonempty schemas.
5. Use no seed data for real use. If the instructor authorizes a demo-only deployment, import fictional seed and create private credentials with `scripts/seed.php --demo-only`; keep `storage/demo-accounts.json` non-public and delete/disable demo accounts before real use.
6. Point the approved URL to public files, set base path and HTTPS cookies, and check file permissions. Keep display_errors disabled and server logs private.
7. From a browser, test catalog, registration/login/logout, two-user ownership, shop/product C/R/U/D, stock, pickup/delivery, status and chat using authorized test accounts. Record actual results and screenshots in `deployment-verification.md`.
8. Probe forbidden URLs for config, SQL, storage, docs and tests. Expect 403/404 with no file contents. Verify no password/hash/session ID appears in HTML or logs exported for the report.
9. Verify from a separate unauthenticated browser that the public URL opens. Verify private data stays private after logout. Remove setup/debug artifacts, confirm GitHub revision, update report/checklist only after checks pass.

## Rollback

If schema import fails on a new empty DB, stop and inspect errors privately; do not drop unrelated tables. PHP/MySQL DDL may auto-commit, so a partially imported schema is not automatically rolled back. Ask the administrator how to clear only the newly approved target or allocate another empty DB. Application rollback should switch back to the previous versioned public mapping. Restore data from a reviewed backup only with explicit target-specific approval.

## Approval boundary

Once the actual target and private configuration are available, present the exact file mapping, SQL diff/import plan, backup/rollback and smoke-test plan. Ask permission for that concrete target. Current status remains **NOT READY TO SUBMIT** until this happens and Angsila verification succeeds.
