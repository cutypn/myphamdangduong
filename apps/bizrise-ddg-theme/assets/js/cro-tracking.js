(() => {
  'use strict';

  const CAMPAIGN_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid'];
  const STARTED_FORMS = new WeakSet();
  const FIRED_SCROLL = new Set();

  const readCampaign = () => {
    const params = new URLSearchParams(window.location.search);
    const campaign = {};

    CAMPAIGN_KEYS.forEach((key) => {
      const value = params.get(key);
      if (value) campaign[key] = value.slice(0, 255);
    });

    if (Object.keys(campaign).length) {
      try {
        window.sessionStorage.setItem('ddg_campaign', JSON.stringify(campaign));
      } catch (error) {
        // Storage may be unavailable in strict privacy contexts; tracking remains optional.
      }
      return campaign;
    }

    try {
      return JSON.parse(window.sessionStorage.getItem('ddg_campaign') || '{}') || {};
    } catch (error) {
      return {};
    }
  };

  const campaign = readCampaign();

  const eventPayload = (name, detail = {}) => ({
    event: name,
    page_location: window.location.href,
    page_path: window.location.pathname,
    ...campaign,
    ...detail,
  });

  const emit = (name, detail = {}) => {
    const payload = eventPayload(name, detail);

    window.dispatchEvent(new CustomEvent('ddg:tracking', { detail: payload }));

    if (Array.isArray(window.dataLayer)) {
      window.dataLayer.push(payload);
    }

    if (typeof window.gtag === 'function') {
      const { event, ...params } = payload;
      window.gtag('event', event, params);
    }
  };

  const labelFor = (element) => {
    const explicit = element.getAttribute('data-track-label');
    if (explicit) return explicit.trim().slice(0, 160);
    return (element.textContent || element.getAttribute('aria-label') || '').trim().replace(/\s+/g, ' ').slice(0, 160);
  };

  const formName = (form) => form.getAttribute('name') || form.getAttribute('id') || form.getAttribute('class') || 'form';

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
      if (STARTED_FORMS.has(form)) return;
      STARTED_FORMS.add(form);
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
    const link = event.target.closest('a, button');
    if (!link) return;

    const href = link instanceof HTMLAnchorElement ? link.getAttribute('href') || '' : '';
    const label = labelFor(link);

    if (/^tel:/i.test(href)) {
      emit('ClickPhone', { link_url: href, link_text: label });
      return;
    }

    if (/zalo\.me|zalo:/i.test(href)) {
      emit('ClickZalo', { link_url: href, link_text: label });
      return;
    }

    if (link.matches('.t2-btn, .t2-text-link, [data-ddg-cta]')) {
      emit('ClickCTA', { link_url: href, link_text: label });
    }
  });

  const handleScroll = () => {
    const doc = document.documentElement;
    const maxScroll = Math.max(1, doc.scrollHeight - window.innerHeight);
    const percent = Math.min(100, Math.round((window.scrollY / maxScroll) * 100));

    [50, 90].forEach((threshold) => {
      if (percent >= threshold && !FIRED_SCROLL.has(threshold)) {
        FIRED_SCROLL.add(threshold);
        emit('scroll', { percent_scrolled: threshold });
      }
    });
  };

  let scrollQueued = false;
  window.addEventListener('scroll', () => {
    if (scrollQueued) return;
    scrollQueued = true;
    window.requestAnimationFrame(() => {
      scrollQueued = false;
      handleScroll();
    });
  }, { passive: true });
})();
