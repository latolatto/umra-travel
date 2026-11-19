(() => {
  const packages = window.UMRA_PACKAGES || [];
  const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
  const heroAudio = document.querySelector('[data-site-audio]');
  if (heroAudio) {
    let heroVisible = true;
    const playWhenVisible = () => {
      if (!heroVisible || !heroAudio.paused) return;
      heroAudio.play().catch(() => {});
    };
    const unlockAudioOnInteraction = () => {
      playWhenVisible();
      document.removeEventListener('pointerdown', unlockAudioOnInteraction);
      document.removeEventListener('keydown', unlockAudioOnInteraction);
    };
    heroAudio.volume = 0.58;
    document.addEventListener('pointerdown', unlockAudioOnInteraction, {once: true, passive: true});
    document.addEventListener('keydown', unlockAudioOnInteraction, {once: true});
    const heroSection = heroAudio.closest('.hero');
    if ('IntersectionObserver' in window && heroSection) {
      const audioVisibility = new IntersectionObserver(entries => {
        heroVisible = entries[0].isIntersecting && entries[0].intersectionRatio >= 0.08;
        if (heroVisible) playWhenVisible();
        else heroAudio.pause();
      }, {threshold: [0, 0.08, 0.25]});
      audioVisibility.observe(heroSection);
    }
    playWhenVisible();
  }
  const icon = name => {
    const paths = {
      plane: '<path d="m3 12 18-5-6 6 2 5-4-3-3 4 1-6-8-1Z"/>',
      calendar: '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4M17 3v4M3 10h18"/>',
      pin: '<path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>'
    };
    return `<svg class="line-icon" viewBox="0 0 24 24" aria-hidden="true">${paths[name] || ''}</svg>`;
  };
  const prices = item => (item.priceRows || []).length
    ? `<div class="package-price-rows" aria-label="Çmimet sipas dhomës">${item.priceRows.map(row => `<div><span>${esc(row.label)}</span><strong>${esc(row.price)}</strong></div>`).join('')}</div>`
    : '';
  const card = (item, compact = false) => {
    const quote = item.price.toLowerCase().includes('kontaktoni');
    const bookingHref = item.slug === 'umre-e-personalizuar' ? 'personalizuar.html' : `rezervo.html?paketa=${encodeURIComponent(item.slug)}`;
    const bookingLabel = item.slug === 'umre-e-personalizuar' ? 'Kërko ofertë' : 'Rezervo';
    const route = (item.route || []).length
      ? `<div class="package-route" aria-label="Itinerari ${esc(item.route.join(' – '))}">${item.route.map((stop, index) => `${index ? `<span class="route-leg" aria-hidden="true">${icon('plane')}</span>` : ''}<b>${esc(stop)}</b>`).join('')}</div>`
      : `<div class="package-route package-route-custom"><b>UDHËTIM SIPAS KËRKESËS</b></div>`;
    const flyers = esc(JSON.stringify(item.flyers || []));
    const features = item.features || [];
    const visibleFeatures = compact ? features.slice(0, 2) : features;
    const inclusionList = features.length
      ? `<ul class="package-inclusions${compact ? ' is-compact' : ''}" aria-label="Çfarë përfshihet">${visibleFeatures.map(feature => `<li>${esc(feature)}</li>`).join('')}${compact && features.length > visibleFeatures.length ? `<li class="inclusions-more">+${features.length - visibleFeatures.length} përfshirje të tjera</li>` : ''}</ul>`
      : '';
    return `<article class="package-card">
      <div class="package-cover"><img src="${esc(item.image)}" alt="${esc(item.imageAlt || `Pamje për ${item.title}`)}" loading="lazy"><span class="package-tag">${esc(item.tag)}</span><a class="package-cover-title" href="paketa.html?slug=${encodeURIComponent(item.slug)}"><span>${esc(item.title)}</span></a></div>
      <div class="package-card-content">
        ${route}
        <div class="package-meta"><span>${icon('calendar')}${esc(item.date)}</span><span>${icon('pin')}${esc(item.stay || 'Mekë · Medinë')}</span></div>
        <div class="price-lockup">${quote ? '' : '<span>Nga</span>'}<strong>${esc(item.price)}</strong>${quote ? '' : '<span>/ person</span>'}</div>
        ${prices(item)}
        ${inclusionList}
        <p class="package-highlight"><span class="package-highlight-mark" aria-hidden="true">•</span>${esc(item.highlight || item.summary)}</p>
        <div class="package-cta-row"><a class="button button-dark button-small" href="${bookingHref}">${bookingLabel}</a><button class="button button-light button-small flyer-open" type="button" data-flyers="${flyers}" aria-label="Shiko fotot dhe fletushkën: ${esc(item.title)}">Foto / fletushkë</button></div>
      </div></article>`;
  };

  document.querySelectorAll('[data-home-packages]').forEach(target => {
    const selected = packages.filter(p => !p.tag.toLowerCase().includes('ramazan'));
    target.innerHTML = selected.slice(0, 3).map(item => card(item, true)).join('');
  });
  document.querySelectorAll('[data-ramadan-packages]').forEach(target => {
    target.innerHTML = packages.filter(p => p.tag.toLowerCase().includes('ramazan')).map(item => card(item, true)).join('');
  });
  document.querySelectorAll('[data-package-list]').forEach(target => {
    target.innerHTML = packages.map(card).join('');
  });

  const flyerModal = document.createElement('div');
  flyerModal.className = 'flyer-modal';
  flyerModal.hidden = true;
  flyerModal.setAttribute('role', 'dialog');
  flyerModal.setAttribute('aria-modal', 'true');
  flyerModal.setAttribute('aria-label', 'Fotot dhe fletushka e paketës');
  flyerModal.setAttribute('aria-hidden', 'true');
  flyerModal.innerHTML = `<div class="flyer-backdrop" data-flyer-close></div><div class="flyer-dialog" role="group" aria-roledescription="carousel"><button class="flyer-close" type="button" data-flyer-close aria-label="Mbyll fotot">×</button><button class="flyer-arrow flyer-prev" type="button" data-flyer-step="-1" aria-label="Fotoja e mëparshme"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg></button><img class="flyer-image" src="" alt="Foto ose fletushkë e paketës" draggable="false"><button class="flyer-arrow flyer-next" type="button" data-flyer-step="1" aria-label="Fotoja tjetër"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg></button><div class="flyer-count" aria-live="polite"></div><div class="flyer-pagination" aria-label="Zgjidh foton"></div></div>`;
  document.body.append(flyerModal);
  const flyerImage = flyerModal.querySelector('.flyer-image');
  const flyerCount = flyerModal.querySelector('.flyer-count');
  const flyerDialog = flyerModal.querySelector('.flyer-dialog');
  const flyerCloseButtons = flyerModal.querySelectorAll('[data-flyer-close]');
  const flyerPagination = flyerModal.querySelector('.flyer-pagination');
  let activeFlyers = [];
  let flyerIndex = 0;
  let lastFlyerTrigger = null;
  let touchStartX = null;
  const renderFlyer = () => {
    flyerImage.src = activeFlyers[flyerIndex];
    flyerImage.alt = `Foto ose fletushkë ${flyerIndex + 1} nga ${activeFlyers.length}`;
    flyerCount.textContent = activeFlyers.length > 1 ? `${flyerIndex + 1} / ${activeFlyers.length}` : '';
    flyerModal.querySelector('.flyer-prev').hidden = activeFlyers.length < 2;
    flyerModal.querySelector('.flyer-next').hidden = activeFlyers.length < 2;
    flyerPagination.hidden = activeFlyers.length < 2;
    flyerPagination.innerHTML = activeFlyers.map((_, index) => `<button class="flyer-dot${index === flyerIndex ? ' is-active' : ''}" type="button" data-flyer-index="${index}" aria-label="Shiko foton ${index + 1}"${index === flyerIndex ? ' aria-current="true"' : ''}><span></span></button>`).join('');
  };
  const closeFlyer = () => {
    if (flyerModal.hidden) return;
    flyerModal.hidden = true;
    flyerModal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('flyer-open');
    lastFlyerTrigger?.focus();
  };
  const stepFlyer = direction => {
    if (activeFlyers.length < 2) return;
    flyerIndex = (flyerIndex + direction + activeFlyers.length) % activeFlyers.length;
    renderFlyer();
  };
  document.addEventListener('click', event => {
    const target = event.target instanceof Element ? event.target : null;
    const trigger = target?.closest('.flyer-open');
    if (trigger) {
      try { activeFlyers = JSON.parse(trigger.dataset.flyers || '[]'); } catch { activeFlyers = []; }
      if (!activeFlyers.length) return;
      lastFlyerTrigger = trigger;
      flyerIndex = 0;
      renderFlyer();
      flyerModal.hidden = false;
      flyerModal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('flyer-open');
      flyerModal.querySelector('.flyer-close').focus();
    }
  });
  flyerCloseButtons.forEach(control => control.addEventListener('click', event => {
    event.preventDefault();
    closeFlyer();
  }));
  flyerModal.addEventListener('click', event => {
    const control = event.target instanceof Element ? event.target.closest('[data-flyer-step], [data-flyer-index]') : null;
    if (control) {
      event.preventDefault();
      event.stopPropagation();
      if (control.hasAttribute('data-flyer-step')) stepFlyer(Number(control.dataset.flyerStep));
      else {
        flyerIndex = Number(control.dataset.flyerIndex);
        renderFlyer();
      }
      return;
    }
    if (event.target === flyerModal) closeFlyer();
  });
  document.addEventListener('keydown', event => {
    if (flyerModal.hidden) return;
    if (event.key === 'Escape') closeFlyer();
    if (event.key === 'ArrowLeft') stepFlyer(-1);
    if (event.key === 'ArrowRight') stepFlyer(1);
    if (event.key === 'Tab') {
      const focusable = [...flyerDialog.querySelectorAll('button:not([hidden])')];
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    }
  });
  flyerDialog.addEventListener('pointerdown', event => {
    if (event.pointerType === 'mouse' || (event.target instanceof Element && event.target.closest('button'))) return;
    touchStartX = event.clientX;
  });
  flyerDialog.addEventListener('pointerup', event => {
    if (touchStartX === null || activeFlyers.length < 2) return;
    const delta = event.clientX - touchStartX;
    touchStartX = null;
    if (Math.abs(delta) < 36) return;
    flyerIndex = (flyerIndex + (delta < 0 ? 1 : -1) + activeFlyers.length) % activeFlyers.length;
    renderFlyer();
  });
  flyerDialog.addEventListener('pointercancel', () => { touchStartX = null; });

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
        <div class="detail-actions"><a class="button button-dark" href="${bookingHref}">${bookingLabel} <span><svg class="arrow-icon" viewBox="0 0 16 16" aria-hidden="true"><path d="M4 12 12 4M5 4h7v7"/></svg></span></a><button class="button button-light flyer-open" type="button" data-flyers="${esc(JSON.stringify(item.flyers || []))}" aria-label="Shiko fotot dhe fletushkën: ${esc(item.title)}">Foto / fletushkë</button></div>
      </div></div></div>`;
      document.querySelector('[data-listing-view]')?.setAttribute('hidden', '');
      packagePage.removeAttribute('hidden');
      document.querySelector('[data-package-page-title]')?.replaceChildren(document.createTextNode(item.title));
      const detailTitle = `${item.title} · Paketë Umre | UMRA Travel`;
      const detailDescription = `${item.title}, ${item.date}. ${item.description}`.slice(0, 155);
      document.title = detailTitle;
      document.querySelector('meta[name="description"]')?.setAttribute('content', detailDescription);
      document.querySelector('meta[property="og:title"]')?.setAttribute('content', detailTitle);
      document.querySelector('meta[property="og:description"]')?.setAttribute('content', detailDescription);
      document.querySelector('meta[name="twitter:title"]')?.setAttribute('content', detailTitle);
      document.querySelector('meta[name="twitter:description"]')?.setAttribute('content', detailDescription);
    }
  }

  const bookingForm = document.querySelector('[data-booking-form]');
  if (bookingForm) {
    const select = bookingForm.querySelector('[name="package"]');
    const roomSelect = bookingForm.querySelector('[name="room-occupancy"]');
    const roomPriceNote = bookingForm.querySelector('[data-room-price-note]');
    const personsSelect = bookingForm.querySelector('[name="persons"]');
    const bookablePackages = packages.filter(p => p.slug !== 'umre-e-personalizuar');
    const query = new URLSearchParams(window.location.search).get('paketa');
    select.innerHTML = bookablePackages.map(p => `<option value="${esc(p.slug)}">${esc(p.title)} · ${esc(p.date)}</option>`).join('');
    if (query && bookablePackages.some(p => p.slug === query)) select.value = query;
    const summary = document.querySelector('[data-booking-summary]');
    const roomNumber = row => Number((row?.label.match(/(\d+)/) || [])[1]);
    const priceForRoom = (item, occupancy) => (item?.priceRows || []).find(row => roomNumber(row) === Number(occupancy));
    const updateRoomOptions = item => {
      if (!roomSelect) return;
      const currentValue = roomSelect.value;
      const rows = [...(item?.priceRows || [])].sort((a, b) => roomNumber(a) - roomNumber(b));
      roomSelect.innerHTML = rows.map(row => `<option value="${roomNumber(row)}">${roomNumber(row)} persona në dhomë · ${esc(row.price.replace(/\s*\/\s*person/i, ''))} / person</option>`).join('');
      const defaultRoom = rows.find(row => roomNumber(row) === 4) || rows[0] || {label: '4'};
      roomSelect.value = rows.some(row => String(roomNumber(row)) === currentValue) ? currentValue : String(roomNumber(defaultRoom));
    };
    const updateSummary = () => {
      const p = packages.find(item => item.slug === select.value);
      if (!p || !summary) return;
      updateRoomOptions(p);
      const selectedRoom = priceForRoom(p, roomSelect?.value);
      if (roomPriceNote) roomPriceNote.textContent = selectedRoom ? `${selectedRoom.label} · ${selectedRoom.price}` : '';
      summary.innerHTML = `<img src="${esc(p.image)}" alt="" loading="lazy"><p class="eyebrow" style="margin:15px 0 0">${esc(p.tag)}</p><h2>${esc(p.title)}</h2><div class="date">${esc(p.date)}</div><p>${esc(p.description || p.summary)}</p><p>Çmimi: <strong>${esc(p.price)}</strong>${p.price.toLowerCase().includes('kontaktoni') ? '' : ' / person'}</p>${prices(p)}<h3 class="booking-inclusions-title">Përfshihet në paketë</h3><ul class="booking-inclusions">${(p.features || []).map(feature => `<li>${esc(feature)}</li>`).join('')}</ul>`;
    };
    select.addEventListener('change', updateSummary);
    roomSelect?.addEventListener('change', updateSummary);
    personsSelect?.addEventListener('change', updateSummary);
    updateSummary();
  }

  const whatsappForm = document.querySelector('[data-whatsapp-form]');
  if (whatsappForm) {
    const resultLink = whatsappForm.querySelector('[data-whatsapp-result]');
    const localDate = new Date();
    const today = `${localDate.getFullYear()}-${String(localDate.getMonth() + 1).padStart(2, '0')}-${String(localDate.getDate()).padStart(2, '0')}`;
    whatsappForm.querySelectorAll('input[type="date"]').forEach(input => { input.min = today; });
    whatsappForm.addEventListener('input', () => { if (resultLink) resultLink.hidden = true; });
    whatsappForm.addEventListener('change', () => { if (resultLink) resultLink.hidden = true; });
    whatsappForm.addEventListener('submit', event => {
      event.preventDefault();
      if (!whatsappForm.reportValidity()) return;
      const data = new FormData(whatsappForm);
      let message;
      if (whatsappForm.hasAttribute('data-custom-umrah-form')) {
        message = `Përshëndetje UMRA Travel!\nDëshiroj një ofertë për Umre të personalizuar.\n\nEmri: ${data.get('name')}\nTelefoni: ${data.get('phone')}\nEmail: ${data.get('email') || 'Nuk u dha'}\nAeroporti i nisjes: ${data.get('airport') || 'Sipas preferencës'}\nUdhëtarë: ${data.get('persons') || 'Nuk u specifikua'}\nData e nisjes: ${data.get('travel-date') || 'Fleksibël'}\nQëndrimi në Mekë / Medinë: ${data.get('stay-days') || 'Sipas ofertës'}\nHoteli: ${data.get('hotel') || 'Sipas preferencës'}\nKërkesa të tjera: ${data.get('message') || 'Nuk ka'}`;
      } else if (whatsappForm.hasAttribute('data-booking-form')) {
        const item = packages.find(p => p.slug === data.get('package'));
        const occupancy = Number(data.get('room-occupancy'));
        const roomRow = (item?.priceRows || []).find(row => Number((row.label.match(/(\d+)/) || [])[1]) === occupancy);
        message = `Përshëndetje UMRA Travel!\nDëshiroj informacion për rezervim.\n\nPaketa: ${item?.title || ''}\nData e paketës: ${item?.date || ''}\nEmri: ${data.get('name')}\nTelefoni: ${data.get('phone')}\nEmail: ${data.get('email') || 'Nuk u dha'}\nUdhëtarë gjithsej: ${data.get('persons')}\nDhoma: ${roomRow?.label || `${occupancy} persona në dhomë`}\nÇmimi: ${roomRow?.price || 'Ju lutem më dërgoni ofertën'}\nKërkesë shtesë: ${data.get('message') || 'Nuk ka'}`;
      } else {
        message = `Përshëndetje UMRA Travel!\nPo ju kontaktoj nga faqja web.\n\nEmri: ${data.get('name')}\nTelefoni: ${data.get('phone')}\nEmail: ${data.get('email') || 'Nuk u dha'}\nArsyeja: ${data.get('topic') || 'Pyetje e përgjithshme'}\n\nMesazhi: ${data.get('message')}`;
      }
      if (!resultLink) return;
      resultLink.href = `https://wa.me/355683195901?text=${encodeURIComponent(message)}`;
      resultLink.hidden = false;
      resultLink.focus();
      resultLink.scrollIntoView({behavior: 'smooth', block: 'nearest'});
    });
  }
})();
