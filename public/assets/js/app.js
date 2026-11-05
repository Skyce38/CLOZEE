document.querySelectorAll('[data-confirm]').forEach(form=>form.addEventListener('submit',event=>{if(!window.confirm(form.dataset.confirm))event.preventDefault();}));
const addVariant=document.querySelector('[data-add-variant]');
if(addVariant) addVariant.addEventListener('click',()=>{
  const list=document.querySelector('[data-variants]');
  if(list.children.length>=12)return;
  const row=document.querySelector('#variant-template').content.cloneNode(true);
  row.querySelectorAll('[name]').forEach(input=>input.name=input.name.replace('__INDEX__',String(list.children.length)));
  list.append(row);
});

async function normalizeProductPhoto(file, maxBytes) {
  if(!['image/jpeg','image/png','image/webp'].includes(file.type)||file.size>8*1048576)throw Error('เลือกภาพ JPG, PNG หรือ WebP ต้นฉบับไม่เกิน 8 MB');
  let bitmap;
  try{bitmap=await createImageBitmap(file,{imageOrientation:'from-image'});}catch{throw Error('เปิดรูปไม่ได้ กรุณาเลือกไฟล์ภาพที่สมบูรณ์');}
  try{
    if(bitmap.width>6000||bitmap.height>6000||bitmap.width*bitmap.height>12000000)throw Error('ภาพต้องไม่เกิน 6,000 พิกเซลต่อด้านและ 12 ล้านพิกเซล');
    const canvas=document.createElement('canvas');const context=canvas.getContext('2d');
    if(!context)throw Error('เบราว์เซอร์นี้ปรับรูปไม่ได้ กรุณาใช้เบราว์เซอร์รุ่นปัจจุบัน');
    let scale=Math.min(1,1600/Math.max(bitmap.width,bitmap.height));
    for(let attempt=0;attempt<9;attempt++){
      canvas.width=Math.max(1,Math.round(bitmap.width*scale));canvas.height=Math.max(1,Math.round(bitmap.height*scale));
      context.clearRect(0,0,canvas.width,canvas.height);context.drawImage(bitmap,0,0,canvas.width,canvas.height);
      const blob=await new Promise(resolve=>canvas.toBlob(resolve,'image/png'));
      if(!blob)throw Error('ปรับภาพไม่สำเร็จ กรุณาเลือกรูปใหม่');
      if(blob.size<=maxBytes)return new File([blob],'product.png',{type:'image/png'});
      scale*=0.8;
    }
    throw Error('ภาพยังมีขนาดใหญ่เกินกำหนด กรุณาเลือกภาพเล็กลง');
  }finally{bitmap.close();}
}

const photoEditor=document.querySelector('[data-photo-editor]');
if(photoEditor){
  const input=photoEditor.querySelector('[data-photo-input]');
  const previews=photoEditor.querySelector('[data-photo-previews]');
  const message=photoEditor.querySelector('[data-photo-message]');
  let objectUrls=[];let sequence=0;let processing=false;
  const form=photoEditor.closest('form');
  form.addEventListener('submit',event=>{if(processing){event.preventDefault();message.textContent='กำลังปรับรูป กรุณารอสักครู่';}});
  const redrawPhotos=()=>{
    const previous=photoEditor.querySelector('[name="cover_image"]:checked')?.value;
    objectUrls.forEach(url=>URL.revokeObjectURL(url));objectUrls=[];previews.replaceChildren();
    const kept=[...photoEditor.querySelectorAll('[data-saved-photo]')].filter(card=>!card.querySelector('[name="remove_images[]"]').checked);
    photoEditor.querySelectorAll('[data-saved-photo]').forEach(card=>card.classList.toggle('photo-removed',card.querySelector('[name="remove_images[]"]').checked));
    const files=[...input.files];let problem='';
    if(kept.length+files.length>Number(photoEditor.dataset.maxFiles))problem='สินค้าหนึ่งรายการมีรูปได้ไม่เกิน 4 รูป กรุณานำรูปเดิมออกหรือเลือกรูปใหม่น้อยลง';
    else if(files.some(file=>!['image/jpeg','image/png','image/webp'].includes(file.type)))problem='รองรับเฉพาะไฟล์ JPG, PNG และ WebP';
    else if(files.some(file=>file.size>Number(photoEditor.dataset.maxBytes)))problem='มีรูปที่ขนาดเกินกำหนด กรุณาเลือกภาพขนาดเล็กลง';
    else if(files.reduce((sum,file)=>sum+file.size,0)>Number(photoEditor.dataset.maxTotal))problem='ไฟล์ภาพรวมมีขนาดใหญ่เกินกำหนด';
    input.setCustomValidity(problem);message.textContent=problem||(files.length?`เลือกรูปใหม่ ${files.length} รูป ยังไม่ได้บันทึก`:'เพิ่มรูปใหม่หรือเลือกภาพหลักจากรูปเดิมได้');
    message.classList.toggle('error',Boolean(problem));
    if(problem)return;
    files.forEach((file,index)=>{
      const url=URL.createObjectURL(file);objectUrls.push(url);
      const card=document.createElement('div');card.className='photo-edit-card';
      const img=document.createElement('img');img.src=url;img.alt=`ตัวอย่างรูปใหม่ ${index+1}`;
      const label=document.createElement('label');label.className='checkbox';
      const radio=document.createElement('input');radio.type='radio';radio.name='cover_image';radio.value=`new:${index}`;
      radio.checked=previous===radio.value||(!kept.length&&!previous&&index===0);
      label.append(radio,document.createTextNode('ใช้เป็นภาพหลัก'));card.append(img,label);previews.append(card);
    });
  };
  input.addEventListener('change',async()=>{
    const current=++sequence;const originals=[...input.files];processing=true;input.dataset.processing='true';
    const buttons=[...form.querySelectorAll('button[type="submit"],button:not([type])')];buttons.forEach(button=>button.disabled=true);
    input.setCustomValidity('กำลังปรับรูป');message.textContent='กำลังหมุนและย่อรูปก่อนอัปโหลด…';
    try{
      if(originals.length>4)throw Error('เพิ่มรูปใหม่ได้ครั้งละไม่เกิน 4 รูป');
      const transfer=new DataTransfer();
      for(const file of originals)transfer.items.add(await normalizeProductPhoto(file,Number(photoEditor.dataset.maxBytes)));
      if(current!==sequence)return;
      input.files=transfer.files;redrawPhotos();
    }catch(error){if(current===sequence){input.value='';previews.replaceChildren();input.setCustomValidity(error.message);message.textContent=error.message;message.classList.add('error');}}
    finally{if(current===sequence){processing=false;input.dataset.processing='false';buttons.forEach(button=>button.disabled=false);}}
  });
  photoEditor.querySelectorAll('[name="remove_images[]"]').forEach(box=>box.addEventListener('change',redrawPhotos));
  window.addEventListener('pagehide',()=>objectUrls.forEach(url=>URL.revokeObjectURL(url)));
}
document.querySelectorAll('[data-product-gallery]').forEach(gallery=>{
  const image=gallery.querySelector('[data-gallery-main]');
  gallery.querySelectorAll('[data-gallery-thumbnail]').forEach(button=>button.addEventListener('click',()=>{
    image.src=button.dataset.imageSrc;
    gallery.querySelectorAll('[data-gallery-thumbnail]').forEach(item=>item.setAttribute('aria-pressed',String(item===button)));
  }));
});

document.querySelectorAll('[data-toast]').forEach(toast=>{
  const close=()=>toast.remove();
  toast.querySelector('[data-toast-close]')?.addEventListener('click',close);
  setTimeout(close,8000);
});
document.querySelector('.error[role="alert"]')?.scrollIntoView({block:'center'});

(function chatWidget(){
  const root=document.querySelector('[data-chat-widget]');
  if(!root)return;
  root.hidden=false;
  const $=selector=>root.querySelector(selector);
  const api=root.dataset.api;
  const panel=$('[data-chat-panel]'),toggle=$('[data-chat-toggle]'),badge=$('[data-chat-badge]');
  const listView=$('[data-chat-list-view]'),threadView=$('[data-chat-thread-view]');
  const threadList=$('[data-chat-threads]'),emptyNote=$('[data-chat-empty]'),sortSelect=$('[data-chat-sort]');
  const title=$('[data-chat-title]'),subtitle=$('[data-chat-subtitle]'),backButton=$('[data-chat-back]');
  const log=$('[data-chat-log]'),form=$('[data-chat-form]'),input=$('[data-chat-input]'),errorNote=$('[data-chat-error]');
  const storageKey=`clozee.chat.${root.dataset.user}`;
  let saved={};
  try{saved=JSON.parse(localStorage.getItem(storageKey))||{};}catch{saved={};}
  const seen=saved.seen&&typeof saved.seen==='object'?saved.seen:{};
  sortSelect.value=saved.sort==='oldest'?'oldest':'newest';
  const persist=()=>{try{localStorage.setItem(storageKey,JSON.stringify({seen,sort:sortSelect.value}));}catch{}};
  let threads=[],openId=0,lastId=0,stopped=false,timer=0,sending=false;
  const clock=new Intl.DateTimeFormat('th-TH',{timeZone:'Asia/Bangkok',day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'});
  const when=value=>{const date=new Date(value);return Number.isNaN(date.getTime())?'':clock.format(date);};
  const isOpen=()=>!panel.hidden;

  async function call(params,body){
    const response=await fetch(`${api}?${new URLSearchParams(params)}`,body?{method:'POST',body:new URLSearchParams({...body,csrf:root.dataset.csrf}),credentials:'same-origin'}:{credentials:'same-origin'});
    if(response.status===401){stopped=true;root.hidden=true;throw Error('กรุณาเข้าสู่ระบบ');}
    const data=await response.json();
    if(!response.ok){const error=Error(data.error||'ระบบแชทขัดข้อง กรุณาลองอีกครั้ง');error.status=response.status;throw error;}
    return data;
  }
  function renderBadge(){
    const unread=threads.filter(t=>t.last_id>0&&!t.last_mine&&t.last_id>(seen[t.id]||0)&&t.id!==openId).length;
    badge.hidden=unread===0;badge.textContent=unread>9?'9+':String(unread);
    toggle.setAttribute('aria-label',unread?`เปิดแชท มี ${unread} แชทที่ยังไม่ได้อ่าน`:(isOpen()?'ปิดแชท':'เปิดแชท'));
  }
  function renderThreads(){
    const ordered=[...threads].sort((a,b)=>(a.at===b.at?(a.last_id-b.last_id||a.id-b.id):a.at<b.at?-1:1)*(sortSelect.value==='oldest'?1:-1));
    threadList.replaceChildren(...ordered.map(thread=>{
      const item=document.createElement('li');const button=document.createElement('button');
      button.type='button';button.className='chat-thread';button.dataset.threadId=thread.id;
      if(!thread.last_mine&&thread.last_id>(seen[thread.id]||0))button.classList.add('unread');
      const name=document.createElement('strong');name.textContent=thread.name;
      const time=document.createElement('time');time.textContent=when(thread.at);
      const product=document.createElement('small');product.textContent=thread.product;
      const snippet=document.createElement('span');snippet.textContent=thread.snippet?(thread.last_mine?'คุณ: ':'')+thread.snippet:'ยังไม่มีข้อความ';
      button.append(name,time,product,snippet);button.addEventListener('click',()=>openThread(thread.id));
      item.append(button);return item;
    }));
    emptyNote.hidden=threads.length>0;
    renderBadge();
  }
  async function refreshThreads(){threads=(await call({action:'threads'})).threads;if(!openId)renderThreads();else renderBadge();}
  function addMessages(messages){
    if(!messages.length)return;
    const nearBottom=log.scrollHeight-log.scrollTop-log.clientHeight<80;
    messages.forEach(message=>{
      if(message.id<=lastId)return;
      const bubble=document.createElement('div');bubble.className=message.mine?'message mine':'message';bubble.textContent=message.text;
      const meta=document.createElement('small');meta.textContent=`${message.mine?'คุณ':'คู่สนทนา'} · ${when(message.at)} น.`;
      bubble.append(meta);log.append(bubble);lastId=message.id;
    });
    if(nearBottom||messages.some(message=>message.mine))log.scrollTop=log.scrollHeight;
    seen[openId]=Math.max(seen[openId]||0,lastId);persist();
  }
  function showList(){
    openId=0;lastId=0;log.replaceChildren();root.dataset.threadId='';
    listView.hidden=false;threadView.hidden=true;backButton.hidden=true;errorNote.hidden=true;
    title.textContent='แชท';subtitle.textContent='';
    renderThreads();
  }
  function showError(message){errorNote.textContent=message;errorNote.hidden=false;}
  function setPanel(open){
    panel.hidden=!open;toggle.setAttribute('aria-expanded',String(open));root.classList.toggle('open',open);
    if(!open){openId=0;lastId=0;root.dataset.threadId='';}
    renderBadge();schedule();
  }
  async function openThread(id){
    setPanel(true);
    openId=id;lastId=0;log.replaceChildren();root.dataset.threadId=String(id);
    listView.hidden=true;threadView.hidden=false;backButton.hidden=false;errorNote.hidden=true;
    title.textContent='กำลังโหลด…';subtitle.textContent='';
    try{
      const data=await call({action:'messages',id,after:0});
      if(openId!==id)return;
      title.textContent=data.thread.name;subtitle.textContent=data.thread.product;
      addMessages(data.messages);log.scrollTop=log.scrollHeight;renderBadge();input.focus();
    }catch(error){if(error.status===404){await refreshThreads().catch(()=>{});showList();}else showError(error.message);}
    schedule();
  }
  async function tick(){
    if(stopped)return;
    if(document.hidden)return schedule();
    try{
      if(openId&&!sending){
        const id=openId,data=await call({action:'messages',id,after:lastId});
        if(openId===id)addMessages(data.messages);
      }
      await refreshThreads();
    }catch(error){if(error.status===404&&openId)showList();}
    schedule();
  }
  function schedule(){clearTimeout(timer);if(!stopped)timer=setTimeout(tick,openId?3000:isOpen()?5000:10000);}

  toggle.addEventListener('click',()=>{if(isOpen())setPanel(false);else{setPanel(true);showList();refreshThreads().catch(()=>{});}});
  $('[data-chat-close]').addEventListener('click',()=>{setPanel(false);toggle.focus();});
  backButton.addEventListener('click',()=>{showList();refreshThreads().catch(()=>{});schedule();});
  sortSelect.addEventListener('change',()=>{persist();renderThreads();});
  document.addEventListener('keydown',event=>{if(event.key==='Escape'&&isOpen()){setPanel(false);toggle.focus();}});
  input.addEventListener('keydown',event=>{if(event.key==='Enter'&&!event.shiftKey&&!event.isComposing){event.preventDefault();form.requestSubmit();}});
  form.addEventListener('submit',async event=>{
    event.preventDefault();
    const text=input.value.trim();
    if(!text||sending||!openId)return;
    sending=true;errorNote.hidden=true;const id=openId;
    try{
      await call({},{action:'send',conversation_id:id,message_text:text});
      input.value='';
      const data=await call({action:'messages',id,after:lastId});
      if(openId===id)addMessages(data.messages);
      await refreshThreads();
    }catch(error){showError(error.message);}
    finally{sending=false;input.focus();}
  });
  document.querySelectorAll('[data-chat-open]').forEach(link=>link.addEventListener('click',event=>{
    event.preventDefault();setPanel(true);showList();refreshThreads().catch(()=>{});
  }));
  document.querySelectorAll('[data-chat-start]').forEach(start=>start.addEventListener('submit',async event=>{
    event.preventDefault();
    try{
      const data=await call({},{action:'start',product_id:start.querySelector('[name="product_id"]').value});
      await refreshThreads();await openThread(data.conversation_id);
    }catch(error){setPanel(true);showList();emptyNote.hidden=false;emptyNote.textContent=error.message;}
  }));
  const page=document.querySelector('[data-chat-page]');
  if(page){
    document.querySelector('[data-chat-fallback]').hidden=true;
    document.querySelector('[data-chat-page-note]').hidden=false;
    const id=Number(page.dataset.thread);
    if(id)openThread(id);else{setPanel(true);showList();}
  }
  refreshThreads().catch(()=>{}).finally(schedule);
})();

// Show that a form is being sent so a slow save is not clicked twice.
document.querySelectorAll('form[method="post"]').forEach(form=>form.addEventListener('submit',event=>{
  const button=event.submitter;
  if(event.defaultPrevented||!button||!button.classList.contains('button'))return;
  setTimeout(()=>{button.disabled=true;button.dataset.label=button.textContent;button.textContent='กำลังบันทึก…';},0);
}));
window.addEventListener('pageshow',()=>document.querySelectorAll('button[data-label]').forEach(button=>{button.disabled=false;button.textContent=button.dataset.label;delete button.dataset.label;}));
