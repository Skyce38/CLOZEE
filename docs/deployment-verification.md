# Deployment verification record

| Check | Result |
| --- | --- |
| Target Angsila URL/path supplied | Verified and installed at https://angsila.informatics.buu.ac.th/~68160207/clozee/ |
| Specific target approved | APPROVED by user: “ทำเลย”, after review of exact targets and SQL plan |
| PHP/DB versions confirmed | Portal PHP 8.4.27 / Apache / MySQL 8.0.46; actual app PDO connection, session and private bootstrap work. App-specific PHP version not separately exposed |
| Files uploaded / production DB changed | 25 public files + 24 private code files + 2 private configuration files; 10 new clozee_ tables. No existing Lab file/table replaced |
| HTTPS and private config protection | PASS: Secure/HttpOnly/SameSite=Lax, no-store, CSP/frame/nosniff, private-path HTTP checks |
| CRUD/Auth/stock/status/chat on Angsila | 30 real functional checkpoints PASS; physical DELETE/concurrency races not repeated on live (covered by isolated local/CI suites) |
| Fresh unauthenticated browser reachability | PASS desktop/mobile from a fresh Chrome context via university VPN. Off-VPN access NOT VERIFIED |
| Rollback/backup verified | New-directory isolation verified; actual backup restoration NOT TESTED |
| Final submission readiness | Technical core deployed/verified; submission details pending |

Latest release: `cd08f6a` (its CI run shows the earlier hash `d6999f3`; only the commit message changed), installed 6 November 2026; see the section at the end of this file. Local runtime and browser results do not substitute for these checks. See `deployment.md` for the exact prerequisites and safe sequence.

## Read-only inspection — 30 October 2026

The user signed in to phpMyAdmin personally using the university VPN. The assigned database is `s68160207`. Existing InnoDB Lab tables are `education`, `profile`, `skill`, `training`, and `users`; their row counts were 3, 1, 5, 2, and 0 respectively. Only metadata, row counts and server settings were inspected; no record contents or passwords were collected. Database-scoped privileges were confirmed with `SHOW GRANTS`.

The existing `users` name conflicts with CLOZEE. The deployment uses private configuration `table_prefix = clozee_` to isolate ten application tables and preserve the Lab tables. The initial inspection was read-only; the later approved installation is recorded below. No production demo seed was run.

FileZilla read-only inspection confirmed SFTP port 22, home `/home/BUU/68160207`, and webroot `/home/BUU/68160207/public_html`. The webroot contains six existing Lab PHP files and `assets`/`config` directories. A browser opened the existing Lab successfully at `https://angsila.informatics.buu.ac.th/~68160207/`. No CLOZEE folder was present in the inspected home or webroot listings.

Approved new targets: public files `/home/BUU/68160207/public_html/clozee`, private files `/home/BUU/68160207/clozee-private`, and public URL `https://angsila.informatics.buu.ac.th/~68160207/clozee/`. The two directories were created after approval. See `angsila-install-plan.md` for the concrete approval scope.

## Installation progress — 30 October 2026

- FileZilla completed 25 public-file transfers, then 24 private-code transfers. The new private `app` and `storage` directories are present. No existing Lab file was overwritten. Only staged source from commit `d2dea676d63e7d56454c54b7bedda7a65e5c9e18` was selected; local test configuration was excluded.
- The Chrome extension could not upload the SQL file because file-URL access was disabled. The approved schema was entered as equivalent SQL through phpMyAdmin's database SQL editor instead; no extension permission was changed. All ten CREATE TABLE statements succeeded. MySQL's integer display-width deprecation warning was non-fatal.
- Immediately after schema creation, the live structure listed 15 tables: ten new empty `clozee_` tables plus the original five Lab tables. The original row counts remained 3 / 1 / 5 / 2 / 0. Later live QA created only fictional CLOZEE records.
- Explicitly scoped information_schema queries confirmed 10 application tables, 75 columns, 14 foreign keys, and 5 enforced CHECK constraints in `s68160207`. phpMyAdmin may switch its connection context to information_schema during metadata queries, so the verification used an explicit schema name rather than DATABASE().
- The initial login page rendered the private bootstrap before configuration. After configuration, catalog, styled navigation and actual PDO/MySQL operations worked.
- The user personally filled `output/angsila-private-config/db-password.txt`, saved/closed it and confirmed completion. Only that file and the non-secret `local.php` reader were uploaded to `/home/BUU/68160207/clozee-private/app/config/`. No password contents were read, logged or committed. These files remain outside public_html and Git/source packages.

## Completed live checks

`evidence/angsila-functional-results.json` records 30 real browser checkpoints: separate account registration/login/logout, wrong password, own shop/product/variants, edit/deactivate/reactivate, cart, pickup checkout, delivery fee, stock deduction/restoration, both complete status sequences, historical snapshots, chat/reply/escaped text, unrelated-user denial, search/filter and mobile layout.

Orders #1 and #3 reached completed via pickup and delivery respectively; order #2 was cancelled and M stock returned from 2 to 3. Product M price later changed from 290 to 295, while order #1 retained its original name/price/total. Three fictional QA accounts, two QA shops (one closed), one demonstration product and three QA orders remain explicitly labelled as test data.

`evidence/angsila-http-results.json` records 17 anonymous HTTP/security checks, including the existing Lab homepage, private paths and CSRF. `evidence/angsila-visual-results.json` and `angsila-catalog-desktop.png` / `angsila-catalog-mobile.png` come from an independent fresh Chrome context with no saved user session, using regular Playwright because the Browser plugin was not available. Manual authenticated flows used CUA/Chrome.

No actual payment, courier delivery, penetration/load test, live concurrency race, live physical DELETE, backup restoration or off-VPN access is claimed. Technical core acceptance passed within the verified scope. Final submission/reservation details still require confirmation.


## 4 November 2026: database product photos

Approved migration and versioned private release installed. Schema is now 11 tables / 89 columns / 15 foreign keys / 9 checks; original row values and Lab counts preserved during migration. All 47 deployed file hashes match. Browser 13/13 and HTTP/security 17/17 passed. See [photo deployment](database-product-photos.md) and [sanitized evidence](evidence/database-product-photos/angsila-deployment.json). Earlier checkpoints below/above describe the original core release.

## 6 November 2026: Thai interface, floating chat and shop deletion

The student asked for this upload to the existing approved Angsila targets. Commit `cd08f6a` passed CI run 42103903945 under its earlier hash `d6999f3`. No SQL or database change. Private code was uploaded with FileZilla to the new directory `/home/BUU/68160207/clozee-thaichat-d6999f3`, using a bridge to the existing private configuration, then public files were overwritten in `public_html/clozee`.

The first web upload had a wrong bootstrap path produced by Git Bash path conversion and returned HTTP 500 for about two minutes (roughly 03:47 to 03:49 local time). The previous web files were restored, the package was rebuilt from PowerShell, its paths were checked and the web files were uploaded again. The database and Lab files were not touched.

After installation: HTTP/security 17/17, static files 8/8 matched the package, anonymous chat API returned 401, private paths returned 404 and a fresh browser captured the pages. The student then tested notifications, chat without refresh, replies, chat sorting and shop deletion with two accounts and reported that all passed. Previous web files remain in the ignored local release folder for rollback.
