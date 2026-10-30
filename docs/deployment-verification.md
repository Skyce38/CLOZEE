# Deployment verification record

| Check | Result |
| --- | --- |
| Target Angsila URL/path supplied | Existing webroot verified; new CLOZEE subdirectory proposed, pending approval |
| Specific target approved | NOT APPROVED / NOT ATTEMPTED |
| PHP/DB versions confirmed | Portal: PHP 8.4.27, MySQL 8.0.46-0ubuntu0.24.04.4, Apache; app webspace runtime / PDO MySQL NOT VERIFIED |
| Files uploaded / production DB changed | NO |
| HTTPS and private config protection | NOT VERIFIED |
| CRUD/Auth/stock/status/chat on Angsila | NOT TESTED |
| External unauthenticated browser reachability | NOT TESTED |
| Rollback/backup verified | NOT VERIFIED |
| Final submission readiness | NOT READY TO SUBMIT |

Local runtime and browser results do not substitute for these checks. See `deployment.md` for the exact prerequisites and safe sequence.

## Read-only inspection — 30 October 2026

The user signed in to phpMyAdmin personally using the university VPN. The assigned database is `s68160207`. Existing InnoDB Lab tables are `education`, `profile`, `skill`, `training`, and `users`; their row counts were 3, 1, 5, 2, and 0 respectively. Only metadata, row counts and server settings were inspected; no record contents or passwords were collected. Database-scoped privileges were confirmed with `SHOW GRANTS`.

The existing `users` name conflicts with CLOZEE. The deployment plan uses private configuration `table_prefix = clozee_` to create ten separate application tables and preserve the Lab tables. No live DDL, seed or upload has been performed.

FileZilla read-only inspection confirmed SFTP port 22, home `/home/BUU/68160207`, and webroot `/home/BUU/68160207/public_html`. The webroot contains six existing Lab PHP files and `assets`/`config` directories. A browser opened the existing Lab successfully at `https://angsila.informatics.buu.ac.th/~68160207/`. No CLOZEE folder was present in the inspected home or webroot listings.

Proposed new targets: public files `/home/BUU/68160207/public_html/clozee`, private files `/home/BUU/68160207/clozee-private`, and public URL `https://angsila.informatics.buu.ac.th/~68160207/clozee/`. These new targets are not created or approved yet. See `angsila-install-plan.md` for the concrete approval scope.
