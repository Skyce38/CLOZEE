# CLOZEE — Proposed Project Structure (historical planning draft)

The current package only contains planning docs and draft schema. This tree is the *target* for the web application, not proof that these files already exist.

```text
CLOZEE/
├── README.md
├── .gitignore                       # create when implementing
├── docs/
│   ├── project-spec.md
│   ├── functional-requirements.md
│   ├── use-cases.md
│   ├── project-structure.md
│   ├── planning-context.md
│   └── course-requirements.md       # To create after reviewing PDFs
├── database/
│   ├── schema.sql
│   └── seed.sql                     # To create for demo accounts
├── app/
│   ├── config/
│   │   ├── db.php                   # no embedded credentials
│   │   └── env.example.php          # safe non-secret template, if needed
│   ├── auth/
│   │   ├── session.php
│   │   └── csrf.php
│   ├── services/
│   │   ├── ShopService.php
│   │   ├── ProductService.php
│   │   └── OrderService.php
│   └── templates/
│       ├── header.php
│       └── footer.php
├── public/                          # web-facing document root / mapped Angsila subfolder
│   ├── index.php
│   ├── register.php
│   ├── login.php
│   ├── logout.php
│   ├── products.php
│   ├── product.php
│   ├── shop.php
│   ├── seller/
│   │   ├── dashboard.php
│   │   ├── products.php
│   │   └── orders.php
│   ├── checkout.php
│   ├── orders.php
│   └── assets/
│       ├── css/
│       ├── js/
│       └── images/
└── storage/                         # private writable area if host supports
    └── uploads/
```

## Deployment caution

Do not assume that Angsila can use `public/` as its document root or that it allows writable folders outside a specific home directory. Confirm actual PHP version, file permissions and web root before adapting structure. Keep config and secrets outside the web-visible area whenever possible; do not commit real host credentials. If the host requires a different layout, document the mapping and keep source-of-truth protected.
