// Builds docs/final-report.html and the report PDF from docs/final-report.md.
// The PDF is rendered until the table of contents page numbers are stable, then page numbers are stamped.
const fs=require('node:fs');
const path=require('node:path');
const {pathToFileURL}=require('node:url');
const {chromium}=require('playwright');
const {PDFDocument,PDFName,StandardFonts,rgb}=require('pdf-lib');

const CSS=`
@font-face{font-family:Sarabun;src:url(fonts/Sarabun-Regular.ttf);font-weight:400}
@font-face{font-family:Sarabun;src:url(fonts/Sarabun-Bold.ttf);font-weight:700}
@font-face{font-family:Sarabun;src:url(fonts/Sarabun-Italic.ttf);font-weight:400;font-style:italic}
@page{size:A4;margin:30mm 25mm 25mm 32mm}
*{box-sizing:border-box}
body{font-family:Sarabun,Tahoma,sans-serif;font-size:12pt;line-height:1.65;color:#000;margin:0}
.cover{height:235mm;text-align:center;font-size:13pt;break-after:page}
.cover-title{font-size:18pt;font-weight:700;line-height:1.6}
.cover-sub{margin-top:6mm}
.cover-label{font-weight:700;margin-top:16mm;margin-bottom:2mm}
.cover-foot{margin-top:18mm}
.front-title{font-size:16pt;font-weight:700;text-align:center;margin-bottom:6mm}
.front-break{break-before:page}
.toc{break-after:page}
.toc-row{display:flex;align-items:baseline;gap:2mm;margin:1.5mm 0}
.toc-dots{flex:1;border-bottom:1px dotted #000}
h2{font-size:15pt;font-weight:700;line-height:1.7;margin:7mm 0 2mm;break-after:avoid}
h3{font-size:13pt;font-weight:700;line-height:1.7;margin:4mm 0 1mm;break-after:avoid}
p{margin:0 0 2.5mm;text-indent:1.1cm;orphans:3;widows:3}
a{color:inherit;overflow-wrap:anywhere}
ul{margin:0 0 3mm;padding-left:9mm}
li{margin:1mm 0}
.table-caption{text-indent:0;margin:4mm 0 1.5mm;break-after:avoid}
table{border-collapse:collapse;width:100%;font-size:10.5pt;line-height:1.45;margin:0 0 5mm}
th,td{padding:1.5mm 2mm;border:1px solid #555;text-align:left;vertical-align:top;overflow-wrap:break-word}
td a{overflow-wrap:anywhere}
th{background:#eee;font-weight:700}
thead{display:table-header-group}
tr{break-inside:avoid}
figure{margin:4mm 0 5mm;break-inside:avoid}
figure img{display:block;max-width:100%;max-height:150mm;width:auto;height:auto;margin:0 auto;object-fit:contain}
figcaption{font-size:11pt;font-weight:700;text-align:center;margin-top:2mm}
code{font-family:Consolas,Sarabun,monospace;font-size:.86em}
pre{white-space:pre-wrap;word-break:break-word;background:#f4f4f4;border:1px solid #ccc;padding:2mm 3mm;margin:0 0 4mm;line-height:1.4;break-inside:avoid}
pre code{font-size:9pt}
.appendix{break-before:page}
.appendix table{font-size:8.5pt;line-height:1.35;table-layout:fixed}
.appendix th,.appendix td{padding:1mm;overflow-wrap:anywhere}
.appendix h2{margin-top:0}
@media screen{body{max-width:850px;padding:35px;margin:auto;background:#fff}html{background:#e8ece6}.cover{height:auto;margin-bottom:60px}}
`;

const stripTags=html=>html.replace(/<[^>]+>/g,'').trim();
const decode=text=>text.replace(/&amp;/g,'&').replace(/&#39;/g,"'").replace(/&quot;/g,'"').replace(/&lt;/g,'<').replace(/&gt;/g,'>');
const normalize=text=>decode(text).replace(/\s+/g,' ').trim();

function renderBody(marked,source){
  return marked.parse(source)
    .replace(/<p>(<img[^>]+alt="([^"]*)"[^>]*>)<\/p>/g,'<figure>$1<figcaption>$2</figcaption></figure>')
    .replace(/<p><strong>(ตารางที่ \d+)<\/strong>/g,'<p class="table-caption"><strong>$1</strong>');
}
function chapterTitles(body){
  return [...body.matchAll(/<h2[^>]*>([\s\S]*?)<\/h2>/g)].map(m=>stripTags(m[1]));
}
function withToc(body,titles,pages){
  const rows=titles.map(t=>`<div class="toc-row"><span>${t}</span><span class="toc-dots"></span><span>${pages[normalize(t)]??'00'}</span></div>`).join('');
  return body.replace('<!-- TOC -->',`<div class="toc">${rows}</div>`);
}
async function outlinePages(pdfjs,buffer){
  const doc=await pdfjs.getDocument({data:new Uint8Array(buffer),verbosity:0}).promise;
  const pages={};
  const walk=async items=>{
    for(const item of items||[]){
      const dest=typeof item.dest==='string'?await doc.getDestination(item.dest):item.dest;
      // Chrome can repeat a heading's text in its outline entry when the heading sits near a page break.
      const title=normalize(item.title),half=title.slice(0,title.length/2);
      if(dest) pages[title===half+half?half:title]??=(await doc.getPageIndex(dest[0]))+1;
      await walk(item.items);
    }
  };
  await walk(await doc.getOutline());
  await doc.destroy();
  return pages;
}

(async()=>{
  const {marked}=await import('marked');
  const pdfjs=await import('pdfjs-dist/legacy/build/pdf.mjs');
  const root=path.resolve(__dirname,'..'),docDir=path.join(root,'docs');
  const body=renderBody(marked,fs.readFileSync(path.join(docDir,'final-report.md'),'utf8'));
  const titles=chapterTitles(body);
  const htmlPath=path.join(docDir,'final-report.html');
  const browser=await chromium.launch({channel:process.env.CLOZEE_BROWSER_CHANNEL||'chrome',headless:true});
  try{
    let pages={},pdf,stable=false;
    for(let pass=0;pass<4&&!stable;pass++){
      fs.writeFileSync(htmlPath,`<!doctype html><html lang="th"><head><meta charset="utf-8"><title>CLOZEE รายงานโครงงาน</title><style>${CSS}</style></head><body>${withToc(body,titles,pages)}</body></html>`);
      const page=await browser.newPage();
      await page.goto(pathToFileURL(htmlPath).href);
      await page.waitForLoadState('networkidle');
      await page.evaluate(()=>document.fonts.ready);
      await page.locator('img').evaluateAll(a=>Promise.all(a.map(i=>i.decode())));
      pdf=await page.pdf({format:'A4',preferCSSPageSize:true,printBackground:true,outline:true,tagged:true});
      await page.close();
      const found=await outlinePages(pdfjs,pdf);
      const missing=titles.filter(t=>!(normalize(t) in found));
      if(missing.length) throw new Error('Headings missing from PDF outline: '+missing.join(', '));
      const next=Object.fromEntries(titles.map(t=>[normalize(t),found[normalize(t)]]));
      stable=JSON.stringify(next)===JSON.stringify(pages);
      pages=next;
    }
    if(!stable) throw new Error('Table of contents page numbers did not stabilise.');
    const out=await PDFDocument.load(pdf);
    out.setTitle('CLOZEE รายงานโครงงาน');
    out.catalog.delete(PDFName.of('Outlines')); // The outline is only used to find pages for the table of contents.
    const font=await out.embedFont(StandardFonts.TimesRoman);
    const firstNumbered=pages[normalize(titles[0])]-1;
    out.getPages().forEach((p,i)=>{
      if(i<firstNumbered) return;
      const label=String(i+1),{width,height}=p.getSize();
      p.drawText(label,{x:width-71-font.widthOfTextAtSize(label,12),y:height-50,size:12,font,color:rgb(0,0,0)});
    });
    const target=path.join(docDir,'CLOZEE-Final-Report-Round2.pdf');
    fs.writeFileSync(target+'.tmp',await out.save());
    fs.renameSync(target+'.tmp',target);
    console.log(`Printed docs/CLOZEE-Final-Report-Round2.pdf: ${out.getPageCount()} pages.`);
  }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});
