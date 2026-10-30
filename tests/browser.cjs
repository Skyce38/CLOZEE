/* Real HTTP/browser smoke and acceptance tests. Uses fictional accounts only.
 * npm install; npm run test:browser. Chrome channel or Playwright Chromium required.
 * Configure CLOZEE_URL for a LOCAL isolated demo instance, never production.
 */
const { chromium } = require('playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const base = process.env.CLOZEE_URL || 'http://127.0.0.1:8088';
if (!/^http:\/\/(127\.0\.0\.1|localhost)(:\d+)?(\/[^?#]*)?$/.test(base)) throw Error('Browser tests are restricted to a local demo URL.');
const root = path.resolve(__dirname, '..');
const evidence = path.join(root, 'docs', 'evidence');
fs.mkdirSync(evidence, {recursive:true});
const results=[];
const suffix=Date.now().toString(36);
const password=crypto.randomBytes(18).toString('hex'); // ephemeral, never logged or stored
const errors=[];
let browser;
async function test(name, work) {
  try { await work(); results.push({test:name,status:'PASS'});console.log('PASS',name); }
  catch(e) { results.push({test:name,status:'FAIL',detail:e.message});console.error('FAIL',name,e.message);throw e; }
}
async function ready(page) { await page.waitForLoadState('networkidle');await page.locator('img').evaluateAll(imgs=>Promise.all(imgs.map(im=>im.decode().catch(()=>{})))); }
async function shot(page,name) { await ready(page);await page.screenshot({path:path.join(evidence,name+'.png'),fullPage:true}); }
async function csrf(page) { return page.locator('input[name=csrf]').first().inputValue(); }
async function register(page, role) {
  await page.goto(base+'/register.php');
  await page.getByLabel('ชื่อที่แสดง',{exact:true}).fill('Demo '+role);
  await page.getByLabel('อีเมล',{exact:true}).fill(role+'.'+suffix+'@example.test');
  await page.getByLabel('รหัสผ่าน',{exact:true}).fill(password);
  await page.getByLabel('ยืนยันรหัสผ่าน',{exact:true}).fill(password);
  await page.getByRole('button',{name:'สมัครสมาชิก',exact:true}).click();
  await page.waitForURL('**/products.php');
}
async function checkout(page,productId,variant,quantity,method) {
  await page.goto(base+'/product.php?id='+productId);
  await page.getByLabel('เลือกไซซ์และราคา').selectOption(variant);
  await page.getByLabel('จำนวน',{exact:true}).fill(String(quantity));
  await page.getByRole('button',{name:'เพิ่มลงตะกร้า',exact:true}).click();
  await page.waitForURL('**/cart.php');
  await page.getByRole('link',{name:'ดำเนินการสั่งซื้อ'}).click();
  await page.getByLabel('วิธีรับสินค้า').selectOption(method);
  await page.getByLabel('สถานที่รับสินค้า / ที่อยู่และข้อมูลติดต่อ').fill('Fictional BUU / Bangsaen demo meeting point. No real address.');
  await shot(page,'checkout-'+method);
  await page.getByRole('button',{name:'ยืนยันคำสั่งซื้อ',exact:true}).click();
  await page.waitForURL('**/order.php?id=*');
  return new URL(page.url()).searchParams.get('id');
}
(async()=>{
  browser=await chromium.launch({channel:process.env.CLOZEE_BROWSER_CHANNEL||'chrome',headless:true});
  const visitor=await browser.newContext({viewport:{width:1536,height:1024}});
  const p=await visitor.newPage();p.on('pageerror',e=>errors.push(e.message));
  await test('catalog renders real PHP data and all product assets',async()=>{
    const r=await p.goto(base+'/');assert.equal(r.status(),200);await ready(p);
    assert.match(await p.title(),/CLOZEE/);assert.ok(await p.locator('.product-card').count()>=4);
    assert.ok(await p.locator('img').evaluateAll(a=>a.every(i=>i.complete&&i.naturalWidth>0)));
    assert.equal(r.headers()['x-content-type-options'],'nosniff');
    await shot(p,'catalog-desktop');
  });
  await test('new/preloved filter and empty search update results',async()=>{
    await p.locator('input[name=condition][value=new]').check({force:true});await p.getByRole('button',{name:'ค้นหา',exact:true}).click();
    assert.equal(await p.locator('.product-card .condition').first().textContent(),'New');
    await p.getByRole('searchbox').fill('not-a-product-'+suffix);await p.getByRole('button',{name:'ค้นหา',exact:true}).click();assert.equal(await p.locator('.product-card').count(),0);assert.ok(await p.getByText('ยังไม่พบเสื้อผ้าที่ค้นหา').isVisible());
  });
  await test('mobile catalog has no horizontal page overflow',async()=>{
    await p.setViewportSize({width:390,height:844});await p.goto(base+'/');await shot(p,'catalog-mobile');
    assert.ok(await p.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth));
    await p.setViewportSize({width:1536,height:1024});
  });
  await test('protected pages redirect anonymous users; logout is POST only',async()=>{
    await p.goto(base+'/seller/products.php');assert.match(p.url(),/login.php/);
    const r=await p.request.get(base+'/logout.php');assert.equal(r.status(),405);
    const missing=await p.request.post(base+'/logout.php',{form:{}});assert.equal(missing.status(),403);
    await shot(p,'login');
  });
  const sellerContext=await browser.newContext({viewport:{width:1440,height:1000}});const seller=await sellerContext.newPage();seller.on('pageerror',e=>errors.push(e.message));
  await test('seller registers with secure session cookie',async()=>{
    await register(seller,'seller');const cookies=await sellerContext.cookies();const session=cookies.find(x=>x.name==='clozee_session');assert.ok(session&&session.httpOnly&&session.sameSite==='Lax');
  });
  await test('seller creates and edits own shop',async()=>{
    await seller.goto(base+'/seller/shop.php');await seller.getByLabel('ชื่อร้าน',{exact:true}).fill('Browser Demo Wardrobe '+suffix);await seller.getByLabel('แนะนำร้าน').fill('Fictional local test shop');await seller.getByLabel('จุดนัดรับ',{exact:true}).fill('Fictional Bangsaen meetup');await seller.getByRole('button',{name:'บันทึกข้อมูลร้าน'}).click();await seller.waitForURL('**/seller/shop.php');assert.equal(await seller.getByLabel('ชื่อร้าน',{exact:true}).inputValue(),'Browser Demo Wardrobe '+suffix);
    await seller.getByLabel('แนะนำร้าน').fill('Edited fictional test shop');await seller.getByRole('button',{name:'บันทึกข้อมูลร้าน'}).click();await shot(seller,'shop-edit');
  });
  let productId,variantId;
  await test('seller creates multi-size product using real form',async()=>{
    await seller.goto(base+'/seller/product-edit.php');await seller.getByLabel('ชื่อสินค้า',{exact:true}).fill('Browser linen '+suffix);await seller.getByLabel('รายละเอียด',{exact:true}).fill('Original demo garment — no customer information.');
    await seller.locator('[name="variants[0][price]"]').fill('290.00');await seller.locator('[name="variants[0][stock_quantity]"]').fill('5');
    await seller.getByRole('button',{name:'+ เพิ่มไซซ์'}).click();await seller.locator('[name="variants[1][size_label]"]').fill('L');await seller.locator('[name="variants[1][price]"]').fill('310.00');await seller.locator('[name="variants[1][stock_quantity]"]').fill('2');
    await shot(seller,'product-create');await seller.getByRole('button',{name:'บันทึกสินค้า',exact:true}).click();await seller.waitForURL('**/seller/products.php');
    const href=await seller.getByRole('link',{name:'แก้ไข',exact:true}).first().getAttribute('href');productId=new URL(href,base).searchParams.get('id');
    await seller.goto(base+'/product.php?id='+productId);variantId=await seller.locator('select[name=variant_id] option').nth(1).getAttribute('value');assert.ok(variantId);await shot(seller,'product-detail');
  });
  const buyerContext=await browser.newContext({viewport:{width:1440,height:1000}});const buyer=await buyerContext.newPage();buyer.on('pageerror',e=>errors.push(e.message));
  let pickupOrder,deliveryOrder;
  await test('buyer registers, adds item, places pickup order',async()=>{await register(buyer,'buyer');pickupOrder=await checkout(buyer,productId,variantId,2,'pickup');assert.ok(await buyer.getByText('580.00 ฿',{exact:true}).last().isVisible());await shot(buyer,'order-pickup');});
  await test('buyer cannot mutate seller order status',async()=>{const r=await buyer.request.post(base+'/order.php?id='+pickupOrder,{form:{csrf:await csrf(buyer),order_id:pickupOrder,status:'confirmed'}});assert.equal(r.status(),422);});
  await test('CSRF rejected before product mutation',async()=>{const r=await seller.request.post(base+'/seller/products.php',{form:{product_id:productId,action:'delete'}});assert.equal(r.status(),403);});
  const outsiderContext=await browser.newContext();const outsider=await outsiderContext.newPage();
  await test('other account cannot edit product or read private order',async()=>{await register(outsider,'stranger');await outsider.goto(base+'/seller/shop.php');await outsider.getByLabel('ชื่อร้าน',{exact:true}).fill('Outsider Demo Shop');await outsider.getByLabel('จุดนัดรับ',{exact:true}).fill('Fictional outsider point');await outsider.getByRole('button',{name:'บันทึกข้อมูลร้าน'}).click();let r=await outsider.goto(base+'/seller/product-edit.php?id='+productId);assert.equal(r.status(),403);r=await outsider.goto(base+'/order.php?id='+pickupOrder);assert.equal(r.status(),404);});
  await test('seller progresses pickup order; buyer sees completion',async()=>{await seller.goto(base+'/order.php?id='+pickupOrder);for(const status of ['confirmed','preparing','ready_for_pickup','completed']){await seller.getByLabel('สถานะถัดไป').selectOption(status);await seller.getByRole('button',{name:'บันทึกสถานะ',exact:true}).click();await seller.waitForURL('**/order.php?id='+pickupOrder);}await buyer.reload();assert.ok(await buyer.locator('.status.completed').isVisible());await shot(seller,'seller-order-completed');});
  await test('delivery checkout records fee and cancellation returns stock',async()=>{deliveryOrder=await checkout(buyer,productId,variantId,1,'delivery');assert.ok(await buyer.getByText('320.00 ฿',{exact:true}).isVisible());await seller.goto(base+'/order.php?id='+deliveryOrder);await seller.getByLabel('สถานะถัดไป').selectOption('cancelled');await seller.getByRole('button',{name:'บันทึกสถานะ',exact:true}).click();await seller.waitForURL('**/order.php?id='+deliveryOrder);await buyer.reload();assert.ok(await buyer.locator('.status.cancelled').isVisible());await buyer.goto(base+'/product.php?id='+productId);assert.match(await buyer.locator('select[name=variant_id]').textContent(),/คงเหลือ 3/);});
  await test('seller edit persists while historical price/name stay unchanged',async()=>{await seller.goto(base+'/seller/product-edit.php?id='+productId);await seller.getByLabel('ชื่อสินค้า',{exact:true}).fill('Edited browser linen '+suffix);await seller.locator('[name="variants[0][price]"]').fill('999.00');await seller.getByRole('button',{name:'บันทึกสินค้า',exact:true}).click();await seller.waitForURL('**/seller/products.php');await buyer.goto(base+'/order.php?id='+pickupOrder);assert.ok((await buyer.locator('tbody td').first().textContent()).includes('Browser linen '+suffix));assert.ok(!(await buyer.locator('tbody td').first().textContent()).includes('Edited browser linen'));assert.ok(await buyer.getByText('290.00 ฿',{exact:true}).isVisible());});
  await test('referenced product cannot be deleted; deactivation hides public item',async()=>{await seller.goto(base+'/seller/products.php');let token=await csrf(seller);let r=await seller.request.post(base+'/seller/products.php',{form:{csrf:token,product_id:productId,action:'delete'}});assert.equal(r.status(),422);r=await seller.request.post(base+'/seller/products.php',{form:{csrf:token,product_id:productId,action:'deactivate'}});assert.equal(r.status(),200);r=await p.goto(base+'/product.php?id='+productId);assert.equal(r.status(),404);});
  await test('mobile seller table does not overflow the page',async()=>{await seller.setViewportSize({width:390,height:844});await seller.goto(base+'/seller/orders.php');await shot(seller,'seller-orders-mobile');assert.ok(await seller.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth));});
  await test('logout invalidates session and protects routes again',async()=>{await buyer.goto(base+'/orders.php');await buyer.getByRole('button',{name:'ออกจากระบบ',exact:true}).click();await buyer.waitForURL('**/login.php');await buyer.goto(base+'/orders.php');assert.match(buyer.url(),/login.php/);});
  await test('SQL injection string cannot bypass login',async()=>{await p.goto(base+'/login.php');const r=await p.request.post(base+'/login.php',{form:{csrf:await csrf(p),email:"' OR 1=1 --",password:'incorrect-test-value'}});assert.equal(r.status(),422);assert.match(await r.text(),/อีเมลหรือรหัสผ่านไม่ถูกต้อง/);});
  await test('no browser JavaScript runtime errors',async()=>assert.deepEqual(errors,[]));
  await shot(seller,'seller-orders-mobile');
})().catch(e=>{console.error(e.stack);process.exitCode=1;}).finally(async()=>{
  if(browser)await browser.close();
  const report={executed_at:new Date().toISOString(),url:base,browser:'Playwright with installed Chrome (Browser plugin not available)',viewports:['1536x1024','1440x1000','390x844'],passed:results.filter(x=>x.status==='PASS').length,failed:results.filter(x=>x.status==='FAIL').length,results};
  fs.writeFileSync(path.join(root,'storage','browser-results.json'),JSON.stringify(report,null,2));
});
