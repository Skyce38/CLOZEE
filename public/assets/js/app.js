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
