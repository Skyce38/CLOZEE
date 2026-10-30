document.querySelectorAll('[data-confirm]').forEach(form=>form.addEventListener('submit',event=>{if(!window.confirm(form.dataset.confirm))event.preventDefault();}));
const addVariant=document.querySelector('[data-add-variant]');
if(addVariant) addVariant.addEventListener('click',()=>{
  const list=document.querySelector('[data-variants]');
  if(list.children.length>=12)return;
  const row=document.querySelector('#variant-template').content.cloneNode(true);
  row.querySelectorAll('[name]').forEach(input=>input.name=input.name.replace('__INDEX__',String(list.children.length)));
  list.append(row);
});
