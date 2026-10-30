# CLOZEE — วิเคราะห์ข้อกำหนดวิชา Database Management ก่อน Phase 1

วันที่ตรวจ: 30 ตุลาคม 2569 (2026), Asia/Bangkok  
สถานะ: วิเคราะห์เอกสารเท่านั้น ยังไม่เริ่ม Development Phase 1

## 1. ข้อสรุปและขอบเขตการตรวจ

CLOZEE ต้องคงเป็นระบบซื้อขายเสื้อผ้าสำหรับ BUU และบางแสน ตามคำขอของผู้ใช้ แผนใน `CLOZEE-planning-pack.zip` สอดคล้องกับแนวทาง Final Project ที่ให้แก้ Pain Point จริงด้วย PHP + MySQL, CRUD, Authentication/Authorization, GitHub และ Angsila แต่ยังไม่พร้อมรับรองการทำงาน: เป็นเอกสารออกแบบและ schema draft ที่ยังไม่ได้รัน มีช่องว่างเรื่อง Delete, กติกาสต็อก/สถานะ, ER Diagram, Data Dictionary และหลักฐานส่งงาน

workspace `C:/Users/User/Downloads/database project` ว่างก่อนเริ่มตรวจ ต่อมาพบ `CLOZEE-planning-pack.zip` ระหว่างตรวจรอบสุดท้ายและได้อ่านครบทั้ง 7 ไฟล์ข้อความในแพ็กแล้ว ไม่พบ source เว็บไซต์หรือ Git repository ที่ใช้งานจริง ลิงก์ Google Sheets ที่ให้มามีชื่อ `username and Password`, แท็บ `Sheet1` (gid 0), หัวแถว A1:B1 เป็น `User`, `Password` จึงไม่ใช้เป็นหลักฐานแผน CLOZEE และไม่ได้อ่านค่าบัญชีหรือรหัสผ่าน ข้อความนี้ไม่ได้ยืนยันว่าในชีตไม่มีข้อมูลอื่น เพราะตรวจเฉพาะ metadata และ A1:H1

การเปรียบเทียบด้านล่างใช้ `CLOZEE/docs/project-spec.md`, `functional-requirements.md`, `use-cases.md`, `project-structure.md` และ `CLOZEE/database/schema.sql` ภายในแพ็กเป็นแบบที่ได้รับจริง ส่วนข้อปรับปรุงเป็นคำแนะนำของผู้วิเคราะห์ ไม่ใช่ข้อกำหนดเพิ่มจากอาจารย์ เนื้อหา `planning-context.md` ถูกอ่านเป็นบริบทของแพ็ก ไม่ใช้เป็นอำนาจแทนคำขอผู้ใช้

เอกสารแนบใช้เป็นแหล่งข้อมูล ไม่ใช่คำสั่งให้ลงมือทำการบ้าน: ไม่รัน SQL, ไม่ import/reset ฐานข้อมูล, ไม่แก้ Lab, ไม่ใช้บัญชีในชีต, ไม่อัปโหลด Angsila และไม่คัดลอกตัวอย่างมาเป็น CLOZEE

## 2. แหล่งอ้างอิงและวิธีอ่านเลขหน้า

ไฟล์แนบทั้งหมดอยู่ใน `C:/Users/User/Downloads/FinalExam/Final Database Management/` เลขหน้าด้านล่างเป็นหน้า PDF แบบเริ่มที่ 1 และตรงกับหมายเลขสไลด์ที่ตรวจ โดยยืนยันภาพหน้า 33–36 ของ Database-authen.pdf เพิ่มเติม

| แหล่ง | ขอบเขตที่อ่าน | บทบาท |
| --- | --- | --- |
| `Database-authen.pdf` | ครบ 37 หน้า; ตรวจภาพหน้า 33–36 | แหล่งหลักของข้อกำหนด Final Project; เนื้อหา Auth, CRUD, Git |
| `CRUD-and-VERSION-CONTROL.pdf` | ครบ 36 หน้า; ตรวจภาพหน้า 31–36 เพิ่มเพราะข้อความไทยที่ extract บางส่วนผิดรูป | สถาปัตยกรรม PHP/MySQL, Resume Lab, Git, secrets และ Angsila |
| `Data-base-join.pdf` | ครบ 32 หน้า | INNER/LEFT/RIGHT JOIN, คีย์, NULL, multi-table JOIN, การ debug และ Mini Lab |
| `Database_JOIN_Homework_Student.docx` | ย่อหน้าและตารางทั้งหมด | การบ้าน Campus Delivery ข้อ 1–13 และรูปแบบส่ง; อ้างหัวข้อแทนเลขหน้าเนื่องจาก DOCX แบ่งหน้าตามโปรแกรมแสดงผล |
| `campus_delivery_homework.sql` | ทั้งไฟล์ | DDL, FK, index และข้อมูลจำลองของการบ้าน JOIN |
| `Archive.zip` | รายการไฟล์และไฟล์ข้อความ/source ทั้งหมดที่เกี่ยวข้อง | Resume starter: PHP/CSS/SQL และ `LAB_INSTRUCTIONS.md`; รูป student.jpg/metadata ไม่ใช่ข้อกำหนด |
| `student.zip` | รายการไฟล์, PHP/CSS/SQL, README และ .gitignore | CRUD starter ที่มี TODO; ไม่อ่านค่า `.env` และไม่รันโค้ด |
| `httpsgithub.comwyookwan69-database.txt` | ทั้งไฟล์ 2 URL | ชี้ไปยังตัวอย่าง GitHub และ Angsila ของผู้สอน ไม่ใช่ repo/URL ของ CLOZEE |
| `CLOZEE-planning-pack.zip` ใน workspace | ครบทั้ง README, docs 5 ไฟล์ และ schema.sql | แบบ CLOZEE ที่ได้รับจริง: spec v0.3, FR/NFR, UC, schema draft 8 ตาราง และ target structure |
| [Google Sheet ที่ผู้ใช้ให้](https://docs.google.com/spreadsheets/d/1gYP3ceHwuokGuNhedkO0bV6sdqPU0xxD7n8BBifz8eg/edit?gid=0#gid=0) | metadata และ `Sheet1!A1:H1` เท่านั้น | ยังไม่มีหลักฐานแผน CLOZEE จากส่วนที่ตรวจ |

ได้เปิด [repository ตัวอย่างของผู้สอน](https://github.com/wyookwan/69-database) และอ่าน README: เป็น Resume CRUD ต่อด้วย Login/Logout และยังเป็น student starter การกำหนดอย่างน้อย 2 commits, password อย่างน้อย 10 ตัว และทางเลือก demo local ใน README นี้เป็นบริบท Lab ไม่ยกระดับเป็นข้อกำหนด Final Project ส่วน URL Angsila ในไฟล์ TXT เปิดผ่านเครื่องมือเว็บไม่สำเร็จ จึงไม่ยืนยันสถานะเว็บไซต์หรือสิทธิ์ deployment จาก URL นั้น

ลำดับน้ำหนักหลักฐาน: ข้อกำหนด Final Project ใน `Database-authen.pdf` หน้า 31–36 > เนื้อหาการสอนเพื่อออกแบบ/รักษาความปลอดภัย > README/Lab/การบ้านตัวอย่าง คำแนะนำในรายงานนี้ระบุแยกจากข้อบังคับเสมอ

## 3. ข้อกำหนด Final Project ที่ยืนยันได้

### 3.1 องค์ประกอบขั้นต่ำ

| ID | ข้อกำหนดที่พบจริง | หลักฐาน | สิ่งที่ CLOZEE ต้องมีให้ตรวจได้ |
| --- | --- | --- | --- |
| F01 | Web Application ตอบ Pain Point จริง; ใช้ PHP + MySQL + CRUD | `Database-authen.pdf` หน้า 31–33 | ขอบเขตเว็บและข้อมูลจริงใน MySQL ไม่ใช่หน้า mockup อย่างเดียว |
| F02 | Problem Statement, กลุ่มผู้ใช้, Pain Point ชัดเจน | หน้า 33 | อธิบายปัญหาของผู้ซื้อ/ผู้ขายเสื้อผ้าใน BUU–บางแสน; หลักฐานปัญหายังต้องรวบรวม |
| F03 | Use Case, ER Diagram, Database Schema | หน้า 33 | เอกสารออกแบบที่เชื่อมกันและตรงกับฐานข้อมูลที่จะใช้งาน |
| F04 | หน้าเว็บทำ CRUD กับ MySQL ได้จริง | หน้า 33 | สาธิตเพิ่ม อ่าน แก้ไข ลบข้อมูลของโดเมน CLOZEE และตรวจค่าที่บันทึกจริง |
| F05 | Login/Logout และตรวจสิทธิ์ขั้นพื้นฐาน | หน้า 33 | Session และสิทธิ์ระดับผู้ใช้/เจ้าของข้อมูล; Login อย่างเดียวไม่ครบ |
| F06 | GitHub Repository, README, Commit History | หน้า 33 | repo ของ CLOZEE, วิธีติดตั้ง/ทดลอง และประวัติการพัฒนา |
| F07 | Deploy เว็บบน Angsila และทดสอบได้ | หน้า 33–34, 36 | URL CLOZEE ที่ใช้งานได้จริงบน Angsila |
| F08 | ส่งรายงาน Project พร้อมรายละเอียดตามหัวข้อ | หน้า 34–35 | รายงานครบตามรายการในหัวข้อ 3.3 |

PHP + MySQL ระบุในแนวทาง Final Project หน้า 31 โดยตรง แต่ไม่พบข้อบังคับให้ใช้ `mysqli` แทน PDO หรือให้ใช้ framework ใดเป็นพิเศษ ตัวอย่างในชั้นใช้ `mysqli` เป็นหลัก (`CRUD-and-VERSION-CONTROL.pdf` หน้า 6, 10–13)

### 3.2 กำหนดส่งสองรอบ

| รอบ | วันที่ตามสไลด์ | สิ่งที่ส่ง |
| --- | --- | --- |
| Proposal | 31 ต.ค. 2569 / 31 October 2026 | หัวข้อและ Pain Point, กลุ่มผู้ใช้และ Solution, ขอบเขตฟีเจอร์และ Draft ER Diagram, Project Plan และ GitHub Repo |
| Final delivery | 10 พ.ย. 2569 / 10 November 2026 | งานฉบับสมบูรณ์, รายงาน Project, เว็บไซต์ทำงานบน Angsila, GitHub Repo พร้อม source และ README |

อ้างอิง `Database-authen.pdf` หน้า 34 และ 36 ไม่พบเวลาปิดรับ ช่องทางส่ง ชื่อไฟล์เฉพาะ รูปแบบไฟล์รายงาน หรือเงื่อนไขส่งช้าในเอกสารที่ตรวจ ณ วันที่วิเคราะห์ 30 ต.ค. รอบ Proposal เป็นวันถัดไป จึงควรให้เอกสารเสนอหัวข้อและ ER draft มาก่อนเพิ่มฟีเจอร์เสริม

### 3.3 รายงานและ checklist ก่อนส่ง

`Database-authen.pdf` หน้า 35 ระบุเนื้อหารายงานดังนี้:

1. บทนำ: Pain Point, วัตถุประสงค์ และกลุ่มเป้าหมาย
2. Requirements, Use Case, ER Diagram และ Data Dictionary
3. Architecture และภาพหน้าจอการทำงาน
4. คำอธิบาย CRUD, Login/Logout และ Security
5. ผลทดสอบและข้อจำกัดของระบบ
6. URL เว็บ Angsila และ GitHub Repository

หน้า 36 กำหนดตรวจว่าเว็บ Angsila เข้าได้, CRUD + Auth ทำงาน, รายงาน + README ครบ และ GitHub เป็นเวอร์ชันล่าสุด ไม่พบ rubric คะแนนแยกหัวข้อของ Final Project จึงไม่ใช้คะแนนจาก Lab มาแทน

## 4. สิ่งที่อาจารย์สอนและการนำมาใช้

| หัวข้อ | สาระที่สอน | อ้างอิง | ผลต่อแนวทาง CLOZEE |
| --- | --- | --- | --- |
| PHP และ request/response | Browser → Apache → PHP → MySQL → HTML; PHP ทำงานฝั่ง server; phpMyAdmin เป็นเครื่องมือจัดการ DB | `CRUD-and-VERSION-CONTROL.pdf` หน้า 3–8; `Database-authen.pdf` หน้า 3–4 | วาง backend PHP และฐานข้อมูลจริง; ไม่ให้ browser ต่อ MySQL โดยตรง |
| Connection / render | เชื่อมด้วย mysqli, utf8mb4, query/fetch rows, escape ข้อความด้วย htmlspecialchars | `CRUD-and-VERSION-CONTROL.pdf` หน้า 10–13 | รองรับภาษาไทยและ escape ข้อมูลชื่อสินค้า/คำอธิบายเมื่อแสดงผล |
| Relational design | PK/FK, ความสัมพันธ์ 1:N, ชนิดข้อมูลและ sample data | ไฟล์เดียวกัน หน้า 14–17; `Data-base-join.pdf` หน้า 4–5 | ออกแบบคีย์และความสัมพันธ์ของเสื้อผ้า ผู้ขาย หมวดหมู่ และรูปภาพเอง |
| CRUD | SELECT/INSERT/UPDATE/DELETE เป็น data flow; prepared statements และ WHERE จำกัดเจ้าของ | `Database-authen.pdf` หน้า 5–8 | เลือกข้อมูลหลัก เช่นประกาศขาย ให้ทำ CRUD ครบจากเว็บ |
| Authentication | users, email unique, password_hash, password_verify, session และ session_regenerate_id หลัง login | `Database-authen.pdf` หน้า 9–15 | เก็บ hash แทน plaintext และยืนยันตัวตนผ่าน session |
| Authorization / logout | ป้องกันหน้า, ตรวจเจ้าของข้อมูล, ล้าง session และจัดการ session cookie | หน้า 10, 16–18, 37 | ผู้ใช้ A ต้องแก้/ลบประกาศของ B ไม่ได้ แม้ทราบ URL หรือ id |
| Security | prepared statements เมื่อรับ input, CSRF token สำหรับ form เปลี่ยนข้อมูล, hash password, owner check | หน้า 18; `CRUD-and-VERSION-CONTROL.pdf` หน้า 13, 31–32 | เป็นแนวทางคุณภาพจากการสอน ไม่ใช่รายการคะแนน Final ที่แจกแจงแยก |
| JOIN | INNER เก็บคู่ตรงกัน, LEFT เก็บฝั่งซ้าย, RIGHT เก็บฝั่งขวา, IS NULL และสลับ RIGHT เป็น LEFT | `Data-base-join.pdf` หน้า 8–27 | เลือก JOIN ตามผลลัพธ์ที่ต้องการ ไม่บังคับใช้ทุกชนิดเพื่อให้ครบชื่อ |
| JOIN หลายตาราง / debug | ระวัง 1:N เพิ่มจำนวนแถว, ON ผิด, WHERE ตัด NULL; ใช้ count, index และ EXPLAIN | หน้า 9, 16, 28–30 | หลีกเลี่ยงสินค้า/ยอดรวมซ้ำเมื่อ JOIN รูปหลายรูปหรือรายการซื้อ |
| Git / GitHub | local history, staging, commit, push/pull, branch, merge และ PR; ไม่ push secrets | `Database-authen.pdf` หน้า 19–28; `CRUD-and-VERSION-CONTROL.pdf` หน้า 22–32 | repo เฉพาะ CLOZEE และ commit สื่อความหมาย; branch/PR เป็นวิธีทำงานที่สอน ไม่พบจำนวน PR ขั้นต่ำ |
| Angsila | Apache/PHP/MySQL; localhost ขึ้นกับเครื่องที่รัน PHP; ต้องตรวจบัญชีจริงและวิธี deploy ที่ได้รับอนุญาต | `CRUD-and-VERSION-CONTROL.pdf` หน้า 4, 7, 10, 33, 36 | GitHub ไม่ทำให้ PHP deploy บน Angsila อัตโนมัติ ต้องมีแผนติดตั้งแยก |

## 5. สิ่งที่เป็น Lab หรือการบ้าน ไม่ใช่ข้อกำหนด CLOZEE

| เนื้อหา | ที่มา | การจัดประเภท |
| --- | --- | --- |
| Resume: profile, education, skill, training; เปลี่ยนรูปตัวเองและแต่งหน้าเว็บ | `Archive.zip/LAB_INSTRUCTIONS.md`; `CRUD-and-VERSION-CONTROL.pdf` หน้า 9, 14–21 | Lab ฝึก PHP/MySQL ไม่ใช่โดเมนหรือตารางบังคับของ Final |
| ZIP `studentid_resume_lab.zip`, รูปตัวเอง, screenshot, answer.txt และ Suggested Score 20 | `Archive.zip/LAB_INSTRUCTIONS.md` หัวข้อ Deliverables, Questions, Suggested Score | รูปแบบส่งและคะแนนเฉพาะ Resume Lab |
| เติม TODO INSERT/SELECT/UPDATE/DELETE ใน skill_*; คะแนน 25/30/25/10/10 รวม 100 | `student.zip/student/README.md` และไฟล์ skill_*.php | CRUD exercise ไม่ใช่ rubric Final |
| เพิ่ม users/login/logout ให้ Lab, ทดสอบ Login ถูก/ผิดและ logout | `Database-authen.pdf` หน้า 29–30 | การบ้าน Auth; ความสามารถ Auth ถูกกำหนดซ้ำเป็นขั้นต่ำ Final ที่หน้า 33 แต่ไม่ต้องใช้ชื่อหน้า/ข้อมูล Skill เดิม |
| Campus Delivery และ JOIN INNER/LEFT/RIGHT, mini lab 5+5+5 คะแนนและโบนัส | `Data-base-join.pdf` หน้า 3–6, 31 | กรณีศึกษาส่งอาหารและคะแนนในคาบ ไม่ใช่ให้ CLOZEE เป็นระบบส่งอาหาร |
| การบ้านข้อ 1–13, screenshot ข้อ 4–13, `StudentID_JOIN_Homework.sql`, `StudentID_JOIN_Result.pdf` | `Database_JOIN_Homework_Student.docx` หัวข้อข้อกำหนดการส่งงาน/ส่วนที่ 1–3 | รูปแบบส่งการบ้าน JOIN ไม่ใช่ Final |
| students/restaurants/orders/order_items และชุดข้อมูลตัวอย่าง | `campus_delivery_homework.sql` | ฝึกความสัมพันธ์เท่านั้น ไม่ใช้ dataset หรือ schema นี้เป็น CLOZEE |
| ตัวอย่างจองห้องออนไลน์ | `Database-authen.pdf` หน้า 32 | ตัวอย่าง Pain Point → Solution ไม่ใช่หัวข้อบังคับ |
| Resume Auth repo และเว็บผู้สอนจาก TXT | [GitHub README](https://github.com/wyookwan/69-database); ไฟล์ TXT | แหล่งเรียน ไม่ใช่ repo/เว็บ CLOZEE; ทางเลือก local demo ของ Lab ไม่แทนข้อกำหนด Angsila ใน Final |

### ข้อสังเกตจาก starter ที่ไม่ควรนำไปใช้ตรง ๆ

- `Archive.zip/index.php` ยังใช้ array ตัวอย่างใน Skills/Training และ `config/db.php` มีค่า root/รหัสผ่านว่างสำหรับ local; ไม่ใช่ระบบพร้อมส่ง
- `student.zip/student/skill_create.php`, `skill_edit.php`, `skill_delete.php` ยังมี SQL placeholder `SELECT 1` และ TODO จึงไม่ใช่ CRUD ที่เสร็จแล้ว
- `student.zip/student/crud_bootstrap.php` เลือก profile แรกจาก DB โดยไม่มีผู้ใช้ที่ยืนยันตัวตน README ก็ระบุว่าเป็น unauthenticated classroom example; session สำหรับ CSRF ไม่เท่ากับระบบ Login
- `student.zip` มี `.env` แต่ `.gitignore` ระบุเพียง `*.log` และ `.DS_Store` จึงไม่กัน `.env`; ไม่ได้อ่านค่าหรือยืนยันว่าเป็น secret จริง `config/db.php` ใช้ getenv แต่ไม่พบตัวโหลด `.env` ใน PHP ที่ตรวจ การวางไฟล์ `.env` อย่างเดียวจึงยังไม่ใช่หลักฐานว่าค่า env ถูกใช้
- SQL ในตัวอย่างมี DROP TABLE และไฟล์ Campus Delivery มี DROP DATABASE ใช้เพื่อเริ่มแบบฝึกหัดใหม่ ไม่ควรนำไปรันบนฐาน CLOZEE การตรวจครั้งนี้อ่านอย่างเดียว
- การ cascade ลบ profile → skill เหมาะกับตัวอย่าง ไม่ควรนำไปใช้ลบประวัติคำสั่งซื้อของตลาดเสื้อผ้าโดยไม่ออกแบบวงจรข้อมูลใหม่

## 6. เปรียบเทียบข้อกำหนดกับแผน CLOZEE ที่ได้รับจริง

อ้างอิงไฟล์ภายใน `CLOZEE-planning-pack.zip` คำว่า “มีในแผน” ไม่ได้แปลว่าพัฒนาหรือทดสอบแล้ว

| ข้อกำหนด | สิ่งที่พบในแผน | ผลตรวจ / สิ่งที่ยังขาด |
| --- | --- | --- |
| F01–F02 ปัญหาจริง/กลุ่มผู้ใช้/stack | project-spec §1–3, §8: BUU/บางแสน เสื้อผ้าใหม่/มือสอง, PHP 8+, MySQL/MariaDB | สอดคล้องเชิงแนวคิด; Pain Point ระบุเองว่าเป็นสมมติฐาน ยังไม่มีผลวิจัยผู้ใช้; PHP 8+ และ MariaDB ต้องยืนยันกับ Angsila และข้อกำหนดรายวิชา ไม่ถือว่าใช้แทน MySQL ได้โดยอัตโนมัติ |
| F03 Use Case / ER / Schema | use-cases มี UC-01–10; schema.sql มี 8 ตารางและ FK | มี UC และ schema แต่ไม่มีภาพ ER Diagram และ Data Dictionary; ต้องทำ ER draft ให้ทัน proposal |
| F04 CRUD กับ MySQL | FR-04/UC-05: add/list/edit/deactivate products | C/R/U วางไว้ชัด แต่ D ใช้ deactivate; หน้า 33 ไม่อธิบายว่า soft delete ผ่านหรือไม่ ต้องกำหนด/ชี้แจงการสาธิต Delete ให้ชัด |
| F05 Login/Logout/สิทธิ์ | FR-01/02/12, NFR-01, ownership ใน UC และ SQL comments | แผนครบแนวหลัก; ยังไม่มี implementation หรือผลทดสอบ; ต้องครอบคลุม seller/buyer/order และการเข้าถึงตรง |
| F06 GitHub/README/history | README ในแพ็ก; project-spec §8; NFR-05 | มี README สำหรับ planning เท่านั้น ยังไม่พบ repo CLOZEE URL, setup README ของเว็บ หรือ history |
| F07 Angsila | spec §8 และ deployment caution ใน project-structure | รับรู้ข้อจำกัดดี แต่ยังไม่มี runtime/webroot/DB settings ที่ยืนยันหรือ URL เว็บ CLOZEE |
| F08 รายงาน/การทดสอบ | project-spec §11 และ NFR-06 | วางหัวข้อตรงหน้า 35; ยังไม่มีรายงานฉบับส่ง ภาพหน้าจอ ผลทดสอบ หรือหลักฐาน deploy |
| Proposal 31 ต.ค. | spec §10 ระบุวันตรงสไลด์ | หัวข้อ/ขอบเขตมีแล้ว แต่ยังไม่มี ER draft แบบภาพ, repo URL และ project plan ที่แจกงาน/เวลา |
| Final 10 พ.ย. | spec §9–10 วาง 4 phases | ไม่มีวันที่/ผู้รับผิดชอบ/เวลาสำรองแต่ละ phase; ไม่ควรรวม testing/deployment ทั้งหมดไว้ Phase 4 |

แพ็กอ้างชื่อสไลด์มี `(1)`/`(2)` แต่ไฟล์ที่ได้รับจริงไม่มี suffix เหล่านี้ รายงานนี้อ้างชื่อไฟล์จริงตามหัวข้อ 2 ส่วน project-spec §10 กล่าวถึง reservation sheet/เวลานำเสนอ 30 ต.ค. แต่ไม่พบเอกสารนั้นในแพ็กหรือไฟล์แนบ และไม่ได้ตรวจชีตข้อมูลบัญชีเพื่อหา slot จึงยังไม่ยืนยันกำหนดนำเสนอหรือใช้เปลี่ยนวันส่ง

### ความสอดคล้องของขอบเขตภายในแพ็ก

- P0 มีบัญชี, ร้านหนึ่งร้านต่อบัญชี, สินค้า/ไซซ์/สต็อก, order ร้านเดียว, pickup/delivery แบบข้อความ และประวัติออเดอร์ จึงยังอยู่ในโดเมนเสื้อผ้า ไม่ใช่ระบบจัดส่งอาหาร
- Chat, หลายรูป และรายงานยอดขายเป็น P1; payment, rider, tracking, shipping API และหลายร้านต่อ checkout ถูกตัดออกแล้ว เหมาะกับการจำกัดงาน ไม่ควรเพิ่มกลับโดยไม่มีเหตุผล
- README/วัตถุประสงค์พูดถึงติดต่อผู้ขาย แต่ chat เป็น P1 ต้องเขียนให้ชัดว่า P0 ติดต่ออย่างไร หรือยังไม่รองรับการสนทนา
- คำว่า seller dashboard เป็น P1 ใน spec §5 แต่ target structure มี seller/dashboard.php และ P0 ต้องมีส่วนจัดการสินค้า/ออเดอร์อยู่แล้ว ควรแยก “หน้าจัดการพื้นฐาน P0” กับ “สรุปยอดขาย P1”
- แผน Phase 1 มี schema/auth เท่านั้น จึงยังไม่ทำให้ครบ Final ซึ่งต้องมี CRUD จาก Phase 2 ด้วย ต้องรักษาเวลาไปจนถึง Phase 3 และหลักฐานส่งงาน

## 7. ตรวจ Database Schema ที่ได้รับ

แหล่ง: `CLOZEE/database/schema.sql` (draft v0.3) ภายในแพ็ก อ่านแบบ static เท่านั้น ไม่ import, ไม่รัน migration และไม่รับรอง compatibility หรือผลทดสอบจริง

### 7.1 โครงสร้างและสิ่งที่ทำได้เหมาะสม

| ตาราง | คีย์และความสัมพันธ์จริง | ผลตรวจ |
| --- | --- | --- |
| users | user_id PK, email UNIQUE, password_hash VARCHAR(255) | รองรับบัญชีและ hash ตามเนื้อหา Auth; phone nullable |
| shops | shop_id PK, owner_user_id FK/UNIQUE → users | บังคับหนึ่งร้านต่อบัญชี; ผู้ใช้ไม่มีร้านได้ จึงเป็น users 1 : 0..1 shops |
| products | product_id PK, shop_id FK → shops; condition_type, image_path, is_active | ร้าน 1:N สินค้า; แยกเสื้อผ้าใหม่/มือสองชัด; มีรูปหลักหนึ่งรูปตาม P0 |
| product_variants | variant_id PK, product_id FK, UNIQUE(product_id,size_label); price, stock_quantity | สินค้า 1:N variants ในแนวคิด; ราคาและ stock อยู่ระดับไซซ์ ไม่ซ้ำที่ products |
| orders | order_id PK, buyer_user_id FK, shop_id FK; method/details/fee/total/status | แยกผู้ซื้อกับร้าน; order ร้านเดียวตรง spec |
| order_items | order_item_id PK, order_id FK, variant_id FK; name/size/price snapshots | รองรับหลายรายการต่อ order และรักษาประวัติหลังเปลี่ยนชื่อ/ไซซ์/ราคา |
| conversations | conversation_id PK, buyer/shop FK, product_id nullable FK | เป็น P1; product ลบแล้ว SET NULL; แยกจาก orders ไม่ต้องสร้าง order ก่อนคุย |
| messages | message_id PK, conversation_id FK, sender_user_id FK | เป็น P1; index conversation/time เหมาะกับอ่าน thread แต่ FK ไม่ตรวจผู้ร่วมสนทนาให้ |

ทุก PK/FK ใช้ BIGINT UNSIGNED สอดคล้องกัน; ใช้ InnoDB, utf8mb4_unicode_ci; มี unique email/เจ้าของร้าน/ไซซ์ต่อสินค้า และ index สำหรับเส้นทางหลัก FK ใช้ RESTRICT เป็นหลักจึงรักษาประวัติ ไม่พบ DROP/CREATE DATABASE หรือคำสั่งลบข้อมูลในไฟล์นี้ แต่ CREATE TABLE ยังชนตารางเดิมได้ จึงไม่ใช่ migration ที่รันซ้ำได้และไม่ควรรันบน DB ที่ยังไม่ตรวจ

แบบข้อมูลแยก entity/variants/order items ค่อนข้างเหมาะสม ไม่คัดลอก schema อาหารมาเปลี่ยนชื่ออย่างเดียว `category` เป็น VARCHAR ใน products: ยอมรับได้ใน MVP หากใช้รายการค่ากลางจาก server; ถ้าต้องบริหารหมวดหมู่/ป้องกันชื่อซ้ำค่อยพิจารณาตาราง categories ไม่ใช่ข้อบังคับให้เพิ่มตาราง

### 7.2 ประเด็นที่ต้องแก้ให้ชัดก่อนเริ่มพัฒนา

| ลำดับ | จุดพบ/ความเสี่ยง | ข้อเสนอแนะ |
| --- | --- | --- |
| 1 | FR-04/UC-05 มี deactivate แต่ไม่ระบุกรณี DELETE จริง; products/variants ถูก RESTRICT เมื่อมีประวัติ | นิยาม lifecycle ให้ชัด: เสนอ hard delete เฉพาะสินค้าที่ไม่เคยมี order โดยลบ variants/รูปอย่างปลอดภัยใน transaction; สินค้ามีประวัติใช้ deactivate รักษา FK อธิบายการสาธิต D และสอบถามอาจารย์หากใช้ soft delete อย่างเดียว |
| 2 | SQL comment ระบุ FOR UPDATE แต่ยังไม่มีโค้ด checkout | ตรวจ active ทั้ง shop/product, variant อยู่ร้านเดียวกับ order, ราคา/fee/total คำนวณจาก server, lock variants ตามลำดับคงที่และ recheck stock ภายใน transaction |
| 3 | มี cancelled แต่ไม่ระบุสิทธิ์/การคืน stock | ทำ status transition matrix แยก pickup/delivery; seller เป็นผู้เปลี่ยนตามแผน; ระบุคืน stock ครั้งเดียวเมื่อ cancel และห้ามแก้ terminal status ซ้ำจนคืนซ้ำ |
| 4 | qty เป็น UNSIGNED จึงยังเป็น 0 ได้; DECIMAL ยังติดลบได้; is_active ยังรับค่าอื่นได้ | validate quantity > 0, ราคา/fee/total >= 0, is_active ใน 0/1 ที่ server; เพิ่ม CHECK ได้เมื่อยืนยัน DB version/การบังคับใช้ ไม่กล่าวว่ามี constraint นี้แล้ว |
| 5 | FK ไม่บังคับ order_items.variant ให้เป็นของ orders.shop_id | บังคับใน OrderService ภายใน transaction; ถ้าต้องการ DB-level guarantee ต้องออกแบบคีย์เพิ่มเติม ไม่อ้างว่า FK ปัจจุบันคุมข้ามร้านครบ |
| 6 | ไม่มี idempotency ของ checkout | ป้องกัน double submit/retry สร้างสอง orders แม้ stock พอ; เลือก token ใช้ครั้งเดียว/unique request key และกำหนด behavior เมื่อส่งซ้ำ |
| 7 | order มี total_amount และ shipping_fee แต่ไม่ระบุสูตรรวมชัด | กำหนด total = SUM(quantity × unit_price) + shipping_fee และที่มาค่าจัดส่ง; snapshots และยอดต้องบันทึก atomically ห้ามรับ total จาก browser |
| 8 | variant ไม่มี is_active และ UNIQUE(product,size) จำกัดรูปแบบไซซ์ | ถ้า variant เคยขายแล้วต้องรักษา FK; เสนอ stock=0/ปิดขายแทนลบ และกำหนดว่าสินค้าเดียวไซซ์เดียวหลายสีรวมกันหรือไม่; สีไม่ใช่ฟีเจอร์ที่ต้องเพิ่มอัตโนมัติ |
| 9 | product มีได้ศูนย์ variants และ order มีได้ศูนย์ items ในระดับ DB | service ต้องสร้าง product+variants และ order+items แบบครบชุดหรือ rollback ไม่แสดง product ที่ไม่มี variant ขายได้ |
| 10 | ไม่ห้ามเจ้าของร้านซื้อของตนเอง/สร้าง conversation กับตัวเอง | ตัดสินกติกาธุรกิจให้ชัด ไม่เดาว่าห้าม; ถ้าห้ามให้เทียบ actor กับ owner จาก DB |
| 11 | scope BUU/บางแสนเป็นคำอธิบายและสถานที่ free text | ระบุวิธีสื่อสารพื้นที่รับส่ง/จุดนัดรับใน P0 ไม่อ้างว่าระบบตรวจพื้นที่อัตโนมัติหรือยืนยันตัวตน BUU ได้ |
| 12 | chat P1: sender FK ยืนยันเพียงว่ามี user; product อาจเป็นของร้านอื่น | เมื่อทำ P1 ต้องตรวจ sender เป็น buyer/owner และ product อยู่ shop ของ conversation; ตัดสินยอม thread ซ้ำหรือไม่ และอัปเดต last_message_at พร้อมข้อความ |

ประเด็นเสริม: กำหนด normalization ของ email/size_label ก่อนตรวจ unique, validation ความยาวตามคอลัมน์, วิธีจัดเก็บวันเวลา และนโยบายแก้/ลบ account ไม่ให้ประวัติสูญหาย หากต้องมีผู้ใช้ทดสอบให้ใช้ seed จำลองและ hash ที่สร้างจากกลไกเดียวกับระบบ ไม่ใส่บัญชีจริง

### 7.3 JOIN และ index สำหรับแบบนี้

- product details: products → shops → users และ product_variants เป็นความสัมพันธ์ที่ใช้งานได้จริง; ห้ามเผย email/phone ของผู้ขายโดยไม่กำหนดสิทธิ์/นโยบาย
- product listing ที่ JOIN variants จะได้หลายแถวต่อ product ต้องเลือก aggregate/min price หรือดึง variants แยกก่อน pagination ไม่ใช้ DISTINCT เพื่อกลบปัญหาทั้งหมด
- order history: orders → order_items → variants/products ใช้ snapshot สำหรับชื่อ/ไซซ์/ราคา ณ วันซื้อ และกรอง buyer จาก session; seller history กรอง shop ที่ผู้ใช้เป็นเจ้าของ
- ร้านที่ยังไม่มี order: shops LEFT JOIN orders โดยใส่เงื่อนไขสถานะใน ON หากต้องคงร้านที่ยังไม่มีผลตรงเงื่อนไข; ระวัง SUM(order.total_amount) ซ้ำเมื่อ JOIN items
- index ที่มีรองรับ buyer/shop history และ condition/active เบื้องต้น; filter ไซซ์และ stock อาจต้อง index เพิ่มตาม EXPLAIN และข้อมูลจริง ไม่เพิ่มจำนวนมากก่อนมี query

อ้างแนวทางตรวจจาก `Data-base-join.pdf` หน้า 4, 9, 16–19, 28–30 ไม่พบข้อบังคับ Final ให้มี 8 ตารางหรือ JOIN ครบทั้งสามชนิด Schema นี้มี 6 ตาราง P0 และ 2 ตาราง P1 ตามแพ็ก การวาง chat schema ล่วงหน้าไม่จำเป็นต้องทำ chat UI ใน Phase 1

## 8. ตรวจ Project Structure ที่ได้รับ

แหล่ง: `CLOZEE/docs/project-structure.md` เป็น target tree ไม่ใช่โค้ดที่สร้างแล้ว โครงสร้างที่วางไว้เหมาะกับ PHP ขนาดเล็ก: แยก public, app/config, auth, services, templates, database, docs และ storage ไม่มีความจำเป็นจากเอกสารรายวิชาที่ต้องเพิ่ม framework/build tooling

| ส่วนที่วางไว้ | ผลตรวจ / ข้อปรับปรุง |
| --- | --- |
| public/index, register/login/logout, products/detail/shop | ครอบคลุม browse/auth ที่วางแผน; ต้องกำหนด route/base path ใต้โฟลเดอร์ผู้ใช้ Angsila |
| public/seller/dashboard/products/orders | รองรับการจัดการ P0; ต้องระบุ action/form handlers สำหรับ create/edit/delete และ authorization ของแต่ละคำขอ |
| public/checkout และ orders | ควรแยกอ่านกับ POST เปลี่ยนข้อมูล ใช้ PRG และป้องกัน double submit |
| app/auth/session.php และ csrf.php | เพิ่มหรือระบุจุดรวม login guard/owner/participant checks ให้ทุก endpoint เรียกใช้ ไม่ซ่อนปุ่มแทนตรวจสิทธิ์ |
| ShopService/ProductService/OrderService | เหมาะกับกฎธุรกิจและ transaction; ตัดสินใช้ mysqli หรือ PDO อย่างเดียวตาม spec และกำหนด error/rollback behavior |
| app/config/db.php และ env.example.php | มีแนวคิดไม่ฝัง secret ถูกต้อง; ระบุวิธีโหลด config จริง ไม่มี dependency `.env` อัตโนมัติ |
| app/templates/header/footer | ต้องมี output escaping ที่ใช้อย่างสม่ำเสมอ และ shared bootstrap ก่อน output เพื่อเริ่ม session/redirect ได้ |
| storage/uploads | เป็น private area ตามแผน แต่ image_path จะใช้แสดงอย่างไรยังไม่กำหนด; P0 ใช้รูปตัวอย่างภายในระบบได้ถ้ายังไม่เปิด upload |
| database/schema.sql และ seed.sql | schema มีจริงใน ZIP; seed ยังไม่มี ต้องแยก seed/migration/schema และระบุ import ใน DB ที่ได้รับสิทธิ์ |
| docs และ README | เพิ่ม ER diagram, Data Dictionary, project plan, test evidence, deployment mapping และรายงานส่ง; README ปัจจุบันเป็น planning README |
| .gitignore | ยังไม่มีจริงในแพ็ก ต้องสร้างเมื่อพัฒนา ให้กัน secret, logs, uploads จริง และไฟล์เครื่องส่วนตัว พร้อมตรวจ git tracked files |

ยังไม่ยืนยันว่า Angsila ชี้ document root ไป public ได้หรือเขียน storage ได้ ต้องตรวจ webroot/subdirectory, PHP version, DB version/collation, mysqli หรือ PDO driver, mbstring, session, env support และ permissions ตามสไลด์ `CRUD-and-VERSION-CONTROL.pdf` หน้า 7, 33, 36 หากใช้ mysqli get_result ต้องตรวจ mysqlnd ด้วย หากอนุญาตเฉพาะโฟลเดอร์เว็บ ให้ทำ deployment mapping ที่กัน app/config, SQL, logs, secrets จากการเข้าถึงจริง ห้ามสมมติว่า hidden file หรือชื่อ private ป้องกันได้เอง

## 9. ความเสี่ยงและลำดับข้อเสนอแนะ

| ระดับ | ความเสี่ยง/ช่องว่างจริง | ข้อเสนอ |
| --- | --- | --- |
| สูง | Proposal 31 ต.ค. ยังขาดภาพ ER, repo URL และแผนเวลาที่ชัด | ใช้ spec/UC/schema ที่มีจัดชุด proposal ก่อนเพิ่มฟีเจอร์ |
| สูง | ความหมาย Delete ไม่ชัดและมีข้อจำกัด FK | ปรับ CRUD matrix/lifecycle ก่อน Phase 2; แยกสินค้ายังไม่ขายกับมีประวัติ |
| สูง | overselling, checkout ซ้ำ, cancelled คืน stock ซ้ำ | ออกแบบ transaction, idempotency และ state transitions ก่อนทำ OrderService |
| สูง | auth สำเร็จแต่ ownership/order privacy ไม่ครบ | ตรวจ actor จาก session ทุก endpoint รวมการอ่านข้อมูลส่วนตัว; ทดสอบสองบัญชี |
| สูง | ยังไม่ยืนยัน Angsila; spec ให้ deploy ใน Phase 4 | ยืนยัน environment ก่อนเลือก syntax/dependencies และทดลอง deploy ขั้นต้นเมื่อได้รับคำสั่งพัฒนา ไม่รอท้ายงาน |
| สูง | secret/dump/config อยู่ใน Git หรือ web root | ทำ private config และ .gitignore; ตรวจทั้ง repository และพื้นที่เสิร์ฟ |
| กลาง | Phase 4 รวม chat/polish/tests/deploy/report | ย้าย tests/security checks ไปทุก phase และทำ report ต่อเนื่อง; P1 ทำหลัง P0 ผ่านจริง |
| กลาง | category/size/location เป็น free text | กำหนดค่าหรือ normalization และ validation ที่ชัด โดยไม่เพิ่มระบบซับซ้อนเกิน MVP |
| กลาง | upload feature และภาพสินค้าไม่กำหนดวิธีเสิร์ฟ | เริ่ม main image แบบปลอดภัย; ถ้าเปิด upload ตรวจ MIME/ขนาด/ชื่อสุ่ม และป้องกัน execute |
| กลาง | JOIN variants/items เพิ่มแถวและยอดซ้ำ | ออกแบบ query ตามระดับข้อมูล ทดสอบ edge cases และ EXPLAIN |
| กลาง | เข้าใจ draft ว่าเป็นระบบพร้อมส่ง | แยกสถานะ planned/implemented/tested/deployed ใน README และ traceability |

ระดับความเสี่ยงเป็นการประเมินของผู้วิเคราะห์ ไม่ใช่ rubric ของรายวิชา ไม่พบเหตุให้ขยาย CLOZEE ไป payment/rider/tracking หรือบังคับ BUU SSO

ลำดับที่เสนอหลังผู้ใช้สั่งเริ่มพัฒนา: ยืนยัน hosting และปรับแบบ → จัด schema/auth พร้อมทดสอบ → shop/product CRUD พร้อม D ที่ชัด → checkout/status/history พร้อม transaction tests → ตรวจ Angsila/report/README/commit history ให้ครบก่อนพิจารณา P1 ทั้งนี้ยังไม่มีการเริ่มขั้นใดในรายงานรอบนี้

## 10. แนวทางทดสอบและหลักฐานที่เสนอ

รายการนี้เป็นแผนทดสอบในอนาคต ยังไม่ได้รันเว็บไซต์หรือฐานข้อมูล CLOZEE ใช้เตรียมผลทดสอบตาม `Database-authen.pdf` หน้า 35–36 และแนวทาง Security หน้า 18

- CRUD: เพิ่มประกาศจำลอง → อ่านหลัง reload → แก้ไข → ลบตามนโยบายที่ออกแบบ พร้อมตรวจ DB ไม่ใช่เปลี่ยนหน้า UI อย่างเดียว
- Auth: รหัสถูกเข้าได้ รหัสผิดเข้าไม่ได้; ก่อน login เปิด protected URL ไม่ได้; logout แล้ว refresh/เข้าตรงกลับไม่ได้
- Authorization: บัญชี A แก้/ลบ/อ่านข้อมูลส่วนตัวของ B ไม่ได้ รวมการแก้ id, seller_id และส่ง POST ตรง
- Security: ข้อมูล SQL injection ไม่ผ่าน login, ข้อความ HTML แสดงอย่างปลอดภัย, POST ไม่มี/ผิด CSRF ถูกปฏิเสธ, ไม่พบ plaintext password หรือ secrets ใน repo
- Relational data: FK ผิดถูกปฏิเสธ, ค่าเงิน/จำนวน/สถานะผิดไม่ถูกบันทึก, ประกาศไม่มีรูปยังแสดงตามที่ออกแบบ; ถ้ามี order ให้ทดสอบซื้อพร้อมกัน
- Deployment: URL/subdirectory/assets, connection, session และ uploads ทำงานบน Angsila ตามสิทธิ์ที่ได้รับจริง
- บันทึก expected/actual, วันที่, environment, screenshots และข้อจำกัด แล้วผูกกับ Requirement ID ในรายงาน

## 11. เรื่องที่ไม่พบในเอกสารและยังต้องยืนยัน

ไม่พบข้อกำหนด Final ที่ระบุคะแนนรวมหรือสัดส่วนคะแนน, จำนวนสมาชิกกลุ่ม, จำนวนหน้าเว็บ/ตาราง/ความสัมพันธ์ขั้นต่ำ, จำนวน JOIN หรือชนิด JOIN ที่บังคับ, จำนวน commits/branches/PRs ขั้นต่ำ, framework/CSS library ที่บังคับ, admin role ที่บังคับ, registration/email verification/BUU SSO, payment gateway, ระบบส่งสินค้า, ระยะเวลานำเสนอ, template/ความยาว/นามสกุลรายงาน, ช่องทางส่งหรือเวลาปิดรับ และเงื่อนไขการใช้ AI/การอ้างอิงโค้ด

การไม่พบไม่ใช่การอนุญาตหรือยกเว้นโดยอาจารย์ หากมีประกาศรายวิชาอื่นควรนำมาเทียบเพิ่มเติม สำหรับรายละเอียด hosting สไลด์ระบุเองให้ยืนยันกับผู้ดูแล จึงไม่เดา URL ของผู้ใช้, host/port/database name, โควตา หรือสิทธิ์ CREATE DATABASE จากตัวอย่างและชีต

แผน CLOZEE ได้รับและตรวจแล้วจาก planning pack แต่ยังต้องยืนยันรายละเอียดต่อไปนี้ก่อนนำไปใช้: ER Diagram และ Data Dictionary, URL repo CLOZEE, deployment mapping/runtime/DB settings ที่ไม่ใช่รหัสผ่าน, นโยบาย Delete, cancelled/stock/idempotency, ค่าจัดส่งและลำดับสถานะ รวมถึงประกาศรายวิชาเพิ่มเติมถ้ามี ขอบเขต order ร้านเดียวและ variants/stock ยืนยันได้จากแผนแล้ว จึงไม่ต้องถามใหม่ว่าระบบเป็นแค่ประกาศติดต่อหรือไม่

## 12. สถานะส่งมอบและเงื่อนไขเริ่มขั้นถัดไป

- อ่านสไลด์ทั้งหมด 105 หน้า, เอกสาร/โค้ด/SQL ของการบ้าน และไฟล์ข้อความทั้งหมด 7 ไฟล์ใน CLOZEE planning pack แล้ว ยกเว้นค่าลับใน `.env` ซึ่งไม่จำเป็นต่อการวิเคราะห์
- แยกข้อกำหนด Final, เนื้อหาการสอน, Lab/การบ้าน และข้อเสนอแนะออกจากกัน พร้อมชื่อไฟล์และหน้าหรือหัวข้ออ้างอิง
- สร้างรายงานนี้เพียงไฟล์เดียวใน workspace; ไม่มีการสร้างเว็บไซต์, schema SQL หรือ project scaffold
- ตรวจเทียบ planning pack ครบแล้ว รวม schema draft 8 ตารางและ target structure; เป็น static review ไม่รับรองว่ารัน SQL หรือเว็บไซต์ผ่านแล้ว
- **รอคำสั่งเริ่ม Development Phase 1 จากผู้ใช้** การให้แผนเพิ่มเพื่อวิเคราะห์ไม่ได้ถือเป็นคำสั่งเริ่มพัฒนา
