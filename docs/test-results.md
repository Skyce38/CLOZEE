# CLOZEE Test Results

ข้อมูลจากการรันจริง ไม่ใช่รายการคาดหวัง

| Suite | Environment | Actual result | Executed (UTC) |
| --- | --- | --- | --- |
| Domain / real DB / multiprocess concurrency | PHP 8.5.12, 11.4.9-MariaDB | 47 PASS, 0 FAIL | 2026-10-30T09:29:20+00:00 |
| HTTP / browser / responsive | Playwright with installed Chrome (Browser plugin not available) | 24 PASS, 0 FAIL | 2026-10-30T09:34:11.510Z |
| GitHub CI | PHP 8.3.36 + MySQL 8.4.13 + Chromium | SUCCESS for verified commit c7783c5 | 2026-10-30 |
| Angsila | Not accessed / no target approved | NOT TESTED | — |

CI evidence: [41032288293](https://github.com/Skyce38/CLOZEE/actions/runs/41032288293); evidence/ci-verification.json records the verified commit and step results. Future commits need their own CI check before delivery; a passing older run does not prove a newer revision.

Commands: `php tests/run.php` with CLOZEE_TEST_DB set to a dedicated test DB; `npm ci` then `npm run test:browser` against the loopback demo server. PHP lint and scripts/doctor.php also executed. Database schema imported into a new isolated DB; migrate/seed scripts refuse existing data. Tests create only fake example.test accounts. Raw local logs remain in ignored storage; sanitized copies are in evidence/.

Browser viewports: 1536x1024, 1440x1000, 390x844. Browser plugin absent; used installed Chrome via Playwright. Interactions: register → own shop/product → buyer cart/checkout → seller transitions → cancel/restock → snapshot history → chat/reply → unauthorized checks → logout/login. Screenshots are from running PHP pages, not the design concept. Permission-denial requests intentionally return 403/404/405/422.

## Domain and database tests

| Test | Observed |
| --- | --- |
| money uses exact integer cents | PASS |
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

## Defects and test corrections recorded during development

- Normalized untrusted variant array keys before rendering failed form submissions and added a browser HTML-injection regression test.
- Corrected a PHP validation error string whose adjacent Unicode dash was interpreted as part of a variable name. Re-ran the domain suite with no warnings after correction.
- Corrected test setup: an account without a shop is redirected to shop creation; ownership test now creates a separate shop before checking foreign edits.
- Corrected a browser locator to match a snapshot cell containing nested size text, rather than requiring the entire cell to equal the name.
- Waited for image decoding before screenshots; the initial quick screenshot captured assets before load. Final evidence captures completed images.
- Browser-generated fixture products from failed runs were deactivated in the isolated local demo; order history was preserved.

## Remaining verification

Angsila, Apache host-specific mapping, HTTPS Secure cookie behavior on the live target, real network/browser-device coverage and restoration from a production backup have NOT been tested. No claim of penetration testing/load testing or real payment/delivery integration. App has no customer image upload. Manual target checklist: manual-tests.md.
