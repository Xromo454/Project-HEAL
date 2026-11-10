// Photo lightbox for pictures in page content: Gallery blocks, single Image blocks and classic galleries.
// It works however the photo was added in the editor: linked to its file, linked to a page,
// not linked at all. Links to other websites are left alone. Esc and the × button close it.
const IMAGE_URL = /\.(avif|gif|jpe?g|png|svg|webp)(\?.*)?$/i;

/** Biggest version of the photo: the link if it points to an image file, else the largest srcset entry. */
function fullUrl(img, link) {
  if (link && IMAGE_URL.test(link.href)) return link.href;
  const best = (img.getAttribute('srcset') || '')
    .split(',')
    .map((s) => s.trim().split(/\s+/))
    .filter((p) => p[0])
    .sort((a, b) => parseInt(b[1] || '0', 10) - parseInt(a[1] || '0', 10))[0];
  return best ? best[0] : img.currentSrc || img.src;
}

const root = document.querySelector('main') || document.body;
const figures = [...root.querySelectorAll('figure.wp-block-image, .gallery-item')];

if (figures.length && typeof HTMLDialogElement === 'function') {
  const dialog = document.createElement('dialog');
  dialog.className = 'lightbox';
  dialog.setAttribute('aria-label', 'Photo');
  dialog.innerHTML =
    '<button class="lightbox__close" type="button" aria-label="Close">×</button>' +
    '<figure class="lightbox__figure"><img class="lightbox__img" alt=""><figcaption class="lightbox__caption"></figcaption></figure>';
  document.body.append(dialog);

  const big = dialog.querySelector('.lightbox__img');
  const caption = dialog.querySelector('.lightbox__caption');
  const close = () => dialog.close();

  dialog.querySelector('.lightbox__close').addEventListener('click', close);
  // Click on the dark backdrop (outside the photo) closes too, but not the second click of a
  // double-click on the thumbnail, and a double-click never selects (tints) the photo.
  let openedAt = 0;
  dialog.addEventListener('mousedown', (e) => {
    if (e.detail > 1) e.preventDefault();
  });
  dialog.addEventListener('click', (e) => {
    if (e.target === dialog && performance.now() - openedAt > 500) close();
  });
  dialog.addEventListener('close', () => {
    if (dialog.open) return; // reopened right away
    big.removeAttribute('src');
    document.body.classList.remove('has-lightbox');
  });

  const open = (fig, img, link) => {
    big.src = fullUrl(img, link);
    big.alt = img.alt || '';
    const cap = fig.querySelector('figcaption, .gallery-caption');
    caption.innerHTML = cap ? cap.innerHTML : '';
    caption.hidden = !cap || !cap.textContent.trim();
    document.body.classList.add('has-lightbox');
    openedAt = performance.now();
    getSelection()?.removeAllRanges();
    dialog.showModal();
  };

  figures.forEach((fig) => {
    const img = fig.querySelector('img');
    if (!img) return;
    const link = img.closest('a');

    // A link to another website stays a normal link.
    if (link && !IMAGE_URL.test(link.href) && new URL(link.href, location.href).origin !== location.origin) return;

    img.classList.add('is-zoomable');
    if (link) {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        open(fig, img, link);
      });
    } else {
      // Not linked: make the photo itself clickable and reachable with the keyboard.
      img.tabIndex = 0;
      img.setAttribute('role', 'button');
      img.addEventListener('click', () => open(fig, img, null));
      img.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          open(fig, img, null);
        }
      });
    }
  });
}
