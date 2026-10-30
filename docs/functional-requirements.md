# CLOZEE — Requirements v1.0 (Draft)

**Priority:** P0 = required MVP; P1 = later if time remains. Acceptance criteria should be demonstrated, not merely described.

| ID | Priority | Functional requirement | Example acceptance criterion |
|---|---|---|---|
| FR-01 | P0 | User registration and login | Valid credentials enter protected area; duplicate email rejected. |
| FR-02 | P0 | Logout and session guard | Logged-out user cannot reopen protected operations. |
| FR-03 | P0 | Create/edit user's own shop | Owner can edit own shop; another user cannot. |
| FR-04 | P0 | CRUD clothing products | Seller can add, list, edit, deactivate own items; another seller cannot. |
| FR-05 | P0 | Maintain new/secondhand condition, size, price, stock | Item variations persist correctly; stock and prices validate. |
| FR-06 | P0 | Browse and filter products | Filtering secondhand excludes new products. |
| FR-07 | P0 | View product/shop details | Correct shop and size information is shown. |
| FR-08 | P0 | Place a single-shop order | Buyer can select quantity/variant; stock reduces exactly once. |
| FR-09 | P0 | Select pickup or local delivery | Chosen method and location details appear in order history. |
| FR-10 | P0 | Seller updates order status | Only relevant shop owner can change status. |
| FR-11 | P0 | Buyer views order history | Buyer sees their orders and item/price snapshots. |
| FR-12 | P0 | Authorize every mutation by ownership | Direct forged URL/POST to another shop is rejected. |
| FR-13 | P1 | Buyer–seller messages for size questions | Only conversation participants can read/send messages. |
| FR-14 | P1 | Upload multiple product images | Multiple images are displayed safely if implemented. |
| FR-15 | P1 | Seller sales dashboard | Seller sees aggregated own-shop data only. |

## Non-functional requirements

- **NFR-01 Security:** `password_hash()` / `password_verify()`; rotate session ID at login; secure logout; CSRF checks on mutating forms; HTML escaping; prepared statements; authorization on every data change.
- **NFR-02 Data integrity:** Foreign keys, unique email/one-shop-per-user, consistent transactions for checkout and stock.
- **NFR-03 Compatibility:** Must work with the PHP and MySQL/MariaDB versions actually provided by Angsila; confirm before deployment.
- **NFR-04 Usability:** Responsive phone and desktop pages; Thai labels are preferred; clear errors.
- **NFR-05 Version control:** Meaningful commits, setup README, SQL schema with safe import steps, `.gitignore` and private DB configuration.
- **NFR-06 Testability:** Document setup, seeded dummy accounts (if added), manual test scenarios and actual results; don't claim tests have passed unless executed.

## Suggested practical test cases
1. Wrong login rejected; correct login succeeds; logout protects internal pages.
2. Seller A cannot edit Seller B's product by changing IDs in URL/POST.
3. New and secondhand product filtering produces expected records.
4. Product with different sizes shows independent stock/price.
5. Checkout with insufficient stock fails without partial order creation.
6. Two simultaneous purchases of last item do not oversell.
7. Seller order status update is visible to relevant buyer only.
8. Pickup and delivery details save correctly for each method.
9. INNER JOIN of orders and buyers works; LEFT JOIN shows shop with no orders.
10. No `.env`, DB credentials or user passwords are committed or leaked.
