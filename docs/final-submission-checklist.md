# CLOZEE Final Submission Audit — 10 November 2026

Audit date: 5 November 2026 (documents only; live site last verified 4 November 2026). **Technical core deployed and verified; final submission metadata remains incomplete.** Angsila was tested directly via university VPN; off-VPN reachability and backup restoration remain unverified. Fill report-cover names and confirm final submission/reservation details before declaring the complete submission ready.

## Lecturer requirements (Database-authen.pdf pp.33–36)

- [x] Problem Statement, objectives, target audience and hypothesized Pain Point in report.
- [x] Requirements, Use Cases and actual Use Case Diagram.
- [x] ER Diagram, reviewed schema and 89-field Data Dictionary.
- [x] PHP relational DB CRUD tested locally and on MySQL CI; physical DELETE for unreferenced products.
- [x] Login/Logout, session, basic authorization and ownership tested.
- [x] GitHub repository/source/README/history prepared at https://github.com/Skyce38/CLOZEE (verify latest pushed revision below before handoff).
- [x] Report includes architecture, actual app screenshots, CRUD/auth/security, tests and limitations.
- [x] Angsila website deployed after exact-target approval and independently browser-verified through university VPN: https://angsila.informatics.buu.ac.th/~68160207/clozee/
- [ ] Final cover: author's full name, other members/solo confirmation and instructor name — explicit placeholders remain. The official slides do not state group or solo; only the student's exam-review summary says "งานกลุ่ม", so confirm with the instructor.
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
- [x] Seller upload of up to 4 product images — deployed on Angsila, see the product-photo update below.
- [ ] Sales analytics — deferred optional P1, not claimed complete.

## SQL / reproducibility / privacy

- [x] Real INNER JOIN and LEFT JOIN results saved; zero-order shop retained.
- [x] PK/FK/unique/CHECK/indexes visible in schema/dictionary/ERD; business rules also validated in PHP.
- [x] Empty-schema installer and demo seeder guards tested; no automatic destructive reset.
- [x] Fictional example.test seed; generated demo passwords and config excluded from Git/source packages.
- [x] README install commands reproduced by fresh GitHub CI; DB/browser/concurrency suites executed.
- [x] No Google Sheets credentials used, read or published. No real-user test data or session cookies exported.

## Actual results and remaining proof

Current local suite: 57 domain/DB/concurrency/install-guard tests per table namespace, plus 23 image-validation, 24 regression browser, 23 photo browser and 14 migration tests. CI passed 198 tests per runtime. Angsila passed 30 core functional checkpoints (30 October), 13 photo browser checks and 17 anonymous HTTP/security checks (4 November); a fresh browser independently captured desktop/mobile views. Physical DELETE and concurrent races were tested in isolated local/MySQL CI databases, not repeated on live. Exact times, scope and cases are in test-results.md and evidence JSON. Final report PDF is CLOZEE-Final-Report-Round2.pdf, with HTML/Markdown source. Screenshots show actual PHP pages; design/concept.png is not test proof.

Before final submission: fill report metadata, confirm the final submission/reservation details and recheck the live URL via VPN. Do not export private configuration/passwords or claim unperformed penetration/load/restore/off-VPN tests. Actual live checks, rather than localhost or CI, support the checked Angsila gate above.

## Product-photo update

- [x] BLOB images deployed on Angsila without GD or writable private uploads.
- [x] Live photo flows 13/13; HTTP/security 17/17; 47/47 file hashes.
- [x] Backed up before additive SQL; preserved original data and Labs.
- [x] Test shops/products closed; temporary installers moved outside webroot.
- [ ] Database quota and backup restore rehearsal remain unverified.
