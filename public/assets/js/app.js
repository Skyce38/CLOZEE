document.querySelectorAll('[data-confirm]').forEach(form=>form.addEventListener('submit',event=>{if(!window.confirm(form.dataset.confirm))event.preventDefault();}));
const addVariant=document.querySelector('[data-add-variant]');
if(addVariant) addVariant.addEventListener('click',()=>{
  const list=document.querySelector('[data-variants]');
  if(list.children.length>=12)return;
  const row=document.querySelector('#variant-template').content.cloneNode(true);
  row.querySelectorAll('[name]').forEach(input=>input.name=input.name.replace('__INDEX__',String(list.children.length)));
  list.append(row);
});

const photoEditor=document.querySelector('[data-photo-editor]');
if(photoEditor){
  const input=photoEditor.querySelector('[data-photo-input]');
  const previews=photoEditor.querySelector('[data-photo-previews]');
  const message=photoEditor.querySelector('[data-photo-message]');
  let objectUrls=[];
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
  input.addEventListener('change',redrawPhotos);
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
