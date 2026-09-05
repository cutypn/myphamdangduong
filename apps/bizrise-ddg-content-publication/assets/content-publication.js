(function(){
'use strict';
var state={brand:'',benefit:''};
function applyFilters(){
  document.querySelectorAll('.ddgc-product-card[data-brand]').forEach(function(card){
    var okBrand=!state.brand||card.getAttribute('data-brand')===state.brand;
    var okBenefit=!state.benefit||card.getAttribute('data-benefit')===state.benefit;
    card.hidden=!(okBrand&&okBenefit);
  });
}
document.addEventListener('click',function(e){
  var toggle=e.target.closest('.ddgc-menu-toggle');
  if(toggle){
    var nav=document.getElementById('ddgc-primary');
    if(!nav)return;
    var open=nav.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded',open?'true':'false');
    return;
  }
  var filter=e.target.closest('[data-filter-type]');
  if(filter){
    var type=filter.getAttribute('data-filter-type');
    var value=filter.getAttribute('data-filter-value')||'';
    state[type]=value;
    filter.closest('.ddgc-filter-row').querySelectorAll('button').forEach(function(btn){btn.classList.toggle('is-active',btn===filter);});
    applyFilters();
  }
});
})();
