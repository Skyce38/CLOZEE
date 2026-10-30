# CLOZEE Test Results

ข้อมูลจากการรันจริง ไม่ใช่รายการคาดหวัง

| Suite | Environment | Actual result | Executed (UTC) |
| --- | --- | --- | --- |
| Domain / real DB / multiprocess concurrency | PHP 8.5.12, 11.4.9-MariaDB | 51 PASS, 0 FAIL | 2026-10-30T13:34:51+00:00 |
| HTTP / browser / responsive | Playwright with installed Chrome (Browser plugin not available) | 24 PASS, 0 FAIL | 2026-10-30T13:36:14.670Z |
| GitHub CI | PHP 8.3.36 + MySQL 8.4.13 + Chromium | SUCCESS for verified commit dd60169 | 2026-10-30 |
| Angsila real functional flows | Apache + PDO/MySQL 8.0.46, Chrome via university VPN | 30 PASS, 0 FAIL | 2026-10-30T14:42:08Z |
| Angsila anonymous HTTP/security checks | HTTPS from local machine via VPN | 17 PASS, 0 FAIL | 2026-10-30T18:42:01.795710+00:00 |

CI evidence: [41109199837](https://github.com/Skyce38/CLOZEE/actions/runs/41109199837); evidence/ci-verification.json records the verified commit and step results. Future commits need their own CI check before delivery; a passing older run does not prove a newer revision.

Commands: `php tests/run.php` with CLOZEE_TEST_DB set to a dedicated test DB; `npm ci` then `npm run test:browser` against the loopback demo server. PHP lint and scripts/doctor.php also executed. Database schema imported into a new isolated DB; migrate/seed scripts refuse existing data. Tests create only fake example.test accounts. Raw local logs remain in ignored storage; sanitized copies are in evidence/.

Browser viewports: 1536x1024, 1440x1000, 390x844. Browser plugin absent; used installed Chrome via Playwright. Interactions: register → own shop/product → buyer cart/checkout → seller transitions → cancel/restock → snapshot history → chat/reply → unauthorized checks → logout/login. Screenshots are from running PHP pages, not the design concept. Permission-denial requests intentionally return 403/404/405/422.

## Domain and database tests

| Test | Observed |
| --- | --- |
| money uses exact integer cents | PASS |
| explicit table slots resolve and bound text is unchanged | PASS |
| unknown table slots are rejected | PASS |
| reject negative and excessive decimal precision | PASS |
| pickup and delivery state machines differ | PASS |
| passwords stored as hashes | PASS |
| one shop per owner updates existing shop | PASS |
| product CREATE and READ | PASS |
| invalid negative product price rejected | PASS |
| duplicate sizes rejected | PASS |
| foreign owner product UPDATE rejected | PASS |
| foreign owner product DELETE rejected | PASS |
| owner product UPDATE persists | PASS |
| unused product physical DELETE works | PASS |
| foreign key rejects orphan variant | PASS |
| DB CHECK rejects actual zero quantity insert | PASS |
| pickup order deducts stock and uses server total | PASS |
| retry same key returns same order without extra stock deduction | PASS |
| same key different request rejected | PASS |
| mixed shop order rejected with no records | PASS |
| insufficient stock rolls back order | PASS |
| own shop purchase rejected | PASS |
| stale stock edit rejected after checkout | PASS |
| buyer and seller may read order; stranger may not | PASS |
| buyer may not change seller status | PASS |
| other seller may not change status | PASS |
| invalid status jump rejected | PASS |
| correct pickup sequence records status audit | PASS |
| completed order cannot be cancelled | PASS |
| referenced product hard delete rejected | PASS |
| product snapshots survive name and price edits | PASS |
| delivery fee calculated server-side | PASS |
| delivery cannot move to pickup status | PASS |
| cancellation returns stock exactly once | PASS |
| deactivated product cannot be ordered | PASS |
| deactivated shop cannot receive orders | PASS |
| concurrent buyers cannot oversell last item | PASS |
| concurrent duplicate checkout creates exactly one order | PASS |
| LEFT JOIN retains shop with no orders | PASS |
| INNER JOIN returns buyer shop and snapshot items | PASS |
| catalog has no duplicate products from variants | PASS |
| chat start returns existing buyer/product thread | PASS |
| shop owner cannot start a buyer thread with self | PASS |
| chat sender stored from authenticated actor | PASS |
| unrelated user cannot read or send to conversation | PASS |
| shop owner replies to authorized conversation | PASS |
| empty or oversized chat text rejected | PASS |
| schema installer refuses nonempty database without changes | PASS |
| demo seeder refuses existing users without changes | PASS |
| shared installer refuses occupied or empty namespace | PASS |
| unrelated tables retain their row counts | PASS |

## Browser tests

| Test | Observed |
| --- | --- |
| catalog renders real PHP data and all product assets | PASS |
| new/preloved filter and empty search update results | PASS |
| mobile catalog has no horizontal page overflow | PASS |
| protected pages redirect anonymous users; logout is POST only | PASS |
| seller registers with HttpOnly and SameSite session cookie | PASS |
| seller creates and edits own shop | PASS |
| seller creates multi-size product using real form | PASS |
| malformed variant keys cannot inject HTML on validation errors | PASS |
| buyer registers, adds item, places pickup order | PASS |
| buyer cannot mutate seller order status | PASS |
| CSRF rejected before product mutation | PASS |
| other account cannot edit product or read private order | PASS |
| seller progresses pickup order; buyer sees completion | PASS |
| delivery checkout records fee and cancellation returns stock | PASS |
| seller edit persists while historical price/name stay unchanged | PASS |
| buyer asks a size question and seller replies in private chat | PASS |
| unrelated account cannot read or forge chat messages | PASS |
| chat content is escaped instead of executing HTML | PASS |
| referenced product cannot be deleted; deactivation hides public item | PASS |
| mobile seller table does not overflow the page | PASS |
| logout invalidates session and protects routes again | PASS |
| wrong login rejected then correct login restores access | PASS |
| SQL injection string cannot bypass login | PASS |
| no unexpected console, asset or page errors | PASS |


## Angsila live functional checks

| Observed flow | Result |
| --- | --- |
| Seller registration and authenticated session | PASS |
| Shop CREATE persisted | PASS |
| Product CREATE with M/L variants, price and stock | PASS |
| Product UPDATE persisted | PASS |
| Catalog READ and images loaded | PASS |
| Logout redirects and clears authenticated UI | PASS |
| Anonymous seller pages redirect to login | PASS |
| Buyer registration and separate identity | PASS |
| Cart quantity and 580.00 total | PASS |
| Pickup checkout: quantity 2, total 580, shipping 0 | PASS |
| Stock M decreases from 5 to 3 after pickup purchase | PASS |
| Delivery checkout: total 320, shipping 30 | PASS |
| Buyer sends private chat; script markup displayed as text | PASS |
| Existing seller Login succeeds | PASS |
| Pickup seller state sequence to completed with five audit entries | PASS |
| Delivery cancellation restores M stock 2 to 3; refresh leaves 3 | PASS |
| Wrong password rejected without authenticated session | PASS |
| Buyer sees seller reply and completed pickup order without status controls | PASS |
| Unrelated account denied private order and chat | PASS |
| Foreign shop owner denied product edit | PASS |
| Owner shop UPDATE and deactivate persist | PASS |
| Delivery state sequence includes out_for_delivery and completes | PASS |
| Order snapshots retain old name and 290 price after catalog edit to 295 | PASS |
| Product deactivate persists and public product is blocked | PASS |
| Product reactivation restores catalog entry | PASS |
| Mobile catalog has no horizontal overflow and images load at 390x844 viewport | PASS |
| Mobile seller order table has no page overflow | PASS |
| Current page JavaScript error/warning log empty | PASS |
| Condition filters exclude/include existing preloved product correctly | PASS |
| Unmatched keyword search returns zero products and empty state | PASS |


Independent fresh-browser desktop/mobile screenshots: evidence/angsila-catalog-desktop.png and angsila-catalog-mobile.png. These use no saved browser session. Live physical DELETE, concurrent races, backup restore and off-VPN reachability were not repeated; see evidence/angsila-functional-results.json. The isolated local/MySQL CI suites cover physical DELETE and concurrency separately.
## Defects and test corrections recorded during development

- Normalized untrusted variant array keys before rendering failed form submissions and added a browser HTML-injection regression test.
- Corrected a PHP validation error string whose adjacent Unicode dash was interpreted as part of a variable name. Re-ran the domain suite with no warnings after correction.
- Corrected test setup: an account without a shop is redirected to shop creation; ownership test now creates a separate shop before checking foreign edits.
- Corrected a browser locator to match a snapshot cell containing nested size text, rather than requiring the entire cell to equal the name.
- Waited for image decoding before screenshots; the initial quick screenshot captured assets before load. Final evidence captures completed images.
- Browser-generated fixture products from failed runs were deactivated in the isolated local demo; order history was preserved.

## Remaining verification

Angsila mapping, live core flows, HTTPS Secure/HttpOnly/SameSite cookies, blocked private URLs and fresh-browser desktop/mobile rendering were tested. Off-VPN access, actual mobile hardware, penetration/load testing and restoration from backup were not verified. App has no customer image upload or real payment/delivery integration. See deployment-verification.md and manual-tests.md.
