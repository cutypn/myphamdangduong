document.addEventListener('DOMContentLoaded', function(){
  const btn = document.querySelector('.menu-toggle');
  const nav = document.querySelector('.primary-nav');
  const mq = window.matchMedia('(max-width: 1280px)');

  if (!btn || !nav) return;

  const parentItems = nav.querySelectorAll('.menu-item-has-children');
  parentItems.forEach(function(item){
    const link = item.querySelector(':scope > a');
    const submenu = item.querySelector(':scope > .sub-menu');
    if (!link || !submenu) return;

    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'submenu-toggle';
    toggle.setAttribute('aria-expanded', 'false');
    toggle.setAttribute('aria-label', 'Mở menu con ' + link.textContent.trim());
    item.insertBefore(toggle, submenu);

    toggle.addEventListener('click', function(e){
      e.preventDefault();
      e.stopPropagation();
      const opening = !item.classList.contains('submenu-open');
      parentItems.forEach(function(other){
        if (other !== item) {
          other.classList.remove('submenu-open');
          const otherToggle = other.querySelector(':scope > .submenu-toggle');
          if (otherToggle) otherToggle.setAttribute('aria-expanded', 'false');
        }
      });
      item.classList.toggle('submenu-open', opening);
      toggle.setAttribute('aria-expanded', opening ? 'true' : 'false');
    });
  });

  function closeMenu(){
    nav.classList.remove('open');
    btn.setAttribute('aria-expanded', 'false');
    btn.setAttribute('aria-label', 'Mở menu chính');
    parentItems.forEach(function(item){
      item.classList.remove('submenu-open');
      const toggle = item.querySelector(':scope > .submenu-toggle');
      if (toggle) toggle.setAttribute('aria-expanded', 'false');
    });
  }

  btn.addEventListener('click', function(e){
    e.stopPropagation();
    const open = nav.classList.toggle('open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    btn.setAttribute('aria-label', open ? 'Đóng menu chính' : 'Mở menu chính');
  });

  nav.addEventListener('click', function(e){
    if (mq.matches && e.target.closest('a') && !e.target.closest('.menu-item-has-children > a')) {
      closeMenu();
    }
  });

  document.addEventListener('click', function(e){
    if (mq.matches && nav.classList.contains('open') && !nav.contains(e.target) && !btn.contains(e.target)) {
      closeMenu();
    }
  });

  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') closeMenu();
  });

  window.addEventListener('resize', function(){
    if (!mq.matches) closeMenu();
  });
});
