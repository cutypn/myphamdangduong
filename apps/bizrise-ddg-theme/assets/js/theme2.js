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

(() => {
  'use strict';

  const campaignKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid'];
  const startedForms = new WeakSet();
  const firedScroll = new Set();

  const readCampaign = () => {
    const params = new URLSearchParams(window.location.search);
    const next = {};

    campaignKeys.forEach((key) => {
      const value = params.get(key);
      if (value) next[key] = value.slice(0, 255);
    });

    if (Object.keys(next).length) {
      try {
        window.sessionStorage.setItem('ddg_campaign', JSON.stringify(next));
      } catch (error) {
        // Attribution remains optional if storage is unavailable.
      }
      return next;
    }

    try {
      return JSON.parse(window.sessionStorage.getItem('ddg_campaign') || '{}') || {};
    } catch (error) {
      return {};
    }
  };

  const campaign = readCampaign();

  const emit = (name, detail = {}) => {
    const payload = {
      event: name,
      page_location: window.location.href,
      page_path: window.location.pathname,
      ...campaign,
      ...detail,
    };

    window.dispatchEvent(new CustomEvent('ddg:tracking', { detail: payload }));

    if (typeof window.gtag === 'function') {
      const { event, ...params } = payload;
      window.gtag('event', event, params);
    } else if (Array.isArray(window.dataLayer)) {
      window.dataLayer.push(payload);
    }
  };

  const labelFor = (element) => {
    const explicit = element.getAttribute('data-track-label');
    if (explicit) return explicit.trim().slice(0, 160);
    return (element.textContent || element.getAttribute('aria-label') || '')
      .trim()
      .replace(/\s+/g, ' ')
      .slice(0, 160);
  };

  const formName = (form) => form.getAttribute('name') || form.getAttribute('id') || 'form';

  const hydrateCampaignFields = (form) => {
    Object.entries(campaign).forEach(([key, value]) => {
      let field = form.querySelector(`input[name="${key}"]`);
      if (!field) {
        field = document.createElement('input');
        field.type = 'hidden';
        field.name = key;
        field.setAttribute('data-ddg-campaign-field', '1');
        form.appendChild(field);
      }
      if (!field.value) field.value = value;
    });
  };

  document.querySelectorAll('form').forEach((form) => {
    hydrateCampaignFields(form);

    const start = () => {
      if (startedForms.has(form)) return;
      startedForms.add(form);
      emit('form_start', { form_name: formName(form) });
    };

    form.addEventListener('focusin', start, { once: true });
    form.addEventListener('input', start, { once: true });
    form.addEventListener('submit', () => {
      hydrateCampaignFields(form);
      emit('form_submit', { form_name: formName(form) });
    });
  });

  document.addEventListener('click', (event) => {
    const element = event.target.closest('a, button');
    if (!element) return;

    const href = element instanceof HTMLAnchorElement ? element.getAttribute('href') || '' : '';
    const label = labelFor(element);

    if (/^tel:/i.test(href)) {
      emit('ClickPhone', { link_url: href, link_text: label });
      return;
    }

    if (/zalo\.me|zalo:/i.test(href)) {
      emit('ClickZalo', { link_url: href, link_text: label });
      return;
    }

    if (element.matches('.t2-btn, .t2-text-link, [data-ddg-cta]')) {
      emit('ClickCTA', { link_url: href, link_text: label });
    }
  });

  let scrollQueued = false;
  const handleScroll = () => {
    const doc = document.documentElement;
    const maxScroll = Math.max(1, doc.scrollHeight - window.innerHeight);
    const percent = Math.min(100, Math.round((window.scrollY / maxScroll) * 100));

    [50, 90].forEach((threshold) => {
      if (percent >= threshold && !firedScroll.has(threshold)) {
        firedScroll.add(threshold);
        emit('scroll', { percent_scrolled: threshold });
      }
    });
  };

  window.addEventListener('scroll', () => {
    if (scrollQueued) return;
    scrollQueued = true;
    window.requestAnimationFrame(() => {
      scrollQueued = false;
      handleScroll();
    });
  }, { passive: true });
})();
