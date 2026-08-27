(() => {
  const toggle = document.querySelector('[data-t2-menu-toggle]');
  const nav = document.querySelector('[data-t2-nav]');
  if (!toggle || !nav) return;

  const setMenuState = (open, { restoreFocus = false } = {}) => {
    toggle.setAttribute('aria-expanded', String(open));
    nav.classList.toggle('is-open', open);
    document.body.classList.toggle('t2-menu-open', open);
    if (restoreFocus) toggle.focus();
  };

  toggle.addEventListener('click', () => {
    const open = toggle.getAttribute('aria-expanded') === 'true';
    setMenuState(!open);
  });

  nav.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
    setMenuState(false);
  }));

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape' || toggle.getAttribute('aria-expanded') !== 'true') return;
    setMenuState(false, { restoreFocus: true });
  });

  const desktopQuery = window.matchMedia('(min-width: 981px)');
  const resetForDesktop = (event) => {
    if (event.matches) setMenuState(false);
  };

  if (typeof desktopQuery.addEventListener === 'function') {
    desktopQuery.addEventListener('change', resetForDesktop);
  } else if (typeof desktopQuery.addListener === 'function') {
    desktopQuery.addListener(resetForDesktop);
  }
})();
