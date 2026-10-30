"""Build coursework documentation from actual schema introspection and test logs.
Run scripts/export-metadata.php and the test suites first. No network or DB writes.
"""
from pathlib import Path
from collections import defaultdict
import json, html

ROOT = Path(__file__).resolve().parent.parent
DOC = ROOT / 'docs'
meta = json.loads((ROOT/'storage/schema-metadata.json').read_text(encoding='utf-8'))
domain = json.loads((ROOT/'storage/test-results.json').read_text(encoding='utf-8'))
browser = json.loads((ROOT/'storage/browser-results.json').read_text(encoding='utf-8'))
joins = json.loads((ROOT/'storage/join-results.json').read_text(encoding='utf-8'))
ci = json.loads((DOC/'evidence/ci-verification.json').read_text(encoding='utf-8'))
live = json.loads((DOC/'evidence/angsila-functional-results.json').read_text(encoding='utf-8'))
live_http = json.loads((DOC/'evidence/angsila-http-results.json').read_text(encoding='utf-8'))
assert live['failed'] == 0 and live_http['failed'] == 0, 'Do not claim live acceptance with failures.'
assert domain['failed'] == 0 and browser['failed'] == 0, 'Do not publish passing results with failing tests.'

def write(name, text):
    (DOC/name).write_text(text,encoding='utf-8')

def table(headers, rows):
    def clean(v): return str(v if v is not None else 'NULL').replace('|',' / ').replace('\n',' ')
    return '\n'.join(['| '+' | '.join(headers)+' |','| '+' | '.join(['---']*len(headers))+' |']+['| '+' | '.join(map(clean,row))+' |' for row in rows])+'\n'

groups=defaultdict(list)
foreign={(r['TABLE_NAME'],r['COLUMN_NAME']):f"{r['REFERENCED_TABLE_NAME']}.{r['REFERENCED_COLUMN_NAME']}" for r in meta['foreign_keys']}
meaning={
 'user_id':'รหัสบัญชีผู้ใช้','full_name':'ชื่อแสดงผล','email':'อีเมลไม่ซ้ำ ปรับเป็นตัวพิมพ์เล็กใน PHP','password_hash':'รหัสผ่านที่ผ่าน password_hash เท่านั้น','phone':'เบอร์โทร (optional; ปัจจุบันไม่ใช้ในฟอร์ม)',
 'shop_id':'รหัสร้าน','owner_user_id':'เจ้าของร้าน หนึ่งร้านต่อบัญชี','shop_name':'ชื่อร้าน','description':'รายละเอียดร้านหรือสินค้า','pickup_location':'จุดนัดรับที่ร้านระบุ','is_active':'1 เปิดใช้งาน / 0 ปิดใช้งาน',
 'product_id':'รหัสสินค้า','product_name':'ชื่อสินค้าในปัจจุบัน','category':'หมวดหมู่จากรายการค่าที่อนุญาตใน PHP','condition_type':'new หรือ secondhand','image_path':'ชื่อภาพสาธิตที่อนุญาตในระบบ',
 'variant_id':'รหัสตัวเลือกไซซ์','size_label':'ไซซ์ ตัวพิมพ์ใหญ่และไม่ซ้ำต่อสินค้า','price':'ราคาต่อหน่วย DECIMAL; PHP คำนวณเป็นสตางค์','stock_quantity':'จำนวนพร้อมขาย 0–999999','version':'เลขรุ่นสำหรับป้องกันการแก้สต็อกทับกัน',
 'order_id':'รหัสคำสั่งซื้อ','buyer_user_id':'บัญชีผู้ซื้อ','fulfillment_method':'pickup หรือ delivery','fulfillment_details':'สถานที่และรายละเอียดส่วนตัวที่ผู้ซื้อให้ร้าน','shipping_fee':'ค่าจัดส่ง ณ เวลาสั่งซื้อ','total_amount':'ผลรวมสินค้าและค่าจัดส่งจาก server','status':'สถานะปัจจุบันตาม state machine','buyer_note':'หมายเหตุถึงร้าน',
 'checkout_key':'รหัสกันคำขอซ้ำ ไม่ซ้ำร่วมกับ buyer_user_id','request_hash':'SHA-256 ของคำขอเพื่อป้องกันใช้ key เดิมกับข้อมูลอื่น','order_item_id':'รหัสรายการซื้อ','product_name_snapshot':'ชื่อสินค้า ณ วันที่สั่ง','size_label_snapshot':'ไซซ์ ณ วันที่สั่ง','quantity':'จำนวน 1–99','unit_price':'ราคาต่อหน่วย ณ วันที่สั่ง',
 'conversation_id':'รหัสการสนทนา','last_message_at':'เวลาข้อความล่าสุด ใช้เรียง thread','message_id':'รหัสข้อความ','sender_user_id':'ผู้ส่งจริงจาก session','message_text':'ข้อความล้วน 1–2000 ตัวอักษร Escape เมื่อแสดงผล',
 'history_id':'รหัสประวัติสถานะ','actor_user_id':'ผู้สร้างคำสั่งซื้อหรือผู้เปลี่ยนสถานะ','from_status':'สถานะเดิม NULL สำหรับการสร้างครั้งแรก','to_status':'สถานะใหม่','attempt_id':'รหัสบันทึกการล็อกอินผิด','bucket':'SHA-256 ของ IP + normalized email','ip_bucket':'SHA-256 ของ IP เพื่อจำกัดการลองรวม','attempted_at':'เวลา login ผิด เก็บชั่วคราว','created_at':'เวลาสร้าง (UTC)','updated_at':'เวลาแก้ไขล่าสุด (UTC)'}
for c in meta['columns']: groups[c['TABLE_NAME']].append(c)
dictionary='# CLOZEE Data Dictionary\n\nสร้างจาก information_schema ของ schema ที่นำเข้าจริงบน MariaDB 11.4.9 ไม่ใช่การเดาจากชื่อฟิลด์ รวม 10 ตาราง 75 คอลัมน์ 14 Foreign Keys เวลาในฐานข้อมูลเป็น UTC ส่วน UI ระบุ UTC ไว้ชัดเจน\n\n'
for name, cols in groups.items():
    dictionary+=f'## {name}\n\n'
    rows=[]
    for c in cols:
        fk=foreign.get((name,c['COLUMN_NAME']))
        key=c['COLUMN_KEY'] or '-'
        if fk:key+='; FK → '+fk
        if c['EXTRA']:key+='; '+c['EXTRA']
        rows.append([c['COLUMN_NAME'],c['COLUMN_TYPE'],c['IS_NULLABLE'],c['COLUMN_DEFAULT'],key,meaning.get(c['COLUMN_NAME'],'ดูคำอธิบาย schema')])
    dictionary+=table(['Column','Type','NULL?','Default','Key / rule','Meaning'],rows)+'\n'
dictionary+='## Unique constraints\n\n'+table(['Table','Index','Columns'],[[x['TABLE_NAME'],x['INDEX_NAME'],x['COLUMNS_LIST']] for x in meta['unique_keys']])
dictionary+='\n## กติกาที่ต้องตรวจใน PHP\n\nทุก order item ต้องอยู่ในร้านของ order, ผู้ส่งแชทต้องเป็นผู้ร่วมสนทนา, สินค้าต้องมีอย่างน้อยหนึ่ง variant, order ต้องมีอย่างน้อยหนึ่ง item, สต็อกต้องไม่ถูกขายเกิน, state transition ต้องตรงวิธีรับสินค้า และราคาต้องมาจาก server ดู implementation-decisions.md สำหรับ CHECK และ business rules ฉบับเต็ม\n'
write('data-dictionary.md',dictionary)

requirements='''# CLOZEE Course Requirements สำหรับ Final Round 2

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
'''
write('course-requirements.md',requirements)

checks=[[r['test'],r['status']] for r in domain['results']]
ui_checks=[[r['test'],r['status']] for r in browser['results']]
tests=f'''# CLOZEE Test Results

ข้อมูลจากการรันจริง ไม่ใช่รายการคาดหวัง

| Suite | Environment | Actual result | Executed (UTC) |
| --- | --- | --- | --- |
| Domain / real DB / multiprocess concurrency | PHP {domain['php']}, {domain['database']} | {domain['passed']} PASS, {domain['failed']} FAIL | {domain['executed_at']} |
| HTTP / browser / responsive | {browser['browser']} | {browser['passed']} PASS, {browser['failed']} FAIL | {browser['executed_at']} |
| GitHub CI | PHP 8.3.36 + MySQL 8.4.13 + Chromium | SUCCESS for verified commit {ci['commit'][:7]} | 2026-10-30 |
| Angsila real functional flows | Apache + PDO/MySQL 8.0.46, Chrome via university VPN | {live['passed']} PASS, {live['failed']} FAIL | {live['executed_at']} |
| Angsila anonymous HTTP/security checks | HTTPS from local machine via VPN | {live_http['passed']} PASS, {live_http['failed']} FAIL | {live_http['executed_at']} |

CI evidence: [{ci['run_id']}]({ci['run_url']}); evidence/ci-verification.json records the verified commit and step results. Future commits need their own CI check before delivery; a passing older run does not prove a newer revision.

Commands: `php tests/run.php` with CLOZEE_TEST_DB set to a dedicated test DB; `npm ci` then `npm run test:browser` against the loopback demo server. PHP lint and scripts/doctor.php also executed. Database schema imported into a new isolated DB; migrate/seed scripts refuse existing data. Tests create only fake example.test accounts. Raw local logs remain in ignored storage; sanitized copies are in evidence/.

Browser viewports: {', '.join(browser['viewports'])}. Browser plugin absent; used installed Chrome via Playwright. Interactions: register → own shop/product → buyer cart/checkout → seller transitions → cancel/restock → snapshot history → chat/reply → unauthorized checks → logout/login. Screenshots are from running PHP pages, not the design concept. Permission-denial requests intentionally return 403/404/405/422.

## Domain and database tests

{table(['Test','Observed'],checks)}
## Browser tests

{table(['Test','Observed'],ui_checks)}

## Angsila live functional checks

{table(['Observed flow','Result'],[[r['check'],r['status']] for r in live['results']])}

Independent fresh-browser desktop/mobile screenshots: evidence/angsila-catalog-desktop.png and angsila-catalog-mobile.png. These use no saved browser session. Live physical DELETE, concurrent races, backup restore and off-VPN reachability were not repeated; see evidence/angsila-functional-results.json. The isolated local/MySQL CI suites cover physical DELETE and concurrency separately.
## Defects and test corrections recorded during development

- Normalized untrusted variant array keys before rendering failed form submissions and added a browser HTML-injection regression test.
- Corrected a PHP validation error string whose adjacent Unicode dash was interpreted as part of a variable name. Re-ran the domain suite with no warnings after correction.
- Corrected test setup: an account without a shop is redirected to shop creation; ownership test now creates a separate shop before checking foreign edits.
- Corrected a browser locator to match a snapshot cell containing nested size text, rather than requiring the entire cell to equal the name.
- Waited for image decoding before screenshots; the initial quick screenshot captured assets before load. Final evidence captures completed images.
- Browser-generated fixture products from failed runs were deactivated in the isolated local demo; order history was preserved.

## Remaining verification

Angsila mapping, live core flows, HTTPS Secure/HttpOnly/SameSite cookies, blocked private URLs and fresh-browser desktop/mobile rendering were tested. Off-VPN access, actual mobile hardware, penetration/load testing and restoration from backup were not verified. App has no customer image upload or real payment/delivery integration. See deployment-verification.md and manual-tests.md.
'''
write('test-results.md',tests)
for source,target in [('storage/test-results.json','domain-results.json'),('storage/browser-results.json','browser-results.json'),('storage/join-results.json','join-results.json')]:
    (DOC/'evidence'/target).write_bytes((ROOT/source).read_bytes())

join_doc='# CLOZEE SQL JOIN evidence\n\nExecuted against the isolated local demo database, with fictional browser-test orders. Re-run database/demo-joins.sql on the actual approved demo environment; IDs/counts depend on demo activity. No credentials are exported.\n\n'
join_doc+='## INNER JOIN actual result\n\n'+table(['Order','Buyer','Shop','Snapshot item','Qty','Unit price'],[[r['order_id'],r['buyer'],r['shop_name'],r['product_name_snapshot'],r['quantity'],r['unit_price']] for r in joins['inner_join']])
join_doc+='\n## LEFT JOIN actual result\n\n'+table(['Shop ID','Shop','Order count'],[[r['shop_id'],r['shop_name'],r['order_count']] for r in joins['left_join']])
join_doc+='\nZero-order shops remain because shops is the preserved LEFT side. COUNT(o.order_id) counts matched rows, not the NULL placeholder. Snapshot items can repeat an order ID because orders 1:N order_items. Do not SUM order totals across item rows.\n'
write('sql-join-evidence.md',join_doc)

report=f'''# CLOZEE

## Local Clothing Marketplace & Delivery Platform

ระบบซื้อขายและจัดส่งเสื้อผ้ามือหนึ่งและมือสองสำหรับนิสิตมหาวิทยาลัยบูรพาและคนในพื้นที่บางแสน

**รายงาน Final Project รอบ 2**  
รายวิชา 89022167 Database Management  
มหาวิทยาลัยบูรพา คณะวิทยาการสารสนเทศ  
รหัสนิสิต: **68160207**  
ผู้จัดทำ: **[ยังไม่ได้รับชื่อ–นามสกุล]**  
สมาชิกเพิ่มเติม: **[ยังไม่ได้รับข้อมูลสมาชิกอื่น หรือการยืนยันว่าทำเดี่ยว]**  
อาจารย์ผู้สอน: **[ยังไม่ได้รับชื่ออาจารย์]**  
กำหนดส่ง: **10 พฤศจิกายน 2569**  
จัดทำหลักฐานการพัฒนา: 30 ตุลาคม 2569<br>
ปรับปรุงรายงานและชุดส่งงาน: 31 ตุลาคม 2569

**สถานะ: ระบบ Local, MySQL CI และระบบหลักบน Angsila ผ่านการตรวจแล้ว; ยังต้องเติมข้อมูลหน้าปกและยืนยันรายละเอียดการส่งก่อนส่งฉบับสมบูรณ์ การเข้าถึงนอก VPN ยังไม่ยืนยัน**

<div class="page-break"></div>

## 1 บทนำและ Problem Statement

นิสิต BUU และคนในบางแสนอาจซื้อขายเสื้อผ้าใหม่/มือสองผ่านหลายช่องทาง ทำให้ข้อมูลไซซ์ สภาพ สต็อก และจุดนัดรับกระจัดกระจาย CLOZEE เสนอพื้นที่เดียวสำหรับค้นหาเสื้อผ้าท้องถิ่น เปิดร้าน จัดการตัวเลือกไซซ์ สั่งซื้อ และบันทึกวิธีรับสินค้า แนวปัญหานี้เป็นสมมติฐานของโครงการจาก project-spec มิใช่ผลสำรวจหรือข้อมูลสัมภาษณ์ที่ได้ทำจริง

วัตถุประสงค์คือประยุกต์ PHP และฐานข้อมูลเชิงสัมพันธ์ให้ผู้ซื้อเลือกเสื้อผ้าและเห็นสถานะ ส่วนผู้ขายควบคุมข้อมูลและสต็อกของร้านตนเอง พร้อมแสดงหลักฐาน CRUD, Authentication, Authorization, JOIN และ transaction ตามเนื้อหารายวิชา

กลุ่มเป้าหมายคือผู้ซื้อที่เป็นนิสิตมหาวิทยาลัยบูรพาและคนในบางแสน และผู้ขายรายย่อย/ร้านเสื้อผ้าในพื้นที่ บัญชีเดียวเป็นผู้ซื้อและเปิดร้านได้หนึ่งร้าน ไม่มีการยืนยันสถานภาพนิสิตหรือกำหนด BUU SSO ในรุ่นนี้

## 2 ขอบเขตและ Requirements

P0 ประกอบด้วยบัญชีผู้ใช้ ร้านค้า สินค้าใหม่/มือสอง ไซซ์ ราคา สต็อก ค้นหา/กรอง ตะกร้าร้านเดียว คำสั่งซื้อ นัดรับ/จัดส่ง ประวัติและการเปลี่ยนสถานะ P1 ที่ทำเพิ่มหลังทดสอบระบบหลักคือแชทข้อความ ส่วนหลายรูปและรายงานยอดขายยังไม่ได้ทำ

การจัดส่งเป็นการบันทึกตัวเลือกและรายละเอียดเพื่อให้ร้านดำเนินการโดยตรง ค่าจัดส่งตัวอย่างตั้งที่ server 30 บาทต่อ order; นัดรับฟรี ไม่มีระบบรับเงินออนไลน์ rider dispatch, GPS, shipping API, refund/dispute, rating หรือ checkout หลายร้าน

{table(['ID','Requirement','Implementation'],[
['FR-01/02','สมัคร/เข้าสู่ระบบ/ออกจากระบบ','Register, hash/verify, session, guard, POST+CSRF logout'],
['FR-03','ร้านของผู้ใช้','สร้างและแก้ไขร้านหนึ่งร้านต่อบัญชี'],
['FR-04/05','สินค้า/ไซซ์/ราคา/สต็อก','C/R/U, deactivate และ DELETE เฉพาะสินค้ายังไม่มีประวัติ; version กัน stale stock'],
['FR-06/07','ค้นหา/กรอง/รายละเอียด','GET filters condition/category/size/query, product/shop pages'],
['FR-08/09','checkout ร้านเดียว/นัดรับ/จัดส่ง','server cart, row locks, idempotency, snapshots, server fee'],
['FR-10/11','สถานะ/ประวัติ','shop owner state machine, buyer-owned history, status audit'],
['FR-12','ตรวจสิทธิ์ทุก mutation','actor จาก session; scoped DB checks; tests สองบัญชี'],
['FR-13','ข้อความผู้ซื้อ–ร้าน','ทำแล้ว: participant-only text chat, manual refresh'],
['FR-14/15','หลายรูป/สรุปยอดขาย','ยังไม่ทำ เป็น P1 ที่เลื่อนได้']])}

NFR เน้น prepared statements, output escaping, CSRF, session/cookie, ownership, FK/unique/check, atomic transactions และ responsive design ดู functional-requirements.md และ implementation-decisions.md รายละเอียดครบ

ข้อกำหนดอาจารย์อ้าง `Database-authen.pdf` หน้า 31–36 โดยเฉพาะหน้า 33 องค์ประกอบขั้นต่ำ หน้า 34 วันส่ง หน้า 35 รายงาน หน้า 36 checklist ไม่ใช้เกณฑ์คะแนน Lab แทน Final ดู course-requirements.md สำหรับ traceability

## 3 Use Cases และสิทธิ์

![Use Case Diagram](diagrams/use-cases.svg)

ผู้เยี่ยมชมค้นหา/ดูสินค้าและสมัครสมาชิก ผู้ซื้อ checkout ดูประวัติของตนและคุยกับร้าน ผู้ขายสร้าง/แก้ร้านและสินค้า ดู order ของร้านและเปลี่ยนสถานะ บัญชีเดียวเล่นบทบาท buyer/seller ได้ตามบริบท ไม่ใช่ role ที่ผู้ใช้ส่งมาแล้วได้รับสิทธิ์เอง

UC-06 flow: login → เลือกไซซ์และจำนวน → cart ร้านเดียว → เลือก pickup/delivery และข้อมูลจำลอง → server ตรวจราคา/stock/สิทธิ์ → transaction lock → สร้าง order/items/snapshots → ลด stock และ audit → commit → แสดง order เฉพาะผู้มีสิทธิ์ ถ้า stock ไม่พอหรือผิดร้านจะ rollback ทั้งชุด

UC-05 flow: เจ้าของร้าน login → เพิ่มสินค้าพร้อม variants → อ่าน/แก้ไขข้อมูล → ปิดขายหรือ DELETE เฉพาะรายการที่ไม่มีประวัติการซื้อ การเปลี่ยน ID ของร้านอื่นต้องถูกปฏิเสธ

## 4 ER Diagram และการออกแบบฐานข้อมูล

![ER Diagram](diagrams/er-diagram.svg)

โมเดลจริงมี **10 ตาราง 75 คอลัมน์ 14 Foreign Keys** รวม users, shops, products, product_variants, orders, order_items, conversations, messages, order_status_history และ login_attempts ใช้ InnoDB/utf8mb4, BIGINT UNSIGNED สำหรับคีย์ และ DECIMAL สำหรับเงิน

users 1:0..1 shops บังคับด้วย UNIQUE owner; shops 1:N products/orders; products 1:N variants; orders 1:N items; variants 1:N historical items; conversations 1:N messages; order 1:N status history ส่วน login_attempts เป็นข้อมูลจำกัดการลอง login ชั่วคราวที่ไม่ผูก FK กับผู้ใช้ เพราะคำขออาจไม่ใช่บัญชีจริง

ER แสดง cardinality ที่ schema อนุญาต (0..N) แต่ service บังคับสินค้าต้องมีอย่างน้อยหนึ่ง variant และ order อย่างน้อยหนึ่ง item ภายใน transaction FK product ของ conversation เป็น nullable และ SET NULL เมื่อสินค้าไม่มีแล้ว ส่วนประวัติคำสั่งซื้อใช้ RESTRICT ป้องกันการทำลายความสัมพันธ์

## 5 Architecture และ Project Structure

![Architecture](diagrams/architecture.svg)

Browser ได้ HTML/CSS/vanilla JS จาก PHP หลัง Apache ประมวลผล route ใน public/ ทุก mutation ผ่าน session/CSRF และ service ที่ตรวจ ownership แล้วใช้ PDO native prepared statements ติดต่อ MySQL/MariaDB config และ storage อยู่ภายนอก webroot ไม่มี password บน browser หรือ repo

public/ แยก route สำหรับ catalog/auth/shop/product/cart/checkout/orders/messages และ seller ส่วน app/ มี bootstrap, validation, auth, services และ templates; database/ เก็บ schema/seed/JOIN; scripts/ ใช้ CLI ติดตั้งและตรวจ; docs/ เก็บรายงานและหลักฐาน Node/Python ใช้เฉพาะ test/document tooling ไม่ต้องใช้ขณะเสิร์ฟ PHP

## 6 การทำงานจริงและ UI

หน้าร้านใช้สีขาว/เขียว สไตล์ editorial ภาพสินค้าเป็นภาพสาธิตสร้างใหม่ มิใช่สินค้า/ลูกค้าจริง รูปด้านล่างเป็น screenshot จากเว็บ PHP ที่รันจริง แยกจาก design concept ซึ่งไม่ใช้เป็นหลักฐานทดสอบ

### 6.1 หน้าร้านและมือถือ

![Desktop catalog](evidence/catalog-desktop.png)

![Mobile catalog](evidence/catalog-mobile.png)

### 6.2 Login และ Product CRUD

![Login](evidence/login.png)

![Seller product form](evidence/product-create.png)

Create/Read/Update persist ลง DB จริง ส่วน DELETE สินค้าที่ไม่มีประวัติทดสอบใน service suite ถ้ามี order อ้างอิงจะปฏิเสธและใช้ deactivate แทน

### 6.3 Checkout และสถานะ

![Pickup checkout](evidence/checkout-pickup.png)

![Completed order](evidence/seller-order-completed.png)

ข้อมูลนัดรับ/ที่อยู่ในหลักฐานเป็นข้อความจำลอง ไม่มีข้อมูลผู้ซื้อจริง

### 6.4 แชทข้อความ

![Participant-only chat](evidence/chat.png)

เริ่มจากหน้าสินค้า ใช้ thread เดิมของ buyer/product คู่เดิม ส่งได้เฉพาะ buyer หรือเจ้าของร้าน แสดงล่าสุด 100 ข้อความและ refresh ด้วยตนเอง ไม่มีการอ้างว่า realtime

## 7 CRUD และ Data Integrity

ProductService ทำ CREATE/UPDATE product+variants ใน transaction, ตรวจเจ้าของร้านจาก session, ราคา/ไซซ์/stock จาก server และ version ของ variant หาก checkout เปลี่ยน stock ระหว่างเปิด form จะปฏิเสธ stale edit

OrderService ใช้ lock ตาม buyer → shop → products → variants; ตรวจร้าน/สินค้ายัง active และ stock อีกครั้ง; สร้าง order/items และลด stock/audit พร้อมกัน เงินคิดเป็นสตางค์ integer ก่อนบันทึก DECIMAL request key unique ต่อผู้ซื้อและ payload hash กัน retry ที่ไม่ตรงกัน ทำให้ส่งซ้ำไม่หัก stock ซ้ำ

![Order status state machine](diagrams/order-status.svg)

Seller เปลี่ยนสถานะตามวิธีรับสินค้าเท่านั้น Cancel อนุญาตก่อนพร้อมรับ/ออกส่งและคืน stock ครั้งเดียวภายใต้ order lock Terminal states เปลี่ยนซ้ำไม่ได้ snapshots ชื่อ/ไซซ์/ราคายังคงเดิมเมื่อแก้ product

## 8 SQL JOIN และผลจริง

ตัวอย่าง SQL อยู่ใน database/demo-joins.sql และผลรันอยู่ sql-join-evidence.md / evidence/join-results.json INNER JOIN เชื่อมผู้ซื้อ–order–ร้าน–snapshot items ส่วน LEFT JOIN คงร้านที่ไม่มี order โดยใช้ COUNT(o.order_id) ไม่ใช่ COUNT(*)

{table(['Order','Buyer','Shop','Item','Qty'],[[r['order_id'],r['buyer'],r['shop_name'],r['product_name_snapshot'],r['quantity']] for r in joins['inner_join'][:4]])}

{table(['Shop','Order count'],[[r['shop_name'],r['order_count']] for r in joins['left_join'][:3]])}

JOIN หนึ่งต่อหลายเพิ่มจำนวนแถวได้ จึงไม่ SUM(total_amount) หลัง JOIN items โดยตรง Product catalog aggregate variants ให้เหลือหนึ่งแถวต่อ product ก่อน pagination หลักการอ้าง Data-base-join.pdf หน้า 9, 16, 28–30

## 9 Authentication และ Security

ใช้ password_hash/password_verify, normalized unique email, session_regenerate_id หลัง login/register, strict session mode และ idle timeout 30 นาที Cookie มี HttpOnly/SameSite=Lax; Secure default true แต่ปิดเฉพาะ Local HTTP การตรวจ HTTPS บน Angsila ยืนยัน Secure/HttpOnly/SameSite=Lax และ no-store แล้ว ไม่มีการส่งออก cookie value หรือรหัสผ่าน

ทุก form เปลี่ยนข้อมูลรวม logout ใช้ POST+CSRF ทุกคำสั่ง SQL รับค่าผ่าน prepared statements Escape output ด้วย htmlspecialchars CSP จำกัด source ของ asset/form และห้าม framing DB errors ไม่เผยต่อผู้ใช้ ผู้ใช้ A ถูกตรวจทั้ง URL และ POST ว่าแก้ข้อมูล B ไม่ได้

จำกัด login ผิดจาก bucket account+IP และ IP ในช่วง 15 นาที เก็บ hash ไม่เก็บรหัสผ่าน/IP ดิบ private config และ demo passwords ถูก gitignore และอยู่นอก public การทดสอบใช้บัญชี example.test ที่สร้างขึ้นเท่านั้น ไม่อ่านหรือใช้ชีต User/Password

## 10 Testing และผลที่ได้

Local: **{domain['passed']} / {domain['passed']+domain['failed']} domain/database/concurrency tests ผ่าน**, **{browser['passed']} / {browser['passed']+browser['failed']} browser tests ผ่าน** Environment PHP {domain['php']}, {domain['database']}, installed Chrome via Playwright ที่ desktop และ 390×844

CI: [GitHub Actions run {ci['run_id']}]({ci['run_url']}) ยืนยัน PHP 8.3.36 + MySQL 8.4.13 และ Chromium ขั้น syntax/schema/seed, domain/concurrency และ browser ผ่านสำหรับ commit `{ci['commit'][:7]}` หลักฐานแยก revision ชัดเจน ไม่ใช้ CI เก่าอ้างว่า commit ใหม่ผ่านอัตโนมัติ

Concurrency test ใช้สอง PHP processes เชื่อม DB อิสระแย่งซื้อ item ชิ้นสุดท้าย ผลสำเร็จเพียงหนึ่งคำขอ stock เหลือศูนย์ ส่วนสองคำขอ key เดียวได้ order เดียว stock ลดครั้งเดียว ทดสอบ rollback, stale edit, cancel คืน stock, snapshot, ownership, CSRF, SQL injection และ XSS chat เพิ่มด้วย

Angsila: ตรวจผ่าน {live['passed']} functional checkpoints และ {live_http['passed']} anonymous HTTP/security checks พร้อม fresh-browser desktop/mobile capture ระบบนัดรับ Order #1 สำเร็จ ยกเลิกจัดส่ง Order #2 คืนสต็อก และจัดส่ง Order #3 สำเร็จ ทดสอบสิทธิ์ข้ามบัญชี แชท และ snapshot ด้วยข้อมูล QA สมมติ รายละเอียดใน test-results.md และ JSON evidence/ การทดสอบนี้ไม่ใช่ penetration/load test; concurrency และ physical DELETE ผ่านใน isolated DB/CI แต่ไม่ได้ทำซ้ำบน live

## 11 Deployment และสถานะส่งงาน

Repository: [Skyce38/CLOZEE](https://github.com/Skyce38/CLOZEE)  
Local URL: http://127.0.0.1:8088 (เฉพาะเครื่องพัฒนา ไม่ใช่ URL ส่งงาน)  
Angsila URL: **https://angsila.informatics.buu.ac.th/~68160207/clozee/**

ผู้ใช้ยืนยันว่าต้องเปิด VPN มหาวิทยาลัย และล็อกอิน phpMyAdmin/FileZilla ด้วยตนเอง จากนั้นอนุมัติปลายทางและแผน SQL ก่อนเขียนจริง แยก public_html/clozee ออกจาก clozee-private นอกเว็บ ใช้ฐาน s68160207 โดยสร้างเฉพาะตาราง clozee_ 10 ตาราง เก็บตาราง Lab เดิมทั้งห้าไว้ครบ รหัสผ่านกรอกโดยผู้ใช้ในไฟล์ส่วนตัวที่ Git ไม่ติดตามและไม่อยู่ใน public_html ไม่อ่านหรือใช้รหัสผ่านจาก Google Sheets

ตรวจ schema จริงได้ 75 คอลัมน์, 14 Foreign Keys, 5 enforced CHECK; หน้าเว็บ PHP/PDO เชื่อมต่อและทำงานกับ MySQL 8.0.46 ได้จริง HTTP protection ผ่านและเว็บ Lab เดิมยังเปิดได้ ดู deployment-verification.md สำหรับหลักฐานและขอบเขตการตรวจ การเปิดด้วยเบราว์เซอร์ใหม่ผ่านในเครือข่าย/VPN เดียวกัน ไม่อ้างว่าทดสอบจากอินเทอร์เน็ตภายนอก VPN แล้ว

![Angsila desktop — actual fresh anonymous browser](evidence/angsila-catalog-desktop.png)

![Angsila mobile — actual fresh anonymous browser](evidence/angsila-catalog-mobile.png)

## 12 ข้อจำกัดและงานที่ยังต้องทำ

ต้องเติมข้อมูลหน้าปกที่ยังไม่ทราบและยืนยันช่องทางส่ง/slot นำเสนอ; ไม่ยืนยัน off-VPN reachability หรือ restore backup; รูปสินค้าเป็นชุดภาพสาธิต ไม่มี customer upload/multiple images; chat ไม่ realtime; order/thread/shop listing จำกัดล่าสุดไม่เกิน 100; ไม่มี analytics dashboard, payment, courier, GPS, refunds หรือ BUU SSO; pain point ยังเป็นสมมติฐาน ไม่อ้างการวิจัยจริง

ก่อนส่งจริงต้องเติมชื่อผู้จัดทำ/สมาชิก/อาจารย์ ตรวจ URL ผ่าน VPN อีกครั้ง และยืนยันช่องทางส่ง/slot นำเสนอกับอาจารย์ เพราะสไลด์ที่ให้ไม่ได้กำหนดรายละเอียดเหล่านี้ ห้ามส่งไฟล์ config หรือรหัสผ่านใน ZIP/GitHub บัญชี ร้าน สินค้า และคำสั่งซื้อ QA เป็นข้อมูลสมมติ มิใช่การซื้อขายจริง

## 13 แหล่งอ้างอิง

- Database-authen.pdf หน้า 31–36: Final Project; หน้า 5–18: CRUD/Auth/Security
- CRUD-and-VERSION-CONTROL.pdf หน้า 3–17, 22–36: PHP/MySQL/Git/Angsila
- Data-base-join.pdf หน้า 4–5, 8–30: relational keys/JOIN/debug
- CLOZEE-round2-kit.zip: round2-build-brief, project-spec, functional-requirements, use-cases, final-report-outline
- [PHP PDO prepared statements](https://www.php.net/manual/en/pdo.prepared-statements.php)
- [MySQL InnoDB locking reads](https://dev.mysql.com/doc/refman/8.0/en/innodb-locking-reads.html)
- GitHub source/CI และหลักฐานที่รันจริงใน docs/evidence; ไม่มีการคัดลอกโปรเจกต์ Resume/อาหารเป็น CLOZEE

<div class="page-break"></div>

## ภาคผนวก Data Dictionary

{dictionary.removeprefix('# CLOZEE Data Dictionary').strip()}
'''
write('final-report.md',report)
print(f'Generated course requirements, 75-field dictionary, report source, actual test tables and JOIN evidence. Tests: {domain["passed"]}+{browser["passed"]}.')
