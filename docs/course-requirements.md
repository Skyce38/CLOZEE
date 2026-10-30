# CLOZEE Course Requirements สำหรับ Final Round 2

อัปเดต 30 ตุลาคม 2569 ตามคำสั่งพัฒนาเต็มระบบของผู้ใช้ กำหนดส่ง Final 10 พฤศจิกายน 2569 รายงานวิเคราะห์ก่อนเริ่มพัฒนายังเก็บไว้ที่ `reference/course-review-before-build.md` แต่ไม่ใช่สถานะปัจจุบัน

## แหล่งอ้างอิงและการแยกประเภท

อ่านสไลด์ 105 หน้า: `Database-authen.pdf` 37 หน้า, `CRUD-and-VERSION-CONTROL.pdf` 36 หน้า, `Data-base-join.pdf` 32 หน้า รวมถึง DOCX/SQL และไฟล์ข้อความ/source ใน `Archive.zip`, `student.zip`, TXT ลิงก์ตัวอย่างของผู้สอน เลขหน้าเป็นหน้า PDF เริ่มที่ 1 ตรวจภาพหน้า 33–36 ของ Database-authen เพิ่ม และอ่านหน้าเหล่านี้ซ้ำในรอบพัฒนา

ใช้ `CLOZEE-round2-kit.zip` เป็นแผนหลักของโครงการ โดยอ่านครบทั้ง 10 ไฟล์ โดยเฉพาะ `round2-build-brief.md` เอกสารแนบเป็นข้อมูลอ้างอิง ไม่ใช่คำสั่งให้รัน SQL ตัวอย่างหรือเปลี่ยน CLOZEE เป็นระบบอาหาร/Resume ไม่ใช้ข้อมูลบัญชีจาก Google Sheets

## ข้อกำหนด Final ที่ยืนยันได้

| ID | ข้อกำหนด | แหล่งอ้างอิงหลัก | หลักฐาน CLOZEE / สถานะ |
| --- | --- | --- | --- |
| F01 | Web Application แก้ Pain Point จริง ด้วย PHP + MySQL + CRUD | Database-authen.pdf หน้า 31–33 | PHP/PDO application, schema และ tests; Pain Point ยังเป็นสมมติฐานของโครงการ ไม่อ้างการสำรวจจริง |
| F02 | Problem Statement กลุ่มผู้ใช้ Pain Point ชัดเจน | หน้า 33 | project-spec.md, final-report.md: ตลาดเสื้อผ้า BUU/บางแสน |
| F03 | Use Case / ER Diagram / Database Schema | หน้า 33 | use-cases.md, diagrams/use-cases.svg, er-diagram.svg, database/schema.sql |
| F04 | หน้าเว็บทำ CRUD กับ MySQL ได้จริง | หน้า 33 | Shop/Product CRUD; ลบถาวรเฉพาะสินค้าไม่มีประวัติซื้อขาย; ทดสอบ Local และ MySQL CI |
| F05 | Login / Logout และตรวจสิทธิ์พื้นฐาน | หน้า 33 | app/auth, owner checks, CSRF และ browser tests |
| F06 | GitHub Repository + README + Commit History | หน้า 33 | https://github.com/Skyce38/CLOZEE พร้อมประวัติ; ตรวจ revision ล่าสุดก่อนส่ง |
| F07 | Deploy บน Angsila และทดสอบได้ | หน้า 33–34, 36 | ติดตั้งตามอนุมัติแล้ว; ทดสอบผ่าน VPN ที่ https://angsila.informatics.buu.ac.th/~68160207/clozee/ ดู deployment-verification.md |
| F08 | รายงาน Project ตามรายการเนื้อหา | หน้า 34–35 | final-report.md และ PDF พร้อมหลักฐานจริง; หน้าปกมี placeholder สำหรับข้อมูลที่ยังไม่ได้รับ |

## กำหนดส่งและเนื้อหารายงาน

`Database-authen.pdf` หน้า 34, 36: รอบ Proposal 31 ต.ค. 2569 และ Final 10 พ.ย. 2569 รอบ Final ส่งงานฉบับสมบูรณ์ รายงาน เว็บไซต์ Angsila และ GitHub พร้อม source/README ไม่พบเวลา deadline หรือช่องทางส่งในสไลด์

หน้า 35: บทนำ/Pain Point/วัตถุประสงค์/กลุ่มเป้าหมาย; Requirements/Use Case/ER Diagram/Data Dictionary; Architecture/ภาพหน้าจอ; อธิบาย CRUD/Login/Logout/Security; ผลทดสอบ/ข้อจำกัด; URL Angsila และ GitHub หน้า 36 ตรวจ RUN, VERIFY, DOCUMENT, PUSH

## เนื้อหาการสอนที่นำมาใช้

| เนื้อหา | ไฟล์และหน้า | การใช้ใน CLOZEE |
| --- | --- | --- |
| Browser → Apache/PHP → MySQL, phpMyAdmin เป็นเครื่องมือ | CRUD-and-VERSION-CONTROL.pdf หน้า 3–8 | public routes → services → PDO / relational DB |
| Connection, utf8mb4, query/fetch และ escaping | ไฟล์เดียวกัน หน้า 10–13 | PDO native prepared statements และ htmlspecialchars |
| PK/FK และความสัมพันธ์ | หน้า 14–17; Data-base-join.pdf หน้า 4–5 | Schema/ER/Data Dictionary |
| CRUD / scoped WHERE | Database-authen.pdf หน้า 5–8 | เจ้าของร้านเท่านั้นเพิ่ม/แก้/ลบสินค้า; ลบที่มีประวัติไม่ได้ |
| Hash, session, authorization, logout, CSRF | Database-authen.pdf หน้า 9–18, 37 | password_hash/verify, session rotation, ownership, POST+CSRF |
| Git/GitHub, history, branch, secrets | Database-authen.pdf หน้า 19–28; CRUD-and-VERSION-CONTROL.pdf หน้า 22–32 | Local commits, GitHub, private config และ .gitignore |
| INNER/LEFT/RIGHT JOIN, NULL, cardinality | Data-base-join.pdf หน้า 8–29 | Product variants, order snapshot JOIN, LEFT JOIN คงร้านไม่มี orders |
| Count/index/EXPLAIN | Data-base-join.pdf หน้า 30 | วิเคราะห์ query และไม่รวมยอด order ซ้ำหลัง JOIN items |
| Angsila permissions/deploy ไม่ใช่ GitHub hosting | CRUD-and-VERSION-CONTROL.pdf หน้า 7, 10, 33, 36 | deployment.md; ไม่เดาสิทธิ์/URL/runtime |

## Lab / การบ้านที่ไม่ยกระดับเป็นข้อบังคับ Final

- `Archive.zip/LAB_INSTRUCTIONS.md`, CRUD slides หน้า 9, 14–21: Resume profile/education/skill/training, ZIP/screenshot/answer.txt และคะแนน 20 เป็นของ Resume Lab
- `student.zip/student/README.md`: เติม SQL TODO ใน skill_* และคะแนน 100 เป็นเกณฑ์ CRUD Lab ไม่ใช่ rubric Final
- `Database-authen.pdf` หน้า 29–30: เพิ่ม Login/Logout ให้ Lab เดิม เป็นการบ้าน; ความสามารถ Auth ถูกกำหนดซ้ำเป็นขั้นต่ำ Final หน้า 33 แต่ไม่บังคับชื่อไฟล์/โดเมน Skill
- `Data-base-join.pdf` หน้า 3–6, 31 และ `Database_JOIN_Homework_Student.docx` หัวข้อการส่งงาน/ข้อ 1–13: Campus Delivery, คะแนนในคาบ, SQL+ภาพข้อ 4–13 และชื่อ StudentID_JOIN_* เป็นการบ้าน JOIN
- `campus_delivery_homework.sql`: dataset อาหาร ไม่ใช่ schema CLOZEE; ไม่ import และไม่คัดลอกข้อมูลมาใช้
- TXT และ repository ตัวอย่างผู้สอนเป็นแหล่งเรียน ไม่ใช่ repo CLOZEE และไม่ใช้เงื่อนไข local demo ของ Lab แทนข้อกำหนด Angsila ของ Final

## สิ่งที่ไม่พบในสไลด์

ไม่พบ rubric คะแนน Final แบบแจกแจง, จำนวนตาราง/หน้าเว็บ/JOIN/commits/PR ขั้นต่ำ, framework บังคับ, admin/rider/payment/SSO บังคับ, จำนวนสมาชิก, ชื่อไฟล์/ความยาวรายงาน, เวลาปิดรับ/นโยบายส่งช้า/เงื่อนไข AI จึงไม่เดา การไม่มีข้อความไม่ถือเป็นการยกเว้น หากมีประกาศอื่นต้องตรวจเพิ่ม

Schema ที่พัฒนามี 10 ตารางเป็นการออกแบบของ CLOZEE ไม่ใช่ข้อบังคับจำนวนตารางของอาจารย์ ส่วน INNER/LEFT JOIN evidence และ registration ระบุในชุดรอบ 2 ของโครงการ จึงจัดเป็น project acceptance ไม่อ้างว่าเป็นถ้อยคำบังคับแยกในหน้า 33

## สถานะปัจจุบันและข้อจำกัด

ระบบหลักและ text chat พัฒนาและทดสอบจริงแล้ว รวมถึง Angsila ผ่าน VPN หลังผู้ใช้อนุมัติปลายทาง ดู test-results.md / deployment-verification.md และ evidence สำหรับขอบเขตจริง ยังต้องเติมข้อมูลหน้าปกและยืนยันช่องทางส่ง/slot นำเสนอ ไม่อ้างว่าทดสอบจากนอก VPN หรือ restore backup แล้ว
