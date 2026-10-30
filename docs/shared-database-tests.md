# Shared database compatibility — 30 October 2026

The assigned Angsila database contains an existing Lab `users` table. CLOZEE now supports a validated private `table_prefix` setting, with explicit SQL table slots. Bound user values are never rewritten. `clozee_` is the planned Angsila prefix; the empty default preserves existing local installations. Foreign key constraint names are also prefixed to avoid shared-schema collisions.

Executed locally with PHP 8.5.12 and MariaDB 11.4.9:

| Gate | Actual result |
| --- | --- |
| Default namespace service/DB/concurrency/install guards | 51 PASS, 0 FAIL |
| `clozee_` namespace in the same test DB | 51 PASS, 0 FAIL |
| Default namespace real browser regression | 24 PASS, 0 FAIL |
| `clozee_` real browser regression | 24 PASS, 0 FAIL |
| PHP syntax | All application/public/script/test PHP files passed |

The prefixed schema was installed alongside the original ten test tables. Tests compare unrelated table row counts before and after, reject occupied/empty namespaces, verify unknown table rejection and unchanged bound text, and exercise real transactions, stock concurrency, ownership and chat. These checks establish local compatibility; they do not prove an Angsila deployment. No live writes or uploads occurred.

Evidence: `evidence/shared-db-domain.json`, `evidence/shared-db-default-browser.json`, and `evidence/shared-db-prefix-browser.json`. The existing report/test-results document describes the preceding build; this addendum records the later namespace change. GitHub's new workflow separately tests both namespaces; its latest result must be checked before claiming CI success for this revision.

`database/angsila-schema.sql` is generated from the canonical schema, uses ten `clozee_` tables, and contains no DROP, DELETE or TRUNCATE statements. It is prepared for review only, not executed on Angsila. Set the private application prefix to match before deployment.
