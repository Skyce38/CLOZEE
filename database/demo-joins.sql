-- Run in your dedicated demo DB. These use CLOZEE data, not the Campus Delivery lab.
-- INNER JOIN: actual buyers, orders, shops and snapshot line items.
SELECT o.order_id,u.full_name AS buyer,s.shop_name,oi.product_name_snapshot,
       oi.size_label_snapshot,oi.quantity,oi.unit_price,o.status
FROM orders o
INNER JOIN users u ON u.user_id=o.buyer_user_id
INNER JOIN shops s ON s.shop_id=o.shop_id
INNER JOIN order_items oi ON oi.order_id=o.order_id
ORDER BY o.order_id,oi.order_item_id;

-- LEFT JOIN: every shop, including shops with zero orders. COUNT(nullable PK) is 0.
SELECT s.shop_id,s.shop_name,COUNT(o.order_id) AS order_count
FROM shops s LEFT JOIN orders o ON o.shop_id=s.shop_id
GROUP BY s.shop_id,s.shop_name ORDER BY s.shop_id;

-- Never sum order totals after joining order_items: each order total would be repeated.
SELECT s.shop_id,s.shop_name,COALESCE(SUM(o.total_amount),0) AS completed_sales
FROM shops s LEFT JOIN orders o ON o.shop_id=s.shop_id AND o.status='completed'
GROUP BY s.shop_id,s.shop_name ORDER BY s.shop_id;
