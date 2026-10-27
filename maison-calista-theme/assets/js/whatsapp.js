(() => {
  const dialog = document.getElementById('mc-wa-dialog');
  const launcher = document.querySelector('.mc-wa-launch');
  const form = document.getElementById('mc-wa-form');
  if (!dialog || !launcher || !form || typeof dialog.showModal !== 'function') return;
  launcher.addEventListener('click', (event) => { event.preventDefault(); dialog.showModal(); });
  dialog.querySelector('.mc-wa-close').addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', (event) => { if (event.target === dialog) { const r=dialog.getBoundingClientRect(); if(event.clientX<r.left || event.clientX>r.right || event.clientY<r.top || event.clientY>r.bottom) dialog.close(); } });
  form.addEventListener('submit', (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    const data = new FormData(form);
    const en = form.dataset.language === 'en';
    const lines = [en ? 'Hello, I am contacting you via the Maison Calista website.' : 'Bonjour, je vous contacte via le site de Maison Calista.', '', `${en ? 'Name' : 'Nom'} : ${data.get('name').trim()}`, `${en ? 'Enquiry' : 'Demande'} : ${data.get('topic')}`];
    if (data.get('period').trim()) lines.push(`${en ? 'Preferred period' : 'Période souhaitée'} : ${data.get('period').trim()}`);
    if (data.get('message').trim()) lines.push(`${en ? 'Question' : 'Question'} : ${data.get('message').trim()}`);
    const canonical = document.querySelector('link[rel="canonical"]');
    if (canonical) lines.push('', `${en ? 'Page viewed' : 'Page consultée'} : ${canonical.href}`);
    window.open(`https://wa.me/${form.dataset.phone}?text=${encodeURIComponent(lines.join('\n'))}`, '_blank', 'noopener,noreferrer');
    if (typeof window.gtag === 'function') window.gtag('event', 'whatsapp_click', { event_category: 'contact', page_path: location.pathname });
  });
})();
