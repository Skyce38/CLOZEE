# CLOZEE — Use Cases (Draft)

## Actors
- **Visitor:** unauthenticated visitor.
- **Buyer:** logged-in CLOZEE user placing orders; any account can be a buyer.
- **Seller:** logged-in user who owns a shop; same account may also be a buyer.

## Core use cases

| ID | Actor | Goal | Prerequisite / output |
|---|---|---|---|
| UC-01 | Visitor | Register / Login | Unique email and valid password; establish session. |
| UC-02 | Visitor / Buyer | Browse/filter new or secondhand products | List active products, optionally by size/category. |
| UC-03 | Buyer | View product details | Show shop, condition, size, price, stock. |
| UC-04 | Seller | Create/edit shop | Must be logged in; one shop per account. |
| UC-05 | Seller | Add/edit/deactivate products | Must own shop; changes persist in DB. |
| UC-06 | Buyer | Place order and select pickup/delivery | Authenticated; available stock; order created atomically. |
| UC-07 | Buyer | View order and status | Orders are scoped to buyer. |
| UC-08 | Seller | View/manage received orders | Only orders of their shop; valid status transitions. |
| UC-09 | Buyer / Seller | Send/read messages (P1) | Only chat participants may access thread. |
| UC-10 | User | Logout | Invalidate session; redirect to public page. |

## UC-06: Place an order — main flow
1. Buyer logs in and opens an active product of a local shop.
2. Buyer selects a size/variant, quantity and checkout.
3. Buyer chooses pickup or delivery, entering meeting/delivery details.
4. PHP validates authentication, selected shop, stock, and input.
5. Inside one transaction, lock selected variant records, re-check stock, create `orders`/`order_items`, reduce stock, then commit.
6. Buyer sees confirmation and saved order history.

**Alternative flows:** Out of stock → clear message, rollback; variant belongs to another shop → reject; invalid pickup/delivery details → no order saved; DB error → rollback. Payments are outside this MVP.

## UC-05: Seller CRUD — main flow
1. Shop owner signs in, opens own seller dashboard.
2. Creates a product with name, new/secondhand condition, description and size/price/stock variations.
3. System inserts into products and variants; owner can update the item later.
4. Deactivating hides the item from public listing but preserves historical orders.

**Forbidden:** A user may not edit another shop's item, even if a URL or POST payload is modified.

## Executed implementation and diagram

[Use Case Diagram](diagrams/use-cases.svg) is generated from diagrams/use-cases.dot. UC-01–10 have implementation and tested flows, including the optional UC-09 text chat. Chat requires manual refresh and only buyer/shop owner access. UC-05 additionally permits physical deletion only when no order history references the product; historical products deactivate. UC-06 server-checks price/fee/activity/stock and uses a unique checkout key, request hash and DB row locks. See test-results.md rather than treating draft example flows as test results. Angsila execution remains pending.
