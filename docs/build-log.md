# Round 2 build phases

30 October 2026. Preserve original planning documents and schema in `reference/`. Inputs: CLOZEE-round2-kit.zip plus the separately supplied course materials. Git commits record each implementation/testing milestone.

| Phase | Work and executed gate | Status |
| --- | --- | --- |
| A Foundation | Imported planning without overwriting earlier review; initialized Git; reviewed schema; PHP lint; isolated MariaDB schema import and seed; doctor confirmed extensions/connection | PASS locally |
| B Auth and seller | Session/password/CSRF, own shop, product+variant CRUD, hard-delete/deactivate rules; real database ownership and browser form tests | PASS locally and MySQL CI |
| C Buyer/orders | Search/cart/single-shop pickup+delivery checkout; row locks, idempotency, snapshots; seller state machine and restock; multiprocess concurrency and browser tests | PASS locally and MySQL CI |
| D Chat/UI | Text chat after local core gates; participant checks, escaped content, desktop/mobile styling and image-load checks | PASS locally and MySQL CI |
| E Submission materials | Actual schema Data Dictionary, ER/UC/architecture, JOIN evidence, README/report, PDF render review, sanitized test evidence and source packaging | Completed; cover placeholders disclosed |
| F Angsila | User explicitly instructed no real server access until target/environment/permissions confirmed | NOT ATTEMPTED; deployment guide prepared |

Local result counts are in test-results.md and JSON evidence, rather than assuming every listed manual test was executed. GitHub CI verifies an exact revision; docs-only changes do not rerun the unchanged code suite. Overall final-coursework status is NOT READY TO SUBMIT until Angsila is deployed/verified and required final metadata/links are complete. No actual credentials or Google Sheets passwords were read or used.
