(() => {
  const toggle = document.querySelector('[data-t2-menu-toggle]');
  const nav = document.querySelector('[data-t2-nav]');
  if (!toggle || !nav) return;

  const toggleLabel = toggle.querySelector('.screen-reader-text');
  const submenuItems = Array.from(nav.querySelectorAll('.menu-item-has-children'));

  const setToggleLabel = (open) => {
    const text = open ? 'Đóng menu' : 'Mở menu';
    toggle.setAttribute('aria-label', text);
    if (toggleLabel) toggleLabel.textContent = text;
  };

  const setSubmenuState = (item, open) => {
    const button = item.querySelector(':scope > .t2-submenu-toggle');
    if (!button) return;
    const parentLabel = button.dataset.parentLabel || 'menu con';
    button.setAttribute('aria-expanded', String(open));
    button.setAttribute('aria-label', `${open ? 'Đóng' : 'Mở'} menu con: ${parentLabel}`);
  };

  const closeSubmenus = () => {
    submenuItems.forEach((item) => {
      item.classList.remove('is-submenu-open');
      setSubmenuState(item, false);
    });
  };

  submenuItems.forEach((item, index) => {
    const parentLink = item.querySelector(':scope > a');
    const submenu = item.querySelector(':scope > .sub-menu');
    if (!parentLink || !submenu) return;

    if (!submenu.id) submenu.id = `t2-submenu-${index + 1}`;

    const parentLabel = parentLink.textContent.trim();
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 't2-submenu-toggle';
    button.dataset.parentLabel = parentLabel;
    button.setAttribute('aria-expanded', 'false');
    button.setAttribute('aria-controls', submenu.id);
    button.setAttribute('aria-label', `Mở menu con: ${parentLabel}`);
    button.innerHTML = '<span aria-hidden="true">⌄</span>';
    parentLink.insertAdjacentElement('afterend', button);

    button.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      const willOpen = !item.classList.contains('is-submenu-open');
      closeSubmenus();
      item.classList.toggle('is-submenu-open', willOpen);
      setSubmenuState(item, willOpen);
    });
  });

  const setMenuState = (open, { restoreFocus = false } = {}) => {
    toggle.setAttribute('aria-expanded', String(open));
    setToggleLabel(open);
    nav.classList.toggle('is-open', open);
    document.body.classList.toggle('t2-menu-open', open);
    if (!open) closeSubmenus();
    if (restoreFocus) toggle.focus();
  };

  setToggleLabel(false);

  toggle.addEventListener('click', () => {
    const open = toggle.getAttribute('aria-expanded') === 'true';
    setMenuState(!open);
  });

  nav.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
    setMenuState(false);
  }));

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;

    const openSubmenu = nav.querySelector('.menu-item-has-children.is-submenu-open');
    if (openSubmenu) {
      const button = openSubmenu.querySelector(':scope > .t2-submenu-toggle');
      closeSubmenus();
      if (button) button.focus();
      return;
    }

    if (toggle.getAttribute('aria-expanded') === 'true') {
      setMenuState(false, { restoreFocus: true });
    }
  });

  // Keep JS state aligned with the CSS breakpoint: hamburger/touch nav is used
  // through 1180px; only wider viewports are treated as full desktop nav.
  const desktopQuery = window.matchMedia('(min-width: 1181px)');
  const resetForDesktop = (event) => {
    if (event.matches) setMenuState(false);
  };

  if (typeof desktopQuery.addEventListener === 'function') {
    desktopQuery.addEventListener('change', resetForDesktop);
  } else if (typeof desktopQuery.addListener === 'function') {
    desktopQuery.addListener(resetForDesktop);
  }
})();
