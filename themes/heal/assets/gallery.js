// Gallery lightbox: opens the photo in a <dialog> with a close (×) button.
// Without JS the link still opens the plain image.
// Photos are WordPress Gallery blocks (Media Library); each links to the full file.
const links = document.querySelectorAll('.wp-block-gallery .wp-block-image > a');

if (links.length && typeof HTMLDialogElement === 'function') {
  const dialog = document.createElement('dialog');
  dialog.className = 'lightbox';
  dialog.setAttribute('aria-label', 'Photo');
  dialog.innerHTML =
    '<button class="lightbox__close" type="button" aria-label="Close">×</button>' +
    '<figure class="lightbox__figure"><img class="lightbox__img" alt=""><figcaption class="lightbox__caption"></figcaption></figure>';
  document.body.append(dialog);

  const img = dialog.querySelector('.lightbox__img');
  const caption = dialog.querySelector('.lightbox__caption');
  const close = () => dialog.close();

  dialog.querySelector('.lightbox__close').addEventListener('click', close);
  // Click on the dark backdrop (outside the photo) closes too. Esc works natively.
  dialog.addEventListener('click', (e) => {
    if (e.target === dialog) close();
  });
  dialog.addEventListener('close', () => {
    img.removeAttribute('src');
    document.body.classList.remove('has-lightbox');
  });

  links.forEach((link) => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const thumb = link.querySelector('img');
      const fig = link.closest('figure')?.querySelector('figcaption');
      img.src = link.href;
      img.alt = thumb ? thumb.alt : '';
      caption.innerHTML = fig ? fig.innerHTML : '';
      caption.hidden = !fig;
      document.body.classList.add('has-lightbox');
      dialog.showModal();
    });
  });
}
