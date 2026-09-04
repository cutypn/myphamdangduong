(function(){
  'use strict';
  function closest(el, selector){ return el && el.closest ? el.closest(selector) : null; }
  document.addEventListener('click', function(event){
    var thumb = closest(event.target, '[data-ddg-thumb]');
    if (thumb) {
      var gallery = closest(thumb, '[data-ddg-gallery]');
      if (!gallery) { return; }
      var picture = gallery.querySelector('[data-ddg-main-media] picture');
      var img = gallery.querySelector('[data-ddg-main-media] img');
      if (!img) { return; }
      var src = thumb.getAttribute('data-src');
      if (!src) { return; }
      if (picture) { picture.querySelectorAll('source').forEach(function(source){ source.remove(); }); }
      img.src = src;
      var srcset = thumb.getAttribute('data-srcset');
      if (srcset) { img.srcset = srcset; } else { img.removeAttribute('srcset'); }
      img.sizes = '(max-width: 767px) 100vw, (max-width: 1199px) 52vw, 620px';
      img.width = parseInt(thumb.getAttribute('data-width') || '1', 10);
      img.height = parseInt(thumb.getAttribute('data-height') || '1', 10);
      img.alt = thumb.getAttribute('data-alt') || img.alt;
      var zoom = gallery.querySelector('[data-ddg-zoom]');
      if (zoom) { zoom.setAttribute('data-zoom-src', src); }
      gallery.querySelectorAll('[data-ddg-thumb]').forEach(function(item){ item.classList.toggle('is-active', item === thumb); });
      return;
    }
    var tab = closest(event.target, '[data-ddg-tabs] [role="tab"]');
    if (tab) {
      var tabs = closest(tab, '[data-ddg-tabs]');
      var targetId = tab.getAttribute('aria-controls');
      if (!tabs || !targetId) { return; }
      tabs.querySelectorAll('[role="tab"]').forEach(function(item){ var active=item===tab; item.classList.toggle('is-active',active); item.setAttribute('aria-selected',active?'true':'false'); });
      tabs.querySelectorAll('[role="tabpanel"]').forEach(function(panel){ var active=panel.id===targetId; panel.classList.toggle('is-active',active); panel.hidden=!active; });
      return;
    }
    var zoomButton = closest(event.target, '[data-ddg-zoom]');
    if (zoomButton) {
      var zoomSrc = zoomButton.getAttribute('data-zoom-src'); if (!zoomSrc) { return; }
      var overlay=document.createElement('div'); overlay.className='ddg-zoom-overlay'; overlay.setAttribute('role','dialog'); overlay.setAttribute('aria-modal','true');
      overlay.innerHTML='<button type="button" class="ddg-zoom-overlay__close" aria-label="Đóng ảnh">×</button><img alt="" decoding="async">';
      overlay.querySelector('img').src=zoomSrc; document.body.appendChild(overlay); document.body.classList.add('ddg-zoom-open'); overlay.querySelector('.ddg-zoom-overlay__close').focus(); return;
    }
    var close=closest(event.target,'.ddg-zoom-overlay__close'); var overlayClick=closest(event.target,'.ddg-zoom-overlay');
    if (close || (overlayClick && event.target===overlayClick)) { var overlayToClose=close?closest(close,'.ddg-zoom-overlay'):overlayClick; if(overlayToClose)overlayToClose.remove(); document.body.classList.remove('ddg-zoom-open'); }
  });
  document.addEventListener('keydown',function(event){ if(event.key!=='Escape')return; var overlay=document.querySelector('.ddg-zoom-overlay'); if(overlay){overlay.remove();document.body.classList.remove('ddg-zoom-open');} });
})();
