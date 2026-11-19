(() => {
  const toggle = document.getElementById('menuToggle');
  const nav = document.getElementById('mainNav');
  if (toggle && nav) {
    const closeMenu = () => {
      toggle.setAttribute('aria-expanded', 'false');
      nav.classList.remove('is-open');
      document.body.classList.remove('menu-open');
    };
    toggle.addEventListener('click', () => {
      const open = toggle.getAttribute('aria-expanded') === 'true';
      toggle.setAttribute('aria-expanded', String(!open));
      nav.classList.toggle('is-open', !open);
      document.body.classList.toggle('menu-open', !open);
    });
    nav.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMenu));
    document.addEventListener('click', event => {
      if (toggle.getAttribute('aria-expanded') === 'true' && !nav.contains(event.target) && !toggle.contains(event.target)) closeMenu();
    });
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
        closeMenu();
        toggle.focus();
      }
    });
  }
  const header = document.getElementById('siteHeader');
  const onScroll = () => header && header.classList.toggle('has-shadow', window.scrollY > 14);
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });

  const revealBlocks = document.querySelectorAll('main > section:not(.hero) .wrap');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  revealBlocks.forEach(block => block.setAttribute('data-reveal', ''));
  if (reduceMotion || !('IntersectionObserver' in window)) {
    revealBlocks.forEach(block => block.classList.add('is-visible'));
  } else {
    const revealObserver = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        revealObserver.unobserve(entry.target);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -28px 0px' });
    revealBlocks.forEach(block => revealObserver.observe(block));
  }
})();
