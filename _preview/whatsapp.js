const PHONE = '+971522230941';
const DISPLAY_PHONE = '+971 52 223 0941';
const WA_PHONE = PHONE.replace(/\D/g, '');
function whatsappUrl(lang) {
  const message = lang === 'en' ? 'Hello, I am contacting you via the Maison Calista website. I would like more information.' : 'Bonjour, je vous contacte via le site de Maison Calista. Je souhaite obtenir des informations.';
  return `https://wa.me/${WA_PHONE}?text=${encodeURIComponent(message)}`;
}
function renderWhatsApp(lang) {
  const en = lang === 'en';
  const labels = en ? {
    title:'Contact Maison Calista', intro:'Tell us what you are looking for. Your answers will be added to your WhatsApp message.',
    close:'Close', name:'Your name', topic:'Your enquiry', choose:'Select a subject', period:'Preferred period (optional)', periodHint:'For example: March 2027', message:'Your question (optional)', submit:'Continue to WhatsApp', note:'You can review your message in WhatsApp before sending it.',
    topics:['General information','Discovery stay','Long-term stay','Family visit','Waiting list']
  } : {
    title:'Contacter Maison Calista', intro:'Précisez votre demande. Vos réponses seront ajoutées à votre message WhatsApp.',
    close:'Fermer', name:'Votre nom', topic:'Votre demande', choose:'Choisissez un sujet', period:'Période souhaitée (facultatif)', periodHint:'Par exemple : mars 2027', message:'Votre question (facultatif)', submit:'Continuer sur WhatsApp', note:'Vous pourrez relire votre message dans WhatsApp avant de l’envoyer.',
    topics:['Informations générales','Séjour Découverte','Séjour longue durée','Visite familiale','Liste d’attente']
  };
  return `<a class="mc-wa-launch" href="${whatsappUrl(lang)}" target="_blank" rel="noopener noreferrer" aria-haspopup="dialog" aria-controls="mc-wa-dialog" aria-label="${en ? 'Contact us on WhatsApp' : 'Nous contacter sur WhatsApp'}"><svg viewBox="0 0 32 32" width="28" height="28" aria-hidden="true" fill="currentColor"><path d="M16 .8A15.1 15.1 0 0 0 2.9 23.4L.8 31.2l8-2.1A15.2 15.2 0 1 0 16 .8Zm0 27.8a12.6 12.6 0 0 1-6.4-1.8l-.5-.3-4.7 1.2 1.3-4.5-.3-.5A12.7 12.7 0 1 1 16 28.6Zm7-9.5c-.4-.2-2.3-1.1-2.6-1.3-.4-.1-.6-.2-.9.2l-1.2 1.4c-.2.3-.4.3-.8.1-1.1-.5-2-1.1-2.9-1.8-.8-.8-1.4-1.6-2-2.5-.2-.4 0-.6.2-.8l.6-.7.4-.6c.1-.3 0-.5 0-.7l-1.2-2.8c-.3-.7-.6-.6-.9-.6h-.8c-.3 0-.7.1-1 .5-.9.9-1.4 2-1.4 3.2 0 1.9 1.4 3.7 1.6 3.9.2.3 2.8 4.4 6.8 6.1.9.4 1.7.7 2.3.8 1 .3 1.9.2 2.6.2.8-.2 2.3-1 2.6-1.8.3-.9.3-1.6.2-1.8-.1-.2-.4-.3-.8-.5Z"/></svg><span>WhatsApp</span></a>
<dialog class="mc-wa-dialog" id="mc-wa-dialog" aria-labelledby="mc-wa-title" lang="${lang}">
  <button type="button" class="mc-wa-close" aria-label="${labels.close}">×</button>
  <h2 id="mc-wa-title">${labels.title}</h2><p>${labels.intro}</p>
  <form id="mc-wa-form" data-phone="${WA_PHONE}" data-language="${lang}">
    <label for="mc-wa-name">${labels.name}</label><input id="mc-wa-name" name="name" autocomplete="name" maxlength="100" required>
    <label for="mc-wa-topic">${labels.topic}</label><select id="mc-wa-topic" name="topic" required><option value="">${labels.choose}</option>${labels.topics.map(t=>`<option>${t}</option>`).join('')}</select>
    <label for="mc-wa-period">${labels.period}</label><input id="mc-wa-period" name="period" placeholder="${labels.periodHint}" maxlength="160">
    <label for="mc-wa-message">${labels.message}</label><textarea id="mc-wa-message" name="message" rows="3" maxlength="1500"></textarea>
    <button class="mc-wa-submit" type="submit">${labels.submit}</button><p class="mc-wa-note">${labels.note}</p>
  </form>
</dialog>`;
}
module.exports = { PHONE, DISPLAY_PHONE, whatsappUrl, renderWhatsApp };
