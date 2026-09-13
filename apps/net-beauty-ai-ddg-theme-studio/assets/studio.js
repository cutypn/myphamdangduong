(function(){
'use strict';
const $=s=>document.querySelector(s);
const esc=s=>String(s??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
const els={brand:$('#netddg-brand'),type:$('#netddg-type'),title:$('#netddg-title'),keyword:$('#netddg-keyword'),intent:$('#netddg-intent'),facts:$('#netddg-facts'),claims:$('#netddg-claims'),notes:$('#netddg-notes'),html:$('#netddg-html'),status:$('#netddg-status'),card:$('#netddg-brand-card'),frame:$('#netddg-preview-frame')};
function profile(){return NET_DDG_STUDIO.profiles[els.brand.value]||NET_DDG_STUDIO.profiles['dang-duong-group'];}
function renderBrand(){const p=profile();els.card.innerHTML='<strong>'+esc(p.label)+'</strong><p>'+esc(p.site_role)+'</p><p><b>Voice:</b> '+esc(p.voice)+'</p><p><b>Theme:</b> '+esc(p.theme)+'</p>';}
function payload(extra={}){return new URLSearchParams(Object.assign({action:'net_ddg_build_html',nonce:NET_DDG_STUDIO.nonce,brand:els.brand.value,type:els.type.value,title:els.title.value,keyword:els.keyword.value,intent:els.intent.value,facts:els.facts.value,claims:els.claims.value,notes:els.notes.value},extra));}
async function build(){els.status.textContent='Đang dựng...';const r=await fetch(NET_DDG_STUDIO.ajax,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},body:payload()});const j=await r.json();if(!j.success){els.status.textContent='Lỗi';alert(j.data?.message||'Không dựng được HTML');return;}els.html.value=j.data.html;els.html.dataset.prompt=j.data.prompt;els.status.textContent='Đã dựng theo '+profile().label;preview();}
function preview(){
  const html=els.html.value;
  els.frame.srcdoc=`<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>
  :root{--ddg-red:#9b0d16;--ddg-ink:#302927;--ddg-muted:#716866;--ddg-line:#eadbd7;--ddg-soft:#fff8f5}
  *{box-sizing:border-box}
  body{margin:0;font-family:"Be Vietnam Pro",system-ui,sans-serif;color:var(--ddg-ink);line-height:1.7;background:#fff}
  .section{padding:54px 0;border-bottom:1px solid var(--ddg-line)}
  .container{width:min(1180px,calc(100% - 48px));margin:0 auto}
  .container.narrow{width:min(920px,calc(100% - 48px))}
  .section-heading{margin:0 0 26px}
  .section-heading .eyebrow,.eyebrow{display:block;margin:0 0 8px;color:var(--ddg-red);font-size:12px;font-weight:900;letter-spacing:.12em;text-transform:uppercase}
  .section-heading h2,.cta-section h2{margin:0 0 12px;font:800 32px/1.2 "Be Vietnam Pro",sans-serif}
  .section-heading h2{color:var(--ddg-red)}
  .section-heading p{margin:0;color:var(--ddg-muted)}
  .stats-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
  .stats-grid>div{padding:18px;border:1px solid var(--ddg-line);border-radius:12px;background:#fff}
  .stats-grid b,.stats-grid span{display:block}.stats-grid b{color:var(--ddg-red);margin-bottom:6px}.stats-grid span{font-size:14px;color:var(--ddg-muted)}
  .news-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
  .news-card{border:1px solid var(--ddg-line);border-radius:14px;background:#fff;overflow:hidden}
  .news-card__body{padding:20px}.news-card__body h3{margin:0 0 8px;font-size:18px}.news-card__body p{margin:0;color:var(--ddg-muted)}
  .partners-row{display:flex;flex-wrap:wrap;gap:10px}.partners-row span{padding:9px 12px;border:1px solid var(--ddg-line);border-radius:999px;background:var(--ddg-soft);font-size:13px}
  .cta-section{padding:44px 0;background:linear-gradient(120deg,#850811,#b30f19);color:#fff}
  .cta-grid{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:28px;align-items:center}
  .cta-section .eyebrow{color:#fff;opacity:.8}.cta-section p{margin:0;opacity:.9}
  .btn{display:inline-flex;align-items:center;justify-content:center;min-height:48px;padding:0 20px;border-radius:8px;text-decoration:none;font-weight:800}
  .btn--light{background:#fff;color:var(--ddg-red)}
  @media(max-width:700px){.section{padding:36px 0}.container,.container.narrow{width:min(100% - 32px,920px)}.stats-grid,.news-grid,.cta-grid{grid-template-columns:1fr}.section-heading h2,.cta-section h2{font-size:26px}}
  </style></head><body><main class="entry-content">${html}</main></body></html>`;
}
async function copyText(text,msg){await navigator.clipboard.writeText(text);els.status.textContent=msg;}
function exportHtml(){const blob=new Blob([els.html.value],{type:'text/html;charset=utf-8'});const a=document.createElement('a');a.href=URL.createObjectURL(blob);const slug=(els.title.value||profile().label).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');a.download=(slug||'net-beauty-ai')+'.html';a.click();URL.revokeObjectURL(a.href);}
async function saveDraft(){if(!els.html.value.trim()){alert('HTML đang trống.');return;}const body=payload({action:'net_ddg_save_draft',html:els.html.value});const r=await fetch(NET_DDG_STUDIO.ajax,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},body});const j=await r.json();if(!j.success){alert(j.data?.message||'Không lưu được.');return;}els.status.innerHTML='Đã lưu draft #'+j.data.post_id;window.open(j.data.edit_url,'_blank','noopener');}
els.brand.value=NET_DDG_STUDIO.currentBrand||'dang-duong-group';renderBrand();els.brand.addEventListener('change',renderBrand);$('#netddg-build').addEventListener('click',e=>{e.preventDefault();build();});$('#netddg-preview').addEventListener('click',e=>{e.preventDefault();preview();});$('#netddg-copy-html').addEventListener('click',e=>{e.preventDefault();copyText(els.html.value,'Đã copy HTML');});$('#netddg-export-html').addEventListener('click',e=>{e.preventDefault();exportHtml();});$('#netddg-copy-prompt').addEventListener('click',async e=>{e.preventDefault();if(!els.html.dataset.prompt)await build();copyText(els.html.dataset.prompt||'','Đã copy prompt AI');});$('#netddg-save-draft').addEventListener('click',e=>{e.preventDefault();saveDraft();});
})();