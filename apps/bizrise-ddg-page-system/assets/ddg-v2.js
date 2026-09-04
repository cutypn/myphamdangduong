(function(){
'use strict';
document.addEventListener('click',function(e){
  var toggle=e.target.closest('.ddg-menu-toggle');
  if(toggle){
    var nav=document.getElementById('ddg-primary-nav');
    if(nav){var open=nav.classList.toggle('is-open');toggle.setAttribute('aria-expanded',open?'true':'false');}
    return;
  }
  var chip=e.target.closest('[data-ddg-filter] button');
  if(chip){
    var brand=chip.getAttribute('data-brand')||'';
    chip.parentElement.querySelectorAll('button').forEach(function(b){b.classList.toggle('is-active',b===chip);});
    document.querySelectorAll('.ddg-product-card[data-brand]').forEach(function(card){
      card.style.display=!brand||card.getAttribute('data-brand')===brand?'':'none';
    });
    return;
  }
  var tab=e.target.closest('[data-tabs] button');
  if(tab){
    var wrap=tab.closest('.ddg-shell');
    var key=tab.getAttribute('data-tab');
    tab.parentElement.querySelectorAll('button').forEach(function(b){b.classList.toggle('is-active',b===tab);});
    wrap.querySelectorAll('[data-panel]').forEach(function(panel){panel.classList.toggle('is-active',panel.getAttribute('data-panel')===key);});
  }
});
})();
