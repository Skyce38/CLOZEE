const fs=require('node:fs');
const path=require('node:path');
const {pathToFileURL}=require('node:url');
const {chromium}=require('playwright');
(async()=>{
  const {marked}=await import('marked');
  const root=path.resolve(__dirname,'..'),doc=path.join(root,'docs');
  const source=fs.readFileSync(path.join(doc,'final-report.md'),'utf8');
  const css=`@page{size:A4;margin:18mm 16mm 19mm}*{box-sizing:border-box}body{font-family:Tahoma,Arial,sans-serif;font-size:12px;line-height:1.8;color:#263b2d;margin:0}h1{font:54px Georgia,serif;color:#244b36;letter-spacing:-1.5px;margin:20px 0}h2{font-size:21px;line-height:1.45;font-weight:500;color:#244b36;margin:28px 0 12px;border-bottom:1px solid #ccd8c6;padding-bottom:9px;break-after:avoid}h3{font-size:16px;margin:22px 0 10px;break-after:avoid}p{margin:10px 0;orphans:3;widows:3}a{color:#244b36;text-decoration:underline;overflow-wrap:anywhere}strong{font-weight:700}table{border-collapse:collapse;width:100%;font-size:10px;line-height:1.55;margin:14px 0 20px;table-layout:fixed}th,td{padding:7px 6px;border:1px solid #d5ded1;text-align:left;vertical-align:top;overflow-wrap:anywhere}th{background:#eef2ed;color:#244b36}thead{display:table-header-group}tr{break-inside:avoid}img{display:block;max-width:100%;max-height:760px;width:auto;height:auto;margin:14px auto;object-fit:contain}p:has(>img),figure{break-inside:avoid}figure{margin:18px 0}figcaption{font-size:10px;color:#697464;text-align:center}img[alt="Mobile catalog"]{max-height:780px}code{font-family:Consolas,monospace;font-size:.92em;overflow-wrap:anywhere;background:#f1f4ef;padding:1px 3px}pre{white-space:pre-wrap;word-break:break-word}.page-break{break-before:page}ul,ol{padding-left:22px}li{margin:5px 0}@media screen{body{max-width:850px;padding:35px;margin:auto;background:#fff}html{background:#e8ece6}}`;
  fs.writeFileSync(path.join(doc,'final-report.html'),'<!doctype html><html lang="th"><head><meta charset="utf-8"><title>CLOZEE Final Project Report</title><style>'+css+'</style></head><body>'+marked.parse(source).replace(/<p>(<img[^>]+alt="([^"]*)"[^>]*>)<\/p>/g,'<figure>$1<figcaption>$2 — actual application / generated diagram as labelled</figcaption></figure>')+'</body></html>');
  const browser=await chromium.launch({channel:process.env.CLOZEE_BROWSER_CHANNEL||'chrome',headless:true});
  try {
    const page=await browser.newPage();await page.goto(pathToFileURL(path.join(doc,'final-report.html')).href);await page.waitForLoadState('networkidle');await page.locator('img').evaluateAll(a=>Promise.all(a.map(i=>i.decode())));
    const pdf=await page.pdf({format:'A4',preferCSSPageSize:true,printBackground:true,displayHeaderFooter:true,headerTemplate:'<span></span>',footerTemplate:'<div style="font-size:8px;color:#697464;width:100%;padding:0 16mm;display:flex;justify-content:space-between"><span>CLOZEE · 89022167 Database Management · Local / CI evidence</span><span><span class="pageNumber"></span> / <span class="totalPages"></span></span></div>'});
    const target=path.join(doc,'CLOZEE-Final-Report-Round2.pdf');
    fs.writeFileSync(target+'.tmp',pdf);fs.renameSync(target+'.tmp',target);
    console.log('Printed docs/CLOZEE-Final-Report-Round2.pdf from actual report source and evidence.');
  }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});
