# รูปสินค้าในฐานข้อมูล — SQL สำหรับตรวจอนุมัติ

สถานะ 31 ตุลาคม 2569: ปรับโค้ดและทดสอบ Local แล้ว ยังไม่ติดตั้งบน Angsila และยังไม่รัน SQL ใหม่บนฐานจริง ใช้เอกสารนี้แทนขั้นตอน private-file uploads ใน `product-photos.md`

## แบบที่ปรับแล้ว

Angsila ตรวจพบ PHP 8.4.27 ไม่มี GD และบัญชี PHP เขียน private storage ไม่ได้ จึงเปลี่ยนมาเก็บภาพใน `product_images.image_data` ชนิด `MEDIUMBLOB` ชื่อ `storage_name` เป็นเพียงรหัสอ้างอิง ไม่ใช่ path ที่เปิดอ่านจาก filesystem

1. ผู้ขายเลือกรูป JPG/PNG/WebP ได้ 4 รูป ต้นฉบับไม่เกิน 8 MiB ต่อรูป และไม่เกิน 6,000 พิกเซลต่อด้าน / 12 ล้านพิกเซล
2. Browser ถอดรหัส หมุนตาม EXIF และย่อภาพก่อนส่งเป็น PNG ด้านยาวไม่เกิน 1,600 พิกเซล หากยังใหญ่จะลดขนาดต่อจนไม่เกิน 1.5 MiB หรือข้อจำกัด PHP ที่ต่ำกว่า ปุ่มบันทึกพักระหว่างปรับภาพ
3. PHP ตรวจ MIME/signature, ลำดับ chunk, CRC, IHDR, ขนาดภาพ, ขนาดข้อมูลหลังคลาย zlib และ scanline filters ด้วยขอบเขตหน่วยความจำ ยอมรับเฉพาะ PNG ภาพนิ่ง RGB/RGBA 8-bit ไม่ interlace ที่รองรับ ไม่เชื่อค่าที่ browser ส่งมา
4. Server สร้าง PNG ใหม่จาก IHDR/ข้อมูลพิกเซลที่ตรวจแล้ว/IEND ตัด ancillary metadata และข้อมูลท้ายไฟล์ออก ปฏิเสธ APNG, critical chunks ที่ไม่รองรับ, ไฟล์เสีย และข้อมูลบีบอัดที่คลายเกินขนาด ไม่ได้ใช้ GD หรือ JPEG/WebP decoder ฝั่ง PHP
5. บันทึกข้อมูลสินค้าและ BLOB ใน transaction เดียวกัน ล็อกสินค้าเพื่อจำกัด 4 รูป เมื่อ rollback หรือลบรูป ไม่มีไฟล์ที่ต้องตามล้าง การลบสินค้าที่ลบได้จะ cascade รูปใน DB; สินค้าที่มีคำสั่งซื้อยังใช้ปิดขายตามเดิม
6. ส่งภาพผ่าน `image.php` ด้วย MIME ที่กำหนด, `nosniff`, CSP และ `no-store`; รูปสินค้า/ร้านที่ปิดขายเห็นได้เฉพาะเจ้าของ การ์ดและฟอร์มดึงเฉพาะ metadata ไม่ดึง BLOB ทุกภาพ

อ้างอิงรูปแบบภาพ: [W3C PNG specification](https://www.w3.org/TR/png/), การหมุนภาพ: [createImageBitmap](https://developer.mozilla.org/en-US/docs/Web/API/Window/createImageBitmap), [gzuncompress พร้อมขอบเขตขนาด](https://www.php.net/manual/en/function.gzuncompress.php)

เมื่อปิด JavaScript จะไม่แปลง JPG/WebP อัตโนมัติ ฟอร์มแจ้งให้เปิด JavaScript หรือใช้ PNG ที่ตรงรูปแบบจำกัดข้างต้น ไม่รับ JPEG/WebP ตรงเข้า endpoint อัปโหลด รุ่นนี้ทดสอบ Chrome/Chromium; Safari, Firefox และมือถือจริงยังไม่ทดสอบ

## SQL ที่เสนอสำหรับ Angsila

ไฟล์ **`database/angsila-database-product-photos-migration.sql`** ใช้กับฐาน `s68160207` ที่ยังมี CLOZEE เดิม 10 ตารางเท่านั้น ตรวจชื่อฐานใน phpMyAdmin ก่อน import

| การเปลี่ยนแปลง | รายละเอียด |
| --- | --- |
| ALTER `clozee_products` | เพิ่ม `brand VARCHAR(100) NULL`, `chest_cm DECIMAL(4,1) NULL`, `length_cm DECIMAL(4,1) NULL`, `condition_notes VARCHAR(1000) NULL` |
| CHECK ขนาดเสื้อ | รอบอก/ความยาวเป็น NULL หรือมากกว่า 0 ถึง 300 ซม. |
| CREATE `clozee_product_images` | 10 ฟิลด์ รวม `image_data MEDIUMBLOB NOT NULL`; FK สินค้า ON DELETE CASCADE |
| CHECK รูป | ขนาด 1–1,572,864 bytes ตรงกับ `OCTET_LENGTH(image_data)`; กว้าง/สูง 1–1,600 |
| Index | UNIQUE storage_name และ `(product_id, sort_order, image_id)` |

หลังเปลี่ยนจะเป็น **11 ตาราง / 89 ฟิลด์ / 15 foreign keys / 9 CHECK constraints** ไม่มี DROP, DELETE, TRUNCATE, CREATE DATABASE หรือ seed ใน SQL นี้ ข้อมูลสินค้าเดิมทั้ง 4 คอลัมน์เริ่มเป็น NULL และยังใช้ภาพสาธิตเดิมได้ ไม่เปลี่ยนราคา สต็อก คำสั่งซื้อ หรือ Lab tables

`database/angsila-schema.sql` เป็น full schema สำหรับ namespace ว่าง ไม่ใช้ทับฐานเดิม ไฟล์ `angsila-product-photos-migration.sql` และ `migrations/001-product-photos.sql` เป็นแบบ private-file ที่ยังไม่ได้ติดตั้งบน Angsila ห้ามใช้ร่วมกับ SQL ใหม่นี้

สำหรับเครื่อง local ที่เคยติดตั้งตารางรูปแบบไฟล์แล้ว ให้สำรองก่อนใช้ `php scripts/migrate-existing-photo-files.php --apply-local-files` สคริปต์ตรวจชื่อ/ขนาด/MIME/มิติเดิม คัดลอก binary เข้า BLOB และตรวจความยาว โดยเก็บไฟล์ต้นฉบับไว้ ใช้ในเครื่องแล้ว 11 รูปใน namespace หลักและ 0 รูปในอีกสาม namespace ไม่ใช้สคริปต์นี้กับ Angsila รุ่นเดิมที่ยังไม่มีตารางรูป

## ผลทดสอบที่รันจริง

Local PHP 8.5.12 / MariaDB 11.4.9:

| ชุด | ผ่าน / ไม่ผ่าน |
| --- | --- |
| ระบบ/DB namespace หลัก | 57 / 0 |
| ระบบ/DB namespace `clozee_` | 57 / 0 |
| ตรวจภาพ โดยเปิด GD | 23 / 0 |
| ตรวจภาพ โดยปิด GD และ EXIF จริง (`php -n` + เฉพาะ extension ที่จำเป็น) | 23 / 0 |
| Browser รูปสินค้า โดยเปิด GD | 23 / 0 |
| Browser รูปสินค้า โดยปิด GD/EXIF | 23 / 0 |
| Browser ระบบเดิม โดยปิด GD/EXIF | 24 / 0 |
| อัปเกรด fixture schema เดิม 10 ตารางพร้อมสินค้า/คำสั่งซื้อ | 14 / 0 |

เพิ่มการทดสอบ binary round-trip/rollback, CHECK ขนาด BLOB, สอง process เพิ่มรูปร่วมกันไม่เกิน 4, ไฟล์ปลอม/CRC/filter/ข้อมูลยุบอัดผิดขนาด, EXIF ใน browser, WebP, resize, สิทธิ์/CSRF และสต็อก ภาพหน้าจอที่ 1440px และ 390px พร้อม JSON อยู่ใน `evidence/database-product-photos/`

ชุด browser เดิมเคยรอ `decode()` ของ lazy images ที่อยู่นอกหน้าจอจนค้าง จึงแก้ตัวทดสอบให้โหลดภาพทุกภาพก่อนจับ screenshot แล้วรันใหม่ผ่าน 24 ข้อ ไม่เปลี่ยน lazy loading ของเว็บไซต์ ใช้ Playwright เนื่องจากไม่มี Browser plugin สำหรับงานทดสอบนี้

CI เพิ่ม matrix เปิด GD และปิด GD/EXIF โดยตรวจว่า extension ถูกปิดจริง แต่ต้องตรวจ run ของ commit นี้ก่อนสรุปผล CI ผล Local ไม่ใช่ผลทดสอบบน Angsila

## ผลกระทบและเงื่อนไขก่อนติดตั้ง

- รูปเพิ่มขนาดฐานข้อมูลสูงสุดประมาณ 6 MiB ต่อสินค้าหากครบ 4 รูป ต้องตรวจ quota กับผู้ดูแล ไม่พบข้อมูล quota ในเอกสารหรือผลตรวจเดิม การ export BLOB ทำให้ไฟล์ backup ใหญ่ขึ้น
- ต้องมี `pdo_mysql`, `mbstring`, `session`, `fileinfo`, `zlib` และ `max_allowed_packet` อย่างน้อย 2 MiB ไม่ต้องมี GD/EXIF หรือ private upload directory ที่ PHP เขียนได้ ต้องตรวจ zlib/limits/packet บน Angsila ก่อนติดตั้ง
- สำรอง 10 ตาราง `clozee_` ผ่าน phpMyAdmin และดาวน์โหลดไฟล์เดิมผ่าน SFTP ไปเก็บในพื้นที่ private ที่ Git ไม่ติดตาม เพราะ PHP สร้าง backup ใน home ไม่ได้ ห้ามเผยแพร่ dump หรือ configuration สำรองซึ่งอาจมีข้อมูลบัญชี
- เทียบ schema/row counts ก่อนและหลัง โดยตรวจ Lab เดิมแยกต่างหาก ไม่ใช้ demo seed บนฐานจริง
- MySQL DDL อาจ auto-commit หากล้มเหลวให้หยุดตรวจ ไม่รันซ้ำอัตโนมัติ ไม่ลบตาราง/คอลัมน์หรือ restore ทับข้อมูลจริงโดยไม่ได้รับอนุมัติ
- หลัง SQL ใหม่ได้รับอนุมัติ จึงสำรอง ติดตั้งโค้ดที่ผ่าน CI และทดสอบอัปโหลด/เปลี่ยน/ลบรูป สองบัญชี checkout สต็อก และมือถือบน URL จริง ก่อนอัปเดตรายงาน PDF และประกาศใช้ฟีเจอร์นี้

**ขอบเขตการอนุมัติรอบนี้คือปรับในเครื่อง ทดสอบ และเสนอ SQL ใหม่ จึงยังไม่เปลี่ยนไฟล์หรือฐานข้อมูล Angsila**
