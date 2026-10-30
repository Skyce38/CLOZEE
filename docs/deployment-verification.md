# Deployment verification record

| Check | Result |
| --- | --- |
| Target Angsila URL/path supplied | Existing webroot verified; new CLOZEE subdirectory proposed, pending approval |
| Specific target approved | APPROVED by user: “ทำเลย”, after review of exact targets and SQL plan |
| PHP/DB versions confirmed | Portal: PHP 8.4.27, MySQL 8.0.46-0ubuntu0.24.04.4, Apache; app webspace runtime / PDO MySQL NOT VERIFIED |
| Files uploaded / production DB changed | 25 public files + 24 private code files uploaded; 10 new clozee_ tables created. Private DB configuration pending user entry |
| HTTPS and private config protection | NOT VERIFIED |
| CRUD/Auth/stock/status/chat on Angsila | NOT TESTED |
| External unauthenticated browser reachability | NOT TESTED |
| Rollback/backup verified | NOT VERIFIED |
| Final submission readiness | NOT READY TO SUBMIT |

Local runtime and browser results do not substitute for these checks. See `deployment.md` for the exact prerequisites and safe sequence.

## Read-only inspection — 30 October 2026

The user signed in to phpMyAdmin personally using the university VPN. The assigned database is `s68160207`. Existing InnoDB Lab tables are `education`, `profile`, `skill`, `training`, and `users`; their row counts were 3, 1, 5, 2, and 0 respectively. Only metadata, row counts and server settings were inspected; no record contents or passwords were collected. Database-scoped privileges were confirmed with `SHOW GRANTS`.

The existing `users` name conflicts with CLOZEE. The deployment uses private configuration `table_prefix = clozee_` to isolate ten application tables and preserve the Lab tables. The initial inspection was read-only; the later approved installation is recorded below. No production demo seed was run.

FileZilla read-only inspection confirmed SFTP port 22, home `/home/BUU/68160207`, and webroot `/home/BUU/68160207/public_html`. The webroot contains six existing Lab PHP files and `assets`/`config` directories. A browser opened the existing Lab successfully at `https://angsila.informatics.buu.ac.th/~68160207/`. No CLOZEE folder was present in the inspected home or webroot listings.

Approved new targets: public files `/home/BUU/68160207/public_html/clozee`, private files `/home/BUU/68160207/clozee-private`, and public URL `https://angsila.informatics.buu.ac.th/~68160207/clozee/`. The two directories were created after approval. See `angsila-install-plan.md` for the concrete approval scope.

## Installation progress — 30 October 2026

- FileZilla completed 25 public-file transfers, then 24 private-code transfers. The new private `app` and `storage` directories are present. No existing Lab file was overwritten. Only staged source from commit `d2dea676d63e7d56454c54b7bedda7a65e5c9e18` was selected; local test configuration was excluded.
- The Chrome extension could not upload the SQL file because file-URL access was disabled. The approved schema was entered as equivalent SQL through phpMyAdmin's database SQL editor instead; no extension permission was changed. All ten CREATE TABLE statements succeeded. MySQL's integer display-width deprecation warning was non-fatal.
- The live structure lists 15 tables: ten new empty `clozee_` tables plus the original five Lab tables. The original row counts remain 3 / 1 / 5 / 2 / 0.
- Explicitly scoped information_schema queries confirmed 10 application tables, 75 columns, 14 foreign keys, and 5 enforced CHECK constraints in `s68160207`. phpMyAdmin may switch its connection context to information_schema during metadata queries, so the verification used an explicit schema name rather than DATABASE().
- `https://angsila.informatics.buu.ac.th/~68160207/clozee/login.php` renders PHP and can load the private bootstrap. Base-path styling/navigation and database login are pending private configuration; this is not an application acceptance pass.
- A separate ignored local folder `output/angsila-private-config` holds non-secret `local.php` configuration and a user-edited `db-password.txt`. The configuration reads the password file outside public_html. The assistant must not read or log its contents. User entry/upload is still pending.
