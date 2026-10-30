const fs=require('node:fs');
const path=require('node:path');
const {instance}=require('@viz-js/viz');
(async()=>{const viz=await instance();const dir=path.join(__dirname,'..','docs','diagrams');for(const f of fs.readdirSync(dir).filter(x=>x.endsWith('.dot'))){const svg=viz.renderString(fs.readFileSync(path.join(dir,f),'utf8'),{format:'svg',engine:'dot'});fs.writeFileSync(path.join(dir,f.replace('.dot','.svg')),svg);console.log('Rendered',f);}})().catch(e=>{console.error(e);process.exit(1)});
