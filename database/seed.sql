-- Fictional demonstration data only. Import once into an EMPTY CLOZEE schema.
-- Hashes below cannot be used to sign in with a published password.
-- Run scripts/seed.php to issue random local demo credentials in ignored storage/.
START TRANSACTION;
INSERT INTO users(user_id,full_name,email,password_hash) VALUES
(1,'Demo Seller One','seller.one@example.test','$2y$12$abdMGooFePoTCXAR8J1biOfnGMdomMkAjuIcL0Kl.e01XHuiEpOAe'),
(2,'Demo Seller Two','seller.two@example.test','$2y$12$abdMGooFePoTCXAR8J1biOfnGMdomMkAjuIcL0Kl.e01XHuiEpOAe'),
(3,'Demo Buyer','buyer@example.test','$2y$12$abdMGooFePoTCXAR8J1biOfnGMdomMkAjuIcL0Kl.e01XHuiEpOAe'),
(4,'Demo Empty Shop','empty.shop@example.test','$2y$12$abdMGooFePoTCXAR8J1biOfnGMdomMkAjuIcL0Kl.e01XHuiEpOAe');
INSERT INTO shops(shop_id,owner_user_id,shop_name,description,pickup_location) VALUES
(1,1,'Bangsaen Closet','เสื้อผ้าใส่สบายสำหรับทุกวัน ร้านสาธิต CLOZEE','จุดนัดรับสาธิต หน้ามหาวิทยาลัยบูรพา'),
(2,2,'BUU Rewear','ส่งต่อเสื้อผ้าตัวโปรดให้เจ้าของคนใหม่ ร้านสาธิต','จุดนัดรับสาธิต ย่านบางแสน'),
(3,4,'The Next Wardrobe','ร้านสาธิตที่ยังไม่มีสินค้าและคำสั่งซื้อ','จุดนัดรับสาธิต บางแสน');
INSERT INTO products(product_id,shop_id,product_name,description,category,condition_type,image_path) VALUES
(1,1,'Linen everyday shirt','เสื้อเชิ้ตลินินสีครีม เนื้อผ้าโปร่ง สภาพดี เหมาะกับวันสบาย ๆ กรุณาตรวจไซซ์ก่อนสั่งซื้อ','Shirts','secondhand','linen.jpg'),
(2,2,'Vintage denim','ยีนส์โทนฟอกธรรมชาติ ทรงตรง เนื้อผ้าแน่น สินค้ามือสอง รายละเอียดจำลองเพื่อสาธิตระบบ','Denim','secondhand','denim.jpg'),
(3,1,'Campus cotton tee','เสื้อยืดผ้าคอตตอนสีครีม ลายบางแสน ใส่สบาย สินค้าใหม่สำหรับข้อมูลสาธิต','T-shirts','new','tee.jpg'),
(4,2,'Relaxed knit cardigan','คาร์ดิแกนไหมพรมสีเขียวมะกอก ทรงผ่อนคลาย เหมาะกับห้องเรียนและคาเฟ่','Knitwear','secondhand','knit.jpg');
INSERT INTO product_variants(variant_id,product_id,size_label,price,stock_quantity) VALUES
(1,1,'M',290.00,5),(2,1,'L',310.00,3),
(3,2,'S',390.00,1),(4,2,'M',390.00,2),(5,2,'L',410.00,1),
(6,3,'S',180.00,10),(7,3,'M',180.00,10),(8,3,'L',200.00,8),
(9,4,'S',350.00,2),(10,4,'M',350.00,1);
COMMIT;
