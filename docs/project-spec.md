# CLOZEE — Project Specification v0.3 (Draft)

## Project name
CLOZEE — Local Clothing Marketplace & Delivery Web Application

## 1. Problem Statement
BUU students and people living in Bangsaen may buy and sell new or secondhand clothing through dispersed social media listings and general-purpose e-commerce services. For local buyers, verifying fit, actual condition, and pickup/delivery arrangements can require multiple conversations across separate channels. Small local sellers may also lack an integrated system for listings, available sizes, stock, and order status.

CLOZEE proposes one local web platform to publish and browse clothing listings, ask sellers about sizing and condition, place orders, and record a local pickup or delivery choice. These pains are **project hypotheses**, not measured findings from user research.

## 2. Target users
- Buyers: Burapha University students and other people in Bangsaen.
- Sellers: BUU students, secondhand clothing resellers, and local clothing shops.
- One account may buy and own one shop in the MVP. There is no separate rider or administrator role in the MVP.

## 3. Objectives
1. Model users, shops, clothing products, sizes/stock, orders, and buyer–seller conversations in a relational database.
2. Build functioning account registration, login, logout, session-based authorization, and product CRUD.
3. Allow buyers to filter products by new/secondhand, place an order, choose local pickup/delivery, and view status.
4. Let sellers list products and update order statuses.
5. Demonstrate SQL relationships, JOINs, and data integrity in a PHP/MySQL application.

## 4. MVP scope (priority P0)
- Responsive PHP website; accessible from phone and desktop.
- Register, login, logout, protected pages.
- A user can create/update their own shop (max 1 shop per user).
- A seller can create, read, update, and deactivate clothing products; manage sizes and stock.
- Browse, filter by `new` / `secondhand`, view product details.
- One order belongs to exactly one seller shop; may contain multiple variants from that shop.
- Choose `pickup` or `delivery`; save a location/address or meeting details as text.
- Basic order statuses controlled by the seller; buyer can view history.
- A single main product image field is sufficient initially; upload support can be added later.

## 5. Optional after MVP (P1)
- Buyer–seller text chat about sizes and clothing condition (schema planned from day one; UI/logic later).
- Multiple product images.
- Seller dashboard/reporting.

## 6. Out of scope
- Courier allocation, rider profiles, live maps/GPS tracking.
- Online payments, payment gateways, real shipping-provider integrations.
- Multi-shop checkout, refunds/dispute workflow, ratings, algorithmic recommendations.
- Mobile-native iOS/Android applications.

## 7. Business rules
- Seller operations require authenticated ownership of the relevant shop.
- Only active products with available stock may be ordered.
- Variant and order-item values are recorded separately so order history survives later price/size/name changes.
- All variants in an order must belong to the order's selected shop.
- Quantity must be positive; unit price and totals cannot be negative. These are validated at the application layer (and DB constraints if supported).
- Order creation and stock deduction must be atomic with DB transaction and locking to prevent overselling.
- Buyer cannot change the seller's order status directly.
- Conversation messages can only be sent/read by the buyer or that shop's owner. A conversation may be associated with a product.
- A shop and product referenced by order history should be deactivated rather than hard-deleted.

## 8. Technology and deployment assumptions
- PHP 8+ with mysqli prepared statements or PDO (choose one consistently).
- MySQL or MariaDB using utf8mb4.
- HTML/CSS/vanilla JS; minimal/no server build tooling.
- GitHub repository with README, meaningful commit history, and secrets excluded.
- Deploy on the BUU Angsila web server **after confirming supported PHP/DB versions, credentials and webroot**.

## 9. Phased implementation
- Phase 1: Verify course requirements; approve data model; project skeleton, schema, register/login/logout and secure sessions.
- Phase 2: Shops, products, variants and seller-owned CRUD.
- Phase 3: Buyer browsing/filtering, single-shop checkout, pickup/delivery, seller statuses, order history.
- Phase 4: Optional simple chat, UI polish, tests, deployment, final report and presentation.

## 10. Milestones as shown in provided teaching slide
- **31 Oct 2026:** proposal: topic, pain points, target users, solution, features, draft ERD, project plan, GitHub repository (slide 34).
- **10 Nov 2026:** final website, report and repository (slide 34).
- Instructor's separate reservation sheet included presentation slots for 30 Oct 2026; do not assume that changes the written submission dates. Verify latest announcements directly.

## 11. Main report contents
Introduction and audience; requirements/use cases; ER diagram/schema/data dictionary; architecture/screenshot walk-through; CRUD and auth/security; tests and limitations; public Angsila URL and repository URL. These are based on the provided teaching slides, not on general project assumptions.

## 12. Round 2 implementation status

Local implementation now follows docs/round2-build-brief.md. Actual code decisions are in implementation-decisions.md; test counts/evidence are in test-results.md. Text-only participant chat is implemented after local core tests; multiple images and sales analytics are deferred. Physical DELETE is supported only for products without order references; other products deactivate. Order idempotency, stale stock protection, cancellation/restock and status audit are implemented. Angsila has NOT been accessed/deployed, following the user's instruction to complete local work first. Original pain-point hypotheses and out-of-scope boundaries remain unchanged. See course-requirements.md for verified lecturer requirements and final-submission-checklist.md for remaining gates.
