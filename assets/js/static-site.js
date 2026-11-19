(() => {
  const packages = window.UMRA_PACKAGES || [];
  const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
  const prices = item => (item.priceRows || []).length
    ? `<div class="package-price-rows" aria-label="Çmimet sipas dhomës">${item.priceRows.map(row => `<div><span>${esc(row.label)}</span><strong>${esc(row.price)}</strong></div>`).join('')}</div>`
    : '';
  const card = item => {
    const quote = item.price.toLowerCase().includes('kontaktoni');
    return `<article class="package-card">
      <a class="package-image" href="paketa.html?slug=${encodeURIComponent(item.slug)}" aria-label="Detajet: ${esc(item.title)}">
        <img src="${esc(item.image)}" alt="${esc(item.title)}" loading="lazy"><span class="package-tag">${esc(item.tag)}</span>
      </a>
      <div class="package-card-content"><p class="eyebrow small-eyebrow">${esc(item.date)}</p><h3><a href="paketa.html?slug=${encodeURIComponent(item.slug)}">${esc(item.title)}</a></h3>
        <p class="package-summary">${esc(item.summary)}</p><div class="price-lockup">${quote ? '' : '<span>Nga</span>'}<strong>${esc(item.price)}</strong>${quote ? '' : '<span>/ person</span>'}</div>
        ${prices(item)}
        <div class="package-cta-row"><a class="button button-dark button-small" href="rezervo.html?paketa=${encodeURIComponent(item.slug)}">Rezervo <span aria-hidden="true">↗</span></a><a class="button button-light button-small" href="paketa.html?slug=${encodeURIComponent(item.slug)}">Detaje</a></div>
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

  const packagePage = document.querySelector('[data-package-detail]');
  if (packagePage) {
    const slug = new URLSearchParams(window.location.search).get('slug');
    const item = packages.find(p => p.slug === slug);
    if (item) {
      const quote = item.price.toLowerCase().includes('kontaktoni');
      packagePage.innerHTML = `<div class="wrap"><div class="detail-top"><div class="detail-image"><img src="${esc(item.image)}" alt="${esc(item.title)}"></div><div class="detail-copy">
        <p class="eyebrow section-kicker">${esc(item.tag)}</p><h2>${esc(item.title)}</h2><p>${esc(item.description)}</p>
        <div class="detail-price"><small>${quote ? 'Çmimi sipas kërkesës' : 'Çmimi fillestar, për person'}</small><strong>${esc(item.price)}</strong></div>
        ${prices(item)}
        <ul class="feature-list">${item.features.map(feature => `<li>${esc(feature)}</li>`).join('')}</ul>
        <div class="detail-actions"><a class="button button-dark" href="rezervo.html?paketa=${encodeURIComponent(item.slug)}">Kërko rezervim <span>↗</span></a><a class="button button-light" href="https://wa.me/355683195901" target="_blank" rel="noopener">Pyet në WhatsApp</a></div>
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
    const query = new URLSearchParams(window.location.search).get('paketa');
    select.innerHTML = packages.map(p => `<option value="${esc(p.slug)}">${esc(p.title)} · ${esc(p.date)}</option>`).join('');
    if (query && packages.some(p => p.slug === query)) select.value = query;
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
      if (whatsappForm.hasAttribute('data-booking-form')) {
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
