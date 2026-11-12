// Mobile menu toggle.
const toggle = document.querySelector('.menu-toggle');
const nav = document.getElementById('site-nav');

if (toggle && nav) {
  toggle.addEventListener('click', () => {
    const open = toggle.getAttribute('aria-expanded') === 'true';
    toggle.setAttribute('aria-expanded', String(!open));
    nav.classList.toggle('is-open', !open);
  });
}

// Sticky header (phones only, see style.css). On larger screens it is a normal header that scrolls
// away with the page. On phones it slides away while you scroll
// down and comes back as soon as you scroll up (never while the menu is open or near the top).
const header = document.querySelector('.site-header');

if (header) {
  const phone = window.matchMedia('(max-width: 760px)');
  const root = document.documentElement;
  let lastY = window.scrollY;
  let ticking = false;

  const show = () => header.classList.remove('is-hidden');
  const setHeight = () => root.style.setProperty('--header-h', `${header.offsetHeight}px`);

  const update = () => {
    ticking = false;
    const y = window.scrollY;
    const menuOpen = nav && nav.classList.contains('is-open');
    if (!phone.matches || menuOpen || y <= header.offsetHeight) {
      show();
      lastY = y;
      return;
    }
    if (Math.abs(y - lastY) < 8) return; // ignore tiny movements
    header.classList.toggle('is-hidden', y > lastY);
    lastY = y;
  };

  window.addEventListener(
    'scroll',
    () => {
      if (!ticking) {
        ticking = true;
        requestAnimationFrame(update);
      }
    },
    { passive: true },
  );
  header.addEventListener('focusin', show); // keyboard users always see what has focus
  phone.addEventListener('change', () => {
    show();
    setHeight();
  });
  window.addEventListener('resize', setHeight);
  setHeight();
}
