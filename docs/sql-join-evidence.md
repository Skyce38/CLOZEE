# CLOZEE SQL JOIN evidence

Executed against the isolated local demo database, with fictional browser-test orders. Re-run database/demo-joins.sql on the actual approved demo environment; IDs/counts depend on demo activity. No credentials are exported.

## INNER JOIN actual result

| Order | Buyer | Shop | Snapshot item | Qty | Unit price |
| --- | --- | --- | --- | --- | --- |
| 1 | Demo buyer | Browser Demo Wardrobe mvuqxv5p | Browser linen mvuqxv5p | 2 | 290.00 |
| 2 | Demo buyer | Browser Demo Wardrobe mvuqypuh | Browser linen mvuqypuh | 2 | 290.00 |
| 3 | Demo buyer | Browser Demo Wardrobe mvuqypuh | Browser linen mvuqypuh | 1 | 290.00 |
| 4 | Demo buyer | Browser Demo Wardrobe mvur03vx | Browser linen mvur03vx | 2 | 290.00 |
| 5 | Demo buyer | Browser Demo Wardrobe mvur03vx | Browser linen mvur03vx | 1 | 290.00 |
| 6 | Demo buyer | Browser Demo Wardrobe mvur42rk | Browser linen mvur42rk | 2 | 290.00 |
| 7 | Demo buyer | Browser Demo Wardrobe mvur42rk | Browser linen mvur42rk | 1 | 290.00 |
| 8 | Demo buyer | Browser Demo Wardrobe mvurim4c | Browser linen mvurim4c | 2 | 290.00 |
| 9 | Demo buyer | Browser Demo Wardrobe mvurim4c | Browser linen mvurim4c | 1 | 290.00 |
| 10 | Demo buyer | Browser Demo Wardrobe mvurt7yi | Browser linen mvurt7yi | 2 | 290.00 |
| 11 | Demo buyer | Browser Demo Wardrobe mvurt7yi | Browser linen mvurt7yi | 1 | 290.00 |
| 12 | Demo buyer | Browser Demo Wardrobe mvurz5ej | Browser linen mvurz5ej | 2 | 290.00 |

## LEFT JOIN actual result

| Shop ID | Shop | Order count |
| --- | --- | --- |
| 1 | Bangsaen Closet | 0 |
| 2 | BUU Rewear | 0 |
| 3 | The Next Wardrobe | 0 |
| 4 | Browser Demo Wardrobe mvuqxv5p | 1 |
| 5 | Browser Demo Wardrobe mvuqypuh | 2 |
| 6 | Outsider Demo Shop | 0 |
| 7 | Browser Demo Wardrobe mvur03vx | 2 |
| 8 | Outsider Demo Shop | 0 |
| 9 | Browser Demo Wardrobe mvur42rk | 2 |
| 10 | Outsider Demo Shop | 0 |
| 11 | Browser Demo Wardrobe mvurim4c | 2 |
| 12 | Outsider Demo Shop | 0 |
| 13 | Browser Demo Wardrobe mvurt7yi | 2 |
| 14 | Outsider Demo Shop | 0 |
| 15 | Browser Demo Wardrobe mvurz5ej | 2 |
| 16 | Outsider Demo Shop | 0 |

Zero-order shops remain because shops is the preserved LEFT side. COUNT(o.order_id) counts matched rows, not the NULL placeholder. Snapshot items can repeat an order ID because orders 1:N order_items. Do not SUM order totals across item rows.
