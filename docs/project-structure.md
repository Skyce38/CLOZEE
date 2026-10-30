# CLOZEE implemented project structure

This tree describes the actual local implementation. The original proposed tree is preserved in reference/project-structure-v0.3.md. Only public/ may be web-visible.

```text
CLOZEE/
  app/
    bootstrap.php                 configuration, headers, shared helpers
    validation.php                money, IDs, enums and status transitions
    config/example.php            safe configuration template
    config/db.php                 native PDO connection/query/transaction helpers
    config/local.php              PRIVATE and gitignored; not in release packages
    auth/session.php              session/auth/CSRF/throttling
    services/                     ShopService, ProductService, OrderService, ChatService
    pages/                        PHP controllers and HTML for public/protected flows
    templates/                    shared header/footer/product/seller navigation
  public/
    index.php, products.php, product.php, shop.php
    register.php, login.php, logout.php
    cart.php, checkout.php, orders.php, order.php, messages.php
    seller/                       dashboard, shop, products, product-edit, orders
    assets/                       CSS, vanilla JS, favicon, generated demo photos
  database/
    schema.sql                    reviewed 10-table schema
    seed.sql                      fictional data with password hashes only
    demo-joins.sql                CLOZEE INNER/LEFT JOIN examples
  scripts/
    migrate.php, seed.php, doctor.php, export-metadata.php
    build-docs.py, render-diagrams.cjs, print-report.cjs, package-release.py
  tests/
    run.php, worker.php           real DB, business rules and process concurrency
    browser.cjs                   real HTTP/Playwright acceptance and responsive checks
  docs/
    course-requirements.md, implementation-decisions.md, test-results.md
    data-dictionary.md, use-cases.md, sql-join-evidence.md
    final-report.md, final-report.html, CLOZEE-Final-Report-Round2.pdf
    deployment.md, deployment-verification.md, manual-tests.md
    final-submission-checklist.md, build-log.md, visual-qa.md
    diagrams/                     ER / use cases / architecture / status: DOT + SVG
    evidence/                     actual non-secret screenshots and JSON results
    design/                       visual concept; not test evidence
    reference/                    original planning baseline, not current readiness
  storage/                        private logs/accounts/session-independent runtime files; ignored
  output/                         generated source/deployment ZIPs and manifest; ignored
  .github/workflows/test.yml       isolated PHP/MySQL/Chromium CI
  .gitignore, .gitattributes, README.md
  package.json, package-lock.json  DEV tooling only; no Node production runtime
```

The application has no rewrite dependency or Composer dependency. Apache must map the URL to public/ while keeping app/config, storage, database and tooling outside webroot. Actual Angsila mapping is unknown and not yet authorized; see deployment.md. There is no claim that the target server has been tested.
