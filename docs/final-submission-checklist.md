# CLOZEE Final Submission Audit — 10 November 2026

Audit date: 30 October 2026. **NOT READY TO SUBMIT** while Angsila and cover metadata remain incomplete. Local deliverable is implemented/tested; this audit does not equate local success with the lecturer's final hosting requirement.

## Lecturer requirements (Database-authen.pdf pp.33–36)

- [x] Problem Statement, objectives, target audience and hypothesized Pain Point in report.
- [x] Requirements, Use Cases and actual Use Case Diagram.
- [x] ER Diagram, reviewed schema and 75-field Data Dictionary.
- [x] PHP relational DB CRUD tested locally and on MySQL CI; physical DELETE for unreferenced products.
- [x] Login/Logout, session, basic authorization and ownership tested.
- [x] GitHub repository/source/README/history prepared at https://github.com/Skyce38/CLOZEE (verify latest pushed revision below before handoff).
- [x] Report includes architecture, actual app screenshots, CRUD/auth/security, tests and limitations.
- [ ] Angsila website deployed and independently browser-verified — target/runtime/permissions not yet known; user instructed no live access.
- [ ] Final cover: author's full name, other members/solo confirmation and instructor name — explicit placeholders remain.
- [ ] Final Angsila URL and round-2 submission/reservation details confirmed by author/instructor.

Registration and detailed JOIN evidence below are CLOZEE kit requirements; do not mislabel every kit item as an explicit p.33 lecturer instruction. No final-score rubric or minimum table/JOIN/commit count was found in the slides.

## CLOZEE demonstrated flows

- [x] New buyer/seller registration and login; wrong login, duplicate email uniqueness, session guard/logout.
- [x] Seller creates/edits own shop and new/preloved clothing product with multiple sizes/prices/stock.
- [x] Buyer browses/searches/filters, views product, uses a single-shop cart.
- [x] Pickup and local delivery save details and calculate fee/total at server.
- [x] Atomic order/stock creation, no overselling in two-process race, idempotent retry.
- [x] Stale seller stock update rejected; historical snapshots preserved after edits.
- [x] Seller transitions/status history; buyer sees updates; cancellation returns stock once.
- [x] Another user cannot edit/delete product, read order or change status.
- [x] Participant-only text chat, seller reply, unrelated-user denial and escaped HTML text.
- [x] Desktop/mobile screenshots and asset/console/page health checks.
- [ ] Customer upload/multiple images and sales analytics — deferred optional P1, not claimed complete.

## SQL / reproducibility / privacy

- [x] Real INNER JOIN and LEFT JOIN results saved; zero-order shop retained.
- [x] PK/FK/unique/CHECK/indexes visible in schema/dictionary/ERD; business rules also validated in PHP.
- [x] Empty-schema installer and demo seeder guards tested; no automatic destructive reset.
- [x] Fictional example.test seed; generated demo passwords and config excluded from Git/source packages.
- [x] README install commands reproduced by fresh GitHub CI; DB/browser/concurrency suites executed.
- [x] No Google Sheets credentials used, read or published. No real-user test data or session cookies exported.

## Actual results and remaining proof

Local suite: 47 domain/DB/concurrency/install-guard tests and 24 browser tests passed; exact times and cases are in test-results.md and evidence JSON. Verified CI revision/run is recorded in evidence/ci-verification.json; check the latest code run after push. Final report PDF is CLOZEE-Final-Report-Round2.pdf, with HTML/Markdown source. Screenshots show the running PHP app; design/concept.png is not test proof.

Before final submission: fill metadata, obtain approved Angsila target, review private upload/DB/rollback plan, obtain explicit deployment approval, deploy and run target tests. Then replace NOT TESTED markers with actual evidence. Do not tick the Angsila gate based on localhost or CI.
