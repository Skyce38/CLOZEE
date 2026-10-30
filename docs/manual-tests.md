# Manual acceptance and Angsila verification

Run with fictional accounts on a disposable demo database. Automated equivalents are in `tests/run.php` and `tests/browser.cjs`; see `test-results.md` for what was actually executed. These steps alone are not a claim of PASS.

| ID | Steps | Expected |
| --- | --- | --- |
| M01 | Open catalog on laptop and 390px phone; search/filter by condition, category and size | Correct active products; meaningful empty state; no page overflow |
| M02 | Register, repeat email, enter wrong password, then correct password | Unique email, safe error, correct login, rotated HttpOnly/SameSite session |
| M03 | Open protected seller/checkout/order URLs anonymously; logout and retry | Redirect to login, no private data; logout requires POST+CSRF |
| M04 | Seller A creates/edits shop and two-size product | Correct persisted name, price, stock and ownership |
| M05 | Seller B changes product ID in edit URL/POST | Denied; Seller A data unchanged |
| M06 | Delete unused test product, then attempt delete of one with orders | Unused product removed; referenced product retained with helpful error |
| M07 | Deactivate product/shop and open public item/checkout | Item hidden/new orders rejected; history retained |
| M08 | Add two variants from one shop; try adding a second shop | Same-shop cart accepted; cross-shop rejected |
| M09 | Order pickup, then delivery with fictional details | Fee 0 / configured fee; server total, snapshots, one stock deduction |
| M10 | Double-submit checkout / retry confirmation; buy last item simultaneously from two accounts | One order for same key; no overselling, no partial rows |
| M11 | Keep edit form open, order item from another account, submit stale stock form | Stale form rejected with reload instruction |
| M12 | Seller progresses pickup and delivery status; buyer observes; unrelated user sends status POST | Correct method-specific transitions; unauthorized requests denied |
| M13 | Cancel pending/preparing order twice | Stock returns once; second transition rejected; audit retained |
| M14 | Edit product name/price after purchase | Historical snapshot and total unchanged |
| M15 | Buyer asks size question; seller replies; unrelated user opens/POSTs thread | Participants succeed; unrelated user denied |
| M16 | Submit HTML/script-looking text, SQL injection string, missing/wrong CSRF | Escaped text; no bypass; mutation rejected without CSRF |
| M17 | Run `database/demo-joins.sql` | INNER results reflect actual orders; LEFT includes zero-order shop |
| M18 | On approved Angsila target, repeat M01–M17 and open forbidden paths | Actual PHP/DB flows pass; config/SQL/log/session files inaccessible |

M18 is NOT EXECUTED. Final acceptance requires a verified Angsila URL and deployed runtime, not merely a locally passing suite.
