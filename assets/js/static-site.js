(() => {
  const packages = window.UMRA_PACKAGES || [];
  const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
  const prices = item => (item.priceRows || []).length
    ? `<div class="package-price-rows" aria-label="Çmimet sipas dhomës">${item.priceRows.map(row => `<div><span>${esc(row.label)}</span><strong>${esc(row.price)}</strong></div>`).join('')}</div>`
    : '';
  const card = item => {
    const quote = item.price.toLowerCase().includes('kontaktoni');
    const bookingHref = item.slug === 'umre-e-personalizuar' ? 'personalizuar.html' : `rezervo.html?paketa=${encodeURIComponent(item.slug)}`;
    const bookingLabel = item.slug === 'umre-e-personalizuar' ? 'Kërko ofertë' : 'Rezervo';
    const route = (item.route || []).length
      ? `<div class="package-route" aria-label="Itinerari ${esc(item.route.join(' – '))}">${item.route.map((stop, index) => `${index ? '<span class="route-leg" aria-hidden="true">✈</span>' : ''}<b>${esc(stop)}</b>`).join('')}</div>`
      : `<div class="package-route package-route-custom"><b>UDHËTIM SIPAS KËRKESËS</b></div>`;
    const flyers = esc(JSON.stringify(item.flyers || []));
    return `<article class="package-card">
      <div class="package-cover"><img src="${esc(item.image)}" alt="Pamje nga udhëtimi në Mekë dhe Medinë" loading="lazy"><span class="package-tag">${esc(item.tag)}</span><a class="package-cover-title" href="paketa.html?slug=${encodeURIComponent(item.slug)}"><span>${esc(item.title)}</span></a></div>
      <div class="package-card-content">
        ${route}
        <div class="package-meta"><span><b aria-hidden="true">▣</b>${esc(item.date)}</span><span><b aria-hidden="true">⌖</b>${esc(item.stay || 'Mekë · Medinë')}</span></div>
        <div class="price-lockup">${quote ? '' : '<span>Nga</span>'}<strong>${esc(item.price)}</strong>${quote ? '' : '<span>/ person</span>'}</div>
        ${prices(item)}
        <p class="package-highlight"><span aria-hidden="true">✳</span>${esc(item.highlight || item.summary)}</p>
        <div class="package-cta-row"><a class="button button-dark button-small" href="${bookingHref}">${bookingLabel}</a><button class="button button-light button-small flyer-open" type="button" data-flyers="${flyers}" aria-label="Hap fletushkën: ${esc(item.title)}">Fletushka</button></div>
      </div></article>`;
  };

  document.querySelectorAll('[data-home-packages]').forEach(target => {
    const selected = packages.filter(p => !p.tag.toLowerCase().includes('ramazan'));
    target.innerHTML = selected.slice(0, 3).map(card).join('');
  });
  document.querySelectorAll('[data-ramadan-packages]').forEach(target => {
    target.innerHTML = packages.filter(p => p.tag.toLowerCase().includes('ramazan')).map(card).join('');
  });
  document.querySelectorAll('[data-package-list]').forEach(target => {
    target.innerHTML = packages.map(card).join('');
  });

  const flyerModal = document.createElement('div');
  flyerModal.className = 'flyer-modal';
  flyerModal.hidden = true;
  flyerModal.setAttribute('role', 'dialog');
  flyerModal.setAttribute('aria-modal', 'true');
  flyerModal.setAttribute('aria-label', 'Fletushka e paketës');
  flyerModal.innerHTML = `<div class="flyer-backdrop" data-flyer-close></div><div class="flyer-dialog"><button class="flyer-close" type="button" data-flyer-close aria-label="Mbyll fletushkën">×</button><button class="flyer-arrow flyer-prev" type="button" aria-label="Fotoja e mëparshme">‹</button><img class="flyer-image" src="" alt="Fletushka e paketës"><button class="flyer-arrow flyer-next" type="button" aria-label="Fotoja tjetër">›</button><div class="flyer-count" aria-live="polite"></div></div>`;
  document.body.append(flyerModal);
  const flyerImage = flyerModal.querySelector('.flyer-image');
  const flyerCount = flyerModal.querySelector('.flyer-count');
  let activeFlyers = [];
  let flyerIndex = 0;
  let lastFlyerTrigger = null;
  const renderFlyer = () => {
    flyerImage.src = activeFlyers[flyerIndex];
    flyerImage.alt = `Fletushka ${flyerIndex + 1} nga ${activeFlyers.length}`;
    flyerCount.textContent = activeFlyers.length > 1 ? `${flyerIndex + 1} / ${activeFlyers.length}` : '';
    flyerModal.querySelector('.flyer-prev').hidden = activeFlyers.length < 2;
    flyerModal.querySelector('.flyer-next').hidden = activeFlyers.length < 2;
  };
  const closeFlyer = () => {
    flyerModal.hidden = true;
    document.body.classList.remove('flyer-open');
    lastFlyerTrigger?.focus();
  };
  document.addEventListener('click', event => {
    const trigger = event.target.closest('.flyer-open');
    if (trigger) {
      try { activeFlyers = JSON.parse(trigger.dataset.flyers || '[]'); } catch { activeFlyers = []; }
      if (!activeFlyers.length) return;
      lastFlyerTrigger = trigger;
      flyerIndex = 0;
      renderFlyer();
      flyerModal.hidden = false;
      document.body.classList.add('flyer-open');
      flyerModal.querySelector('.flyer-close').focus();
      return;
    }
    if (event.target.closest('[data-flyer-close]')) closeFlyer();
    if (event.target.closest('.flyer-prev') && activeFlyers.length > 1) {
      flyerIndex = (flyerIndex - 1 + activeFlyers.length) % activeFlyers.length;
      renderFlyer();
    }
    if (event.target.closest('.flyer-next') && activeFlyers.length > 1) {
      flyerIndex = (flyerIndex + 1) % activeFlyers.length;
      renderFlyer();
    }
  });
  document.addEventListener('keydown', event => {
    if (flyerModal.hidden) return;
    if (event.key === 'Escape') closeFlyer();
    if (event.key === 'ArrowLeft' && activeFlyers.length > 1) {
      flyerIndex = (flyerIndex - 1 + activeFlyers.length) % activeFlyers.length;
      renderFlyer();
    }
    if (event.key === 'ArrowRight' && activeFlyers.length > 1) {
      flyerIndex = (flyerIndex + 1) % activeFlyers.length;
      renderFlyer();
    }
  });

  const packagePage = document.querySelector('[data-package-detail]');
  if (packagePage) {
    const slug = new URLSearchParams(window.location.search).get('slug');
    const item = packages.find(p => p.slug === slug);
    if (item) {
      const quote = item.price.toLowerCase().includes('kontaktoni');
      const bookingHref = item.slug === 'umre-e-personalizuar' ? 'personalizuar.html' : `rezervo.html?paketa=${encodeURIComponent(item.slug)}`;
      const bookingLabel = item.slug === 'umre-e-personalizuar' ? 'Kërko ofertë' : 'Kërko rezervim';
      packagePage.innerHTML = `<div class="wrap"><div class="detail-top"><div class="detail-image"><img src="${esc(item.image)}" alt="${esc(item.title)}"></div><div class="detail-copy">
        <p class="eyebrow section-kicker">${esc(item.tag)}</p><h2>${esc(item.title)}</h2><p>${esc(item.description)}</p>
        <div class="detail-price"><small>${quote ? 'Çmimi sipas kërkesës' : 'Çmimi fillestar, për person'}</small><strong>${esc(item.price)}</strong></div>
        ${prices(item)}
        <ul class="feature-list">${item.features.map(feature => `<li>${esc(feature)}</li>`).join('')}</ul>
        <div class="detail-actions"><a class="button button-dark" href="${bookingHref}">${bookingLabel} <span>↗</span></a><button class="button button-light flyer-open" type="button" data-flyers="${esc(JSON.stringify(item.flyers || []))}">Hap fletushkën</button></div>
      </div></div></div>`;
      document.querySelector('[data-listing-view]')?.setAttribute('hidden', '');
      packagePage.removeAttribute('hidden');
      document.querySelector('[data-package-page-title]')?.replaceChildren(document.createTextNode(item.title));
      document.title = `${item.title} | UMRA Travel`;
    }
  }

  const bookingForm = document.querySelector('[data-booking-form]');
  if (bookingForm) {
    const select = bookingForm.querySelector('[name="package"]');
    const bookablePackages = packages.filter(p => p.slug !== 'umre-e-personalizuar');
    const query = new URLSearchParams(window.location.search).get('paketa');
    select.innerHTML = bookablePackages.map(p => `<option value="${esc(p.slug)}">${esc(p.title)} · ${esc(p.date)}</option>`).join('');
    if (query && bookablePackages.some(p => p.slug === query)) select.value = query;
    const summary = document.querySelector('[data-booking-summary]');
    const updateSummary = () => {
      const p = packages.find(item => item.slug === select.value);
      if (!p || !summary) return;
      summary.innerHTML = `<img src="${esc(p.image)}" alt="" loading="lazy"><p class="eyebrow" style="margin:15px 0 0">${esc(p.tag)}</p><h2>${esc(p.title)}</h2><div class="date">${esc(p.date)}</div><p>${esc(p.summary)}</p><p>Çmimi: <strong>${esc(p.price)}</strong>${p.price.toLowerCase().includes('kontaktoni') ? '' : ' / person'}</p>${prices(p)}`;
    };
    select.addEventListener('change', updateSummary);
    updateSummary();
  }

  const whatsappForm = document.querySelector('[data-whatsapp-form]');
  if (whatsappForm) {
    whatsappForm.addEventListener('submit', event => {
      event.preventDefault();
      if (!whatsappForm.reportValidity()) return;
      const data = new FormData(whatsappForm);
      let message;
      if (whatsappForm.hasAttribute('data-custom-umrah-form')) {
        message = `Përshëndetje UMRA Travel!\nDëshiroj një ofertë për Umre të personalizuar.\n\nEmri: ${data.get('name')}\nTelefoni: ${data.get('phone')}\nEmail: ${data.get('email') || 'Nuk u dha'}\nAeroporti i nisjes: ${data.get('airport') || 'Sipas preferencës'}\nUdhëtarë: ${data.get('persons') || 'Nuk u specifikua'}\nData e nisjes: ${data.get('travel-date') || 'Fleksibël'}\nQëndrimi në Mekë / Medinë: ${data.get('stay-days') || 'Sipas ofertës'}\nHoteli: ${data.get('hotel') || 'Sipas preferencës'}\nKërkesa të tjera: ${data.get('message') || 'Nuk ka'}`;
      } else if (whatsappForm.hasAttribute('data-booking-form')) {
        const item = packages.find(p => p.slug === data.get('package'));
        message = `Përshëndetje UMRA Travel!\nDëshiroj informacion për rezervim.\n\nPaketa: ${item?.title || ''}\nData e paketës: ${item?.date || ''}\nEmri: ${data.get('name')}\nTelefoni: ${data.get('phone')}\nEmail: ${data.get('email') || 'Nuk u dha'}\nUdhëtarë: ${data.get('persons')}\nAeroporti i nisjes: ${data.get('airport') || 'Sipas paketës'}\nDatat e preferuara: ${data.get('travel-dates') || 'Sipas paketës'}\nKohëzgjatja: ${data.get('duration') || 'Sipas paketës'}\nHoteli / dhoma: ${data.get('hotel-room') || 'Pa preferencë'}\nKërkesë shtesë: ${data.get('message') || 'Nuk ka'}`;
      } else {
        message = `Përshëndetje UMRA Travel!\nKam një pyetje nga faqja web.\n\nEmri: ${data.get('name')}\nTelefoni: ${data.get('phone')}\nEmail: ${data.get('email') || 'Nuk u dha'}\nTema: ${data.get('topic') || 'Pyetje e përgjithshme'}\n\nMesazhi: ${data.get('message')}`;
      }
      const link = document.querySelector('[data-whatsapp-result]');
      link.href = `https://wa.me/355683195901?text=${encodeURIComponent(message)}`;
      link.hidden = false;
      link.focus();
      link.scrollIntoView({behavior: 'smooth', block: 'nearest'});
    });
  }
})();
