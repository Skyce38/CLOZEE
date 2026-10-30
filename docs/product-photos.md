# รูปสินค้าและรายละเอียดเสื้อผ้า

สถานะ 31 ตุลาคม 2569: พัฒนาและทดสอบในเครื่องแล้ว ยังไม่อัปเดต Angsila เว็บไซต์จริงและรายงานรอบ 2 เดิมยังเป็นรุ่น 10 ตาราง 75 ฟิลด์

## สิ่งที่เพิ่ม

- ผู้ขายเพิ่มรูป JPG, PNG หรือ WebP สูงสุด 4 รูปต่อสินค้า ดูตัวอย่างก่อนบันทึก เลือกรูปหลัก และนำรูปเดิมออกได้
- แบรนด์ รอบอกและความยาวหน่วยเซนติเมตร และสภาพ/ตำหนิ เป็นข้อมูลเพิ่มเติม ไม่บังคับกรอก รอบอกและความยาวต้องมากกว่า 0 ถึง 300 ซม. ทศนิยมหนึ่งตำแหน่ง
- หน้ารายละเอียดแสดงแกลเลอรีและข้อมูลเสื้อผ้า การ์ดสินค้าทั้งหน้าค้นหาและหน้าร้านใช้ภาพหลักที่เลือก สินค้าเดิมยังใช้ภาพสาธิตได้
- ตรวจสิทธิ์เจ้าของและ CSRF ก่อนเก็บไฟล์ ตรวจ MIME และถอดรหัสภาพจริง หมุนตาม EXIF แล้วย่อด้านยาวเหลือไม่เกิน 1,600 พิกเซล พร้อมเข้ารหัสภาพใหม่เพื่อตัด metadata และข้อมูลแทรกท้ายไฟล์
- เก็บชื่อไฟล์สุ่มใน private storage นอก public webroot ให้ `image.php` ส่งเฉพาะรูปที่เปิดขายหรือเจ้าของมีสิทธิ์ดู เมื่อลบรูปหรือสินค้าโดยไม่มีประวัติสั่งซื้อ จะล้างไฟล์หลัง transaction สำเร็จ

จำกัดไฟล์ต้นฉบับ 1.5 MiB ต่อรูป หรือข้อจำกัด PHP ที่ต่ำกว่า; ไม่เกิน 6,000 พิกเซลต่อด้านและ 12 ล้านพิกเซล ขนาดรวมต้องไม่เกิน `post_max_size` หักพื้นที่ข้อมูลฟอร์ม ระบบปฏิเสธ SVG, ไฟล์ปลอม, ไฟล์เสีย และไฟล์เกินขนาด หากบันทึกไม่สำเร็จต้องเลือกไฟล์ใหม่ตามข้อจำกัดของ browser

## Schema ที่เพิ่ม

รุ่นนี้มี 11 ตาราง 88 ฟิลด์ 15 foreign keys และ 7 CHECK constraints ตรวจจาก MariaDB ในเครื่องแล้ว ไม่เปลี่ยนความสัมพันธ์คำสั่งซื้อหรือสต็อก

| ตาราง/ฟิลด์ | ชนิด | ความหมาย |
| --- | --- | --- |
| products.brand | VARCHAR(100), NULL | แบรนด์ |
| products.chest_cm | DECIMAL(4,1), NULL | รอบอก > 0 และ ≤ 300 |
| products.length_cm | DECIMAL(4,1), NULL | ความยาว > 0 และ ≤ 300 |
| products.condition_notes | VARCHAR(1000), NULL | สภาพและตำหนิ |
| product_images.image_id | BIGINT UNSIGNED, PK | รหัสรูป |
| product_images.product_id | BIGINT UNSIGNED, FK | สินค้าเจ้าของรูป; CASCADE เมื่อลบสินค้า |
| product_images.storage_name | VARCHAR(64), UNIQUE | ชื่อไฟล์สุ่ม |
| product_images.mime_type | VARCHAR(32) | MIME ภาพหลังแปลง |
| product_images.byte_size | INT UNSIGNED | ขนาดหลังแปลง |
| product_images.width / height | SMALLINT UNSIGNED | ขนาดพิกเซลหลังแปลง |
| product_images.sort_order | TINYINT UNSIGNED | ลำดับแสดง รูปแรกเป็นภาพหลัก |
| product_images.created_at | TIMESTAMP | เวลาบันทึก |

หนึ่งสินค้ามีรูป 0–4 รูป จำกัดจำนวนภายใต้ product row lock ใน service พร้อม index `(product_id, sort_order, image_id)` สำหรับเรียกภาพหลักและแกลเลอรี

## ผลทดสอบในเครื่อง

รันจริงวันที่ 31 ตุลาคม 2569 (เวลาไทย), PHP 8.5.12 / MariaDB 11.4.9 / Chrome ผ่าน Playwright:

| ชุดทดสอบ | ผ่าน | ไม่ผ่าน |
| --- | ---: | ---: |
| `tests/run.php` ฐานทดสอบไม่มี prefix | 54 | 0 |
| `tests/run.php` ฐานทดสอบ prefix `clozee_` | 54 | 0 |
| `tests/product-photos.php` ตรวจภาพ/ข้อมูล/ขอบเขตไฟล์ | 17 | 0 |
| `tests/product-photos.cjs` อัปโหลด แกลเลอรี สิทธิ์ CSRF rollback สต็อก และมือถือ | 20 | 0 |
| `tests/browser.cjs` ระบบเดิมบนโค้ดสาขานี้ | 24 | 0 |

หลักฐาน JSON อยู่ใน private `storage/` และ CI artifacts; ตัวอย่างหน้าจออยู่ใน `docs/evidence/product-photos/` ตรวจหน้าจอ 1440px และ 390px แล้วไม่มี page overflow ทดสอบ migration บนฐาน local/test ทั้งสอง namespace โดยจำนวนสินค้าและคำสั่งซื้อคงเดิม เรียกซ้ำแล้วไม่แก้ข้อมูล

GitHub Actions ผ่านครบทั้ง 5 ชุดบน MySQL 8.4.13 / PHP 8.3 / Chromium: [run 41115700733](https://github.com/Skyce38/CLOZEE/actions/runs/41115700733), commit `d9f9eea` ดู `evidence/product-photos/ci-verification.json` ผลเหล่านี้ยังไม่ใช่การทดสอบฟีเจอร์รูปสินค้าบน Angsila

## ก่อนอัปเดต Angsila

1. ขออนุมัติตรวจ PHP ในบริบทเว็บ CLOZEE: `fileinfo`, GD รองรับ JPEG/PNG/WebP, EXIF, memory/upload/post limits และสิทธิ์เขียน private storage ลบเครื่องมือตรวจทันทีหลังใช้งาน ไม่เผยแพร่ phpinfo
2. สำรองตาราง `clozee_` และไฟล์เดิม รวม private uploads โดยไม่เผยแพร่ configuration หรือ password
3. ตรวจ SQL `database/angsila-product-photos-migration.sql` ซึ่งเพิ่ม 4 คอลัมน์ 2 CHECK และตาราง `clozee_product_images` เท่านั้น ขออนุมัติก่อน import ห้ามใช้ full schema ทับฐานเดิม และห้ามรัน seed บน production
4. หลังอนุมัติ ใช้ staging แบบ public/private ตาม `deployment.md`; ให้ private `storage/uploads/products` เขียนได้เฉพาะบัญชีที่ PHP ใช้งาน และเก็บนอก `public_html` ไม่เปลี่ยนไฟล์ตั้งค่ารหัสผ่านเดิม
5. ทดสอบเพิ่ม/เปลี่ยน/ลบภาพ สิทธิ์อีกบัญชี ไฟล์ปลอม สต็อก/checkout และมือถือบน URL จริง แล้วบันทึกผลก่อนอัปเดตรายงานหรือประกาศพร้อมใช้งาน

CLI สำหรับฐาน local ที่สำรองแล้ว: `php scripts/migrate-product-photos.php --apply` จะปฏิเสธกรณีตรวจพบ migration บางส่วน และไม่แก้อัตโนมัติ MySQL DDL อาจ auto-commit หากเกิดข้อผิดพลาดให้หยุดตรวจ ไม่ import ซ้ำทันที การย้อนโค้ดต้องใช้ revision เดิมร่วมกับ schema ที่เพิ่มแบบ additive; ไม่ลบคอลัมน์/ตารางหรือ restore ข้อมูลจริงโดยไม่ได้รับอนุมัติ

`database/schema.sql` และ `database/angsila-schema.sql` ในสาขานี้ใช้สำหรับติดตั้งใหม่บน namespace ว่างเท่านั้น รายละเอียดข้อมูลใหม่ในเอกสารนี้เป็นภาคเพิ่มเติมของ Data Dictionary เดิม รายงาน PDF จะปรับเป็นรุ่นใหม่หลังตรวจ deployment จริง

## ขั้นต่อไป

หลังฟีเจอร์รูปสินค้าผ่านการติดตั้งและตรวจจริง จึงเริ่มภาษาไทย/อังกฤษ ต่อด้วยรีวิวจากคำสั่งซื้อที่สำเร็จ ส่วนค้นหาร้านใกล้ฉันและขายทั่วประเทศต้องออกแบบที่ตั้ง พื้นที่บริการ ค่าส่งและที่อยู่จัดส่งเพิ่มเติมก่อนพัฒนา
