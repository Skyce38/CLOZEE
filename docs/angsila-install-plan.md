# Angsila installation plan — approved, in progress

30 October 2026. The user approved the exact targets with “ทำเลย” after reviewing the plan. Follow `deployment-verification.md` for actual progress; approval alone does not mean installation or tests passed.

| Component | Exact proposed target |
| --- | --- |
| Public PHP entry points/assets | `/home/BUU/68160207/public_html/clozee/` |
| Private application/configuration/storage | `/home/BUU/68160207/clozee-private/` |
| Website URL | `https://angsila.informatics.buu.ac.th/~68160207/clozee/` |
| Database | Existing assigned `s68160207`, ten new tables prefixed `clozee_` |
| Database schema for review | `database/angsila-schema.sql` |
| Transfer method | User-authenticated FileZilla SFTP session, port 22 |

The home/webroot and existing Lab URL are verified. The CLOZEE targets are new proposed directories, absent from the inspected listings. Existing Lab files and five Lab tables remain in place. No DROP/DELETE/TRUNCATE statements occur in the new schema. No production demo seed is planned.

## Prepared split layout

Run `python scripts/stage-angsila.py --private-root /home/BUU/68160207/clozee-private --output output/angsila-approved-layout` locally. This copies only tracked application/public files, excludes local configuration and credentials, and adapts seventeen public PHP entry points to the exact private bootstrap path. It refuses to overwrite an existing output directory. Upload `web/` contents into the new public target and `private/` contents into the new private target. Never upload the entire staging parent or local test preview.

Configuration is created privately with host `localhost`, database/user `s68160207`, prefix `clozee_`, base path `/~68160207/clozee`, secure cookies enabled, and delivery fee `30.00`. The user must supply the actual database password privately, without a Google Sheet or chat. Do not copy local development configuration. Never publish credentials or a phpinfo page.

## Execution after explicit target approval

1. Recheck target listings and schema names immediately before writing. Stop if a target or any `clozee_` table unexpectedly exists; do not overwrite.
2. Create the two new directories, transfer only the prepared public/private files, and verify the transfer queue completed. Keep existing Lab files unchanged.
3. Import the reviewed schema into `s68160207` through the user-authenticated phpMyAdmin session. MySQL DDL may partially commit: stop on any error and inspect, without dropping tables or retrying blindly.
4. Have the user complete the private database configuration. Check actual webspace PHP/PDO/session access, then test catalog and registration. Never expose a credentials form or diagnostic dump.
5. With approved fictional test accounts, verify login/logout, ownership, shop/product CRUD, pickup/delivery, transaction/stock/status and chat on the actual URL. Record real results; existing local/CI passes do not count as live verification.
6. Check forbidden URLs for private config, SQL, logs, storage and tests; verify HTTPS/session cookies, mobile layout and that the existing Lab URL still opens. Test separate-browser reachability; VPN-dependent access must be disclosed.
7. Update the deployment record and report only with observed results. Deployment is incomplete if any acceptance check fails.

## Preservation / rollback

No existing file or table is replaced by this installation. If it fails, stop and leave the Lab unchanged. Disabling/renaming only the newly created public CLOZEE directory can take it offline; do not drop tables or restore/delete unrelated data. Any destructive cleanup requires a separate concrete review. Full database export is not required merely to add a separate namespace, and no old Lab records need to be copied into CLOZEE.

## Validation before requesting approval

The default and shared namespaces each passed 51 domain/database/concurrency tests and 24 browser checks locally. The split private/public layout also passed all 24 browser checks on a local PHP server. GitHub Actions run 41064936461 succeeded for commit `f3342a0469b4145ea618547696fa1e0d33fc4a19`, including both namespace suites and browser acceptance. Server runtime restrictions, private-file access and live behavior remain to be verified after approval.
