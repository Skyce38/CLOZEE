# CLOZEE — Round 2 Implementation Brief
**Target delivery:** 10 November 2026
**Goal:** A complete, demonstrable PHP/MySQL coursework web application and required submission materials.

## Authoritative references
Read these planning files together:
- `README.md`
- `docs/project-spec.md`
- `docs/functional-requirements.md`
- `docs/use-cases.md`
- `database/schema.sql` (DRAFT — review before execution)
- `docs/project-structure.md`

Read course materials provided separately (if accessible):
- `Database-authen(1).pdf`: p.33 project minimum; p.34 milestones; p.35 report; p.36 submission checklist
- `CRUD-and-VERSION-CONTROL(1).pdf`: PHP/MySQL CRUD, authentication and Git
- `Data-base-join(1).pdf`: SQL JOIN and relationships
- `campus_delivery_homework(1).sql` / `Database_JOIN_Homework_Student(2).docx`: educational JOIN examples only; do not copy their food-delivery data as CLOZEE

## Project summary
CLOZEE is a marketplace for new and secondhand clothing aimed at Burapha University students and people living in Bangsaen. One registered user may buy items and own one shop. Sellers list sizes, prices and stock. Buyers browse and filter listings, order from ONE shop per checkout, choose local pickup or delivery, and see order status. Simple buyer-seller text chat is desirable after the basic flows work. No online payment gateway, rider dispatch, or real-time GPS.

## Required completion gates (P0)
1. **Foundation**: valid runnable PHP application; safe DB config template; schema/seed; documented installation; no hard-coded secrets.
2. **Authentication**: Register/Login/Logout with password hashes and sessions, correct access control.
3. **Shop/product CRUD**: create/edit own shop; create/read/update/deactivate own products and size/price/stock variants.
4. **Buyer flow**: browse/search/filter; product details; choose variant and quantity; checkout one shop; choose pickup/delivery and save details; view purchase history.
5. **Seller flow**: see own-shop orders; update order status with validated transitions; buyer sees updates.
6. **Database**: MySQL/MariaDB relational design with PK/FK/constraints/indexes, accurate demo JOIN queries; seed with clearly fake sample data.
7. **Testing**: run and document tests; permission checks; order transaction/stock integrity; mobile/desktop smoke tests; setup reproducible.
8. **Documentation**: report, ERD, use cases, data dictionary, architecture, screenshots after running app, test results, limitations, README and GitHub link.
9. **Deployment**: host on the BUU Angsila server after verifying exact environment, permissions, PHP and DB versions; preserve credentials privately. Provide a real working URL after verifying it from a browser.

## Secondary features (P1)
- Simple text-only buyer-seller chat scoped to authorized participants.
- Multiple product images.
- Seller dashboard summary.
Implement P1 only after all P0 gates pass; report unfinished P1 honestly.

## Security and integrity
- Use `password_hash`, `password_verify`, `session_regenerate_id(true)`, prepared statements, output escaping and CSRF tokens.
- Every mutation must check owner authorization using the logged-in user, not merely a client-sent ID.
- Validate product condition, quantity, prices, sizes and pickup/delivery details; do not expose database errors to public users.
- Use transactional order placement with stock rechecks and row locks, prevent overselling.
- Preserve order-item name/size/price snapshots for history.
- Deactivate products/shops linked to existing orders; do not destroy order history.
- Avoid unreviewed destructive SQL (DROP, DELETE ALL, production migrations).
- Never read, publish, commit or display user/password spreadsheets or production credentials.

## Work sequence: execute continuously, do not stop for approval after every phase
- Phase A: inspect files and course slides; write `docs/course-requirements.md`; review or amend draft schema; set up PHP project and safe configs.
- Phase B: implement auth and seller/shop/product CRUD; test ownership security.
- Phase C: implement browse/filter, checkout, pickup/delivery, order history and seller statuses; test DB transactions.
- Phase D: implement text chat if P0 complete; polish accessibility, Thai copy and responsive views.
- Phase E: final tests, screenshots, ER diagram and data dictionary, report, README, submission audit. Prepare Angsila deployment package and deployment instructions.
- Phase F: deploy to Angsila ONLY if authorized access and environment are available and the user explicitly approves the target; otherwise mark deployment BLOCKED and provide precise steps/requirements without claiming it is done.

## Conditions for reporting complete
Provide: actual tested routes/screens; schema import result; test logs and failures; verified GitHub repo URL; verified Angsila URL; report location; outstanding risks. If either hosting, real demo, or report is not verified, label status 'NOT READY TO SUBMIT'.

## Do not overreach
- Do not build native mobile apps.
- Do not add payment integrations, maps, rider management or unrelated modules.
- Do not redesign the entire spec without explaining why.
- Do not claim a screenshot, SQL JOIN result, deployed site, or passing test without executing and verifying.
