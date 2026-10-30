# CLOZEE — Final Submission Checklist (10 November 2026)

## From course slides (Database-authen PDF, pp.33–36)
- [ ] Problem Statement, target audience, and Pain Point are in the report.
- [ ] Requirements and Use Case Diagram are complete.
- [ ] ER Diagram, database schema and Data Dictionary are complete.
- [ ] PHP + MySQL application demonstrates real database CRUD.
- [ ] Register, Login, Logout, sessions and user access rules are tested.
- [ ] GitHub repo contains current source code and README with meaningful commit history.
- [ ] Website runs on BUU Angsila server at a verified URL.
- [ ] Report includes architecture, app screenshots, security, testing and limitations.
- [ ] Final round submission links are ready for the instructor.

## CLOZEE demo flow
- [ ] New user registers and logs in.
- [ ] Seller creates own shop and lists a secondhand shirt with size, price and stock.
- [ ] Buyer sees item and filters new/secondhand correctly.
- [ ] Buyer orders with pickup, and later demonstrates delivery choice.
- [ ] Order creates exactly one set of records and deducts stock once.
- [ ] Seller updates order status, buyer sees the change.
- [ ] Unauthorized user cannot edit seller's product or read private orders.
- [ ] Logout blocks protected pages.
- [ ] If implemented: buyer sends size question and seller replies.

## SQL evidence
- [ ] At least one INNER JOIN showing buyer, orders and shop/products.
- [ ] At least one LEFT JOIN preserving shops with zero orders.
- [ ] PK/FK relation is visible in schema/ERD.
- [ ] Sample data does not expose personal details or credentials.

## Presentation or reservation sheet
- [ ] Prepare project name, Canva/slides URL, Angsila URL, GitHub URL, member student IDs as required.
- [ ] Do not enter any shared passwords into reservation sheets.
- [ ] Confirm with instructor where/when round-2 materials should be submitted.

## Actual proof
- [ ] GitHub link opens to correct repo, latest code and README.
- [ ] Angsila link is reachable outside localhost and PHP/MySQL connection works.
- [ ] Report is generated and opens correctly.
- [ ] Screenshots are from the actual running version, not mockups.
- [ ] A new user can follow README to run the application.
- [ ] No `.env`, passwords, auth cookies, test real-user info or access tokens in commits.

**Ready-to-submit rule:** All coursework requirements above must pass; optional features may remain documented as future work.
