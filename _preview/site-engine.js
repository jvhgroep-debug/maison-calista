/**
 * Shared Maison Calista static site engine (preview server + Cloudflare Pages build).
 */
const fs = require('fs');
const path = require('path');
const { SITE_URL, pathFor } = require('./sitemap');

const ROOT = path.join(__dirname, '..');
const THEME = path.join(ROOT, 'maison-calista-theme');
const CONTENT = path.join(THEME, 'inc', 'content');
const CONTACT_EMAIL = 'contact@associationcalista.com';
const OG_IMAGE = `${SITE_URL}/assets/images/photos/maison-calista-restaurant-terrace-pool-dusk.jpg`;

const PAGES = [
  { slug: '', file: 'home.html', title: { fr: 'Accueil', en: 'Home' }, nav: false },
  { slug: 'about-maison-calista', file: 'about.html', title: { fr: 'À propos', en: 'About' }, nav: true },
  { slug: 'the-residence', file: 'residence.html', title: { fr: 'La résidence', en: 'The Residence' }, nav: true },
  { slug: 'care-support', file: 'care.html', title: { fr: 'Accompagnement', en: 'Care & Support' }, nav: true },
  { slug: 'family', file: 'family.html', title: { fr: 'Famille', en: 'Family' }, nav: true },
  { slug: 'activities', file: 'activities.html', title: { fr: 'Activités', en: 'Activities' }, nav: true },
  { slug: 'restaurant', file: 'restaurant.html', title: { fr: 'Restaurant', en: 'Restaurant' }, nav: true },
  { slug: 'stays-pricing', file: 'pricing.html', title: { fr: 'Séjours & tarifs', en: 'Stays & Pricing' }, nav: true },
  { slug: 'gallery', file: 'gallery.html', title: { fr: 'Galerie', en: 'Gallery' }, nav: true },
  { slug: 'contact', file: 'contact.html', title: { fr: 'Contact', en: 'Contact' }, nav: true },
  { slug: 'privacy-policy', file: 'privacy.html', title: { fr: 'Confidentialité', en: 'Privacy Policy' }, nav: false },
  { slug: 'cookie-policy', file: 'cookies.html', title: { fr: 'Cookies', en: 'Cookie Policy' }, nav: false },
];

const PRICES = {
  discovery_double: '€1 899',
  discovery_single: '€2 599',
  discovery_surcharge: '€319',
  wellbeing: '€1 899',
  comfort: '€2 599',
  signature: '€3 799',
};

function stripWpComments(html) {
  return html.replace(/<!--\s*\/?wp:[\s\S]*?-->/g, '');
}

function readContent(file, lang) {
  const p = lang === 'en'
    ? path.join(CONTENT, 'en', file)
    : path.join(CONTENT, file);
  if (!fs.existsSync(p)) return `<p>Missing content: ${file}</p>`;
  return fs.readFileSync(p, 'utf8');
}

function expandShortcodes(html, lang, pagePath) {
  const home = lang === 'en' ? '/en/' : '/';
  const year = String(new Date().getFullYear());
  const loc = lang === 'en' ? 'Near Marrakech, Morocco' : 'Près de Marrakech, Maroc';
  const selfPath = pagePath || home;
  const otherPath = lang === 'en'
    ? selfPath.replace(/^\/en/, '') || '/'
    : '/en' + (selfPath === '/' ? '/' : selfPath);

  let out = html
    // Content files use %%THEME_URI%%/assets/... → /assets/...
    .replace(/%%THEME_URI%%\/assets/g, '/assets')
    .replace(/%%THEME_URI%%/g, '')
    .replace(/%%HOME_URL%%/g, home);

  out = out.replace(/\[mc_site_logo[^\]]*\]/g,
    `<img class="mc-site-logo" src="/assets/images/logo/maison-calista-logo.svg" alt="Maison Calista" width="160" height="48" loading="eager" decoding="async" />`);

  out = out.replace(/\[mc_language_switcher\]/g, `
<nav class="mc-lang" aria-label="Langue">
  <a class="mc-lang__link${lang === 'fr' ? ' is-active' : ''}" href="${lang === 'fr' ? selfPath : otherPath}" hreflang="fr" lang="fr">🇫🇷 <span>Français</span></a>
  <a class="mc-lang__link${lang === 'en' ? ' is-active' : ''}" href="${lang === 'en' ? selfPath : otherPath}" hreflang="en" lang="en">🇬🇧 <span>English</span></a>
</nav>`);

  out = out.replace(/\[mc_location\]/g, loc);
  out = out.replace(/\[mc_year\]/g, year);
  out = out.replace(/\[mc_last_updated\]/g,
    lang === 'en'
      ? `<p class="mc-last-updated">Last updated: ${new Date().toLocaleDateString('en-GB')}</p>`
      : `<p class="mc-last-updated">Dernière mise à jour : ${new Date().toLocaleDateString('fr-FR')}</p>`);

  out = out.replace(/\[mc_whatsapp[^\]]*\]/g,
    `<span class="mc-tbc" title="Customizer placeholder"><span class="mc-tbc__value">To be confirmed</span></span>`);

  out = out.replace(/\[mc_social_links\]/g, '');

  out = out.replace(/\[mc_maps\]/g, `
<div class="mc-maps mc-maps--disabled" role="img" aria-label="Map placeholder">
  <div class="mc-maps__placeholder">
    <p class="mc-maps__pin" aria-hidden="true"></p>
    <p class="mc-maps__title">${lang === 'en' ? 'Map placeholder — exact pin to be confirmed' : 'Carte désactivée — emplacement exact à confirmer'}</p>
    <p class="mc-maps__loc">${loc}</p>
    <p class="mc-tbc"><span class="mc-tbc__value">To be confirmed</span></p>
  </div>
</div>`);

  out = out.replace(/\[mc_legal_review_notice\]/g, `
<aside class="mc-legal-banner" role="status">
  <strong class="mc-legal-banner__mark">${lang === 'en' ? 'Legal notice' : 'Avis juridique'}</strong>
  <p class="mc-legal-banner__text">${lang === 'en'
    ? 'LEGAL REVIEW REQUIRED BEFORE GO-LIVE — This page is a professional template for general information only.'
    : 'À RÉVISER AVANT LA MISE EN LIGNE — Cette page est un modèle professionnel à titre informatif uniquement.'}</p>
</aside>`);

  out = out.replace(/\[mc_price\s+key="([^"]+)"\]/g, (_, key) => PRICES[key] || '€—');

  out = out.replace(/\[mc_contact_form\]/g, `
<form class="mc-form" onsubmit="event.preventDefault(); alert(${lang === 'en' ? "'Preview only — form submissions are disabled.'" : "'Aperçu uniquement — l’envoi de formulaires est désactivé.'"});">
  <div class="mc-form__row"><label>${lang === 'en' ? 'Full name' : 'Nom complet'}</label><input type="text" required /></div>
  <div class="mc-form__row"><label>Email</label><input type="email" required /></div>
  <div class="mc-form__row"><label>${lang === 'en' ? 'Message' : 'Message'}</label><textarea rows="5" required></textarea></div>
  <button type="submit" class="mc-btn">${lang === 'en' ? 'Send message' : 'Envoyer'}</button>
  <p class="mc-form__note">${lang === 'en' ? `Form — ${CONTACT_EMAIL}` : `Formulaire — ${CONTACT_EMAIL}`}</p>
</form>`);

  return out;
}

function buildNav(lang, currentSlug) {
  const home = lang === 'en' ? '/en/' : '/';
  const items = PAGES.filter((p) => p.nav).map((p) => {
    const href = home + (p.slug ? p.slug + '/' : '');
    const label = p.title[lang];
    const active = p.slug === currentSlug ? ' aria-current="page"' : '';
    return `<li class="wp-block-navigation-item"><a class="wp-block-navigation-item__content" href="${href}"${active}>${label}</a></li>`;
  }).join('');

  return `<ul class="wp-block-navigation__container">${items}</ul>`;
}

function buildHeader(lang, currentSlug, pagePath) {
  const home = lang === 'en' ? '/en/' : '/';
  const contact = home + 'contact/';
  const cta = lang === 'en' ? 'Waiting list' : "Liste d\u2019attente";
  const menuLabel = lang === 'en' ? 'Open menu' : 'Ouvrir le menu';
  const navLabel = lang === 'en' ? 'Primary navigation' : 'Navigation principale';

  const html = `
<header class="wp-block-group mc-header">
  <div class="mc-header__inner">
    <a class="mc-header__brand" href="${home}">
      [mc_site_logo]
      <span class="mc-header__brand-text screen-reader-text">Maison Calista</span>
    </a>
    <button class="mc-nav-toggle" type="button" aria-expanded="false" aria-controls="mc-primary-nav" aria-label="${menuLabel}">
      <span></span><span></span><span></span>
    </button>
    <nav class="mc-header__nav" id="mc-primary-nav" aria-label="${navLabel}">
      ${buildNav(lang, currentSlug)}
    </nav>
    <div class="mc-header__actions">
      [mc_language_switcher]
      <a class="mc-btn-cta" href="${contact}">${cta}</a>
    </div>
  </div>
</header>`;
  return expandShortcodes(html, lang, pagePath);
}

function buildFooter(lang, pagePath) {
  const home = lang === 'en' ? '/en/' : '/';
  const html = lang === 'en' ? `
<footer class="wp-block-group mc-footer">
  <div class="mc-footer__grid">
    <div>
      <div class="mc-footer__brand">Maison Calista</div>
      <p>An exclusive boutique residence near Marrakech — warmth, light, humanity and quality of life.</p>
      <p><a href="mailto:${CONTACT_EMAIL}">${CONTACT_EMAIL}</a><br />[mc_location]</p>
    </div>
    <div>
      <h3 class="mc-footer__heading">Explore</h3>
      <ul class="mc-footer__list">
        <li><a href="${home}about-maison-calista/">About</a></li>
        <li><a href="${home}the-residence/">The residence</a></li>
        <li><a href="${home}care-support/">Care &amp; support</a></li>
        <li><a href="${home}activities/">Activities</a></li>
        <li><a href="${home}restaurant/">Restaurant</a></li>
      </ul>
    </div>
    <div>
      <h3 class="mc-footer__heading">Stays</h3>
      <ul class="mc-footer__list">
        <li><a href="${home}stays-pricing/">Stays &amp; pricing</a></li>
        <li><a href="${home}family/">Family</a></li>
        <li><a href="${home}gallery/">Gallery</a></li>
        <li><a href="${home}contact/">Contact</a></li>
      </ul>
    </div>
    <div>
      <h3 class="mc-footer__heading">Legal &amp; language</h3>
      <ul class="mc-footer__list">
        <li><a href="${home}privacy-policy/">Privacy</a></li>
        <li><a href="${home}cookie-policy/">Cookies</a></li>
      </ul>
      [mc_language_switcher]
      [mc_social_links]
    </div>
  </div>
  <div class="mc-footer__meta">
    <p>© [mc_year] Maison Calista. All rights reserved.</p>
    [mc_last_updated]
  </div>
</footer>` : `
<footer class="wp-block-group mc-footer">
  <div class="mc-footer__grid">
    <div>
      <div class="mc-footer__brand">Maison Calista</div>
      <p>Une résidence boutique exclusive près de Marrakech — chaleur, lumière, humanité et qualité de vie.</p>
      <p><a href="mailto:${CONTACT_EMAIL}">${CONTACT_EMAIL}</a><br />[mc_location]</p>
    </div>
    <div>
      <h3 class="mc-footer__heading">Explorer</h3>
      <ul class="mc-footer__list">
        <li><a href="${home}about-maison-calista/">À propos</a></li>
        <li><a href="${home}the-residence/">La résidence</a></li>
        <li><a href="${home}care-support/">Accompagnement</a></li>
        <li><a href="${home}activities/">Activités</a></li>
        <li><a href="${home}restaurant/">Restaurant</a></li>
      </ul>
    </div>
    <div>
      <h3 class="mc-footer__heading">Séjours</h3>
      <ul class="mc-footer__list">
        <li><a href="${home}stays-pricing/">Séjours &amp; tarifs</a></li>
        <li><a href="${home}family/">Famille</a></li>
        <li><a href="${home}gallery/">Galerie</a></li>
        <li><a href="${home}contact/">Contact</a></li>
      </ul>
    </div>
    <div>
      <h3 class="mc-footer__heading">Légal &amp; langue</h3>
      <ul class="mc-footer__list">
        <li><a href="${home}privacy-policy/">Confidentialité</a></li>
        <li><a href="${home}cookie-policy/">Cookies</a></li>
      </ul>
      [mc_language_switcher]
      [mc_social_links]
    </div>
  </div>
  <div class="mc-footer__meta">
    <p>© [mc_year] Maison Calista. Tous droits réservés.</p>
    [mc_last_updated]
  </div>
</footer>`;
  return expandShortcodes(html, lang, pagePath);
}

function buildPage(page, lang, options = {}) {
  const production = Boolean(options.production);
  const pagePath = lang === 'en'
    ? (page.slug ? `/en/${page.slug}/` : '/en/')
    : (page.slug ? `/${page.slug}/` : '/');
  let body = stripWpComments(readContent(page.file, lang));
  body = expandShortcodes(body, lang, pagePath);

  const title = `${page.title[lang]} · Maison Calista`;
  const description = lang === 'en'
    ? 'Maison Calista — exclusive boutique residence near Marrakech, Morocco.'
    : 'Maison Calista — résidence boutique exclusive près de Marrakech.';
  const canonical = `${SITE_URL}${pagePath}`;
  const frUrl = `${SITE_URL}${pathFor('fr', page.slug || '')}`;
  const enUrl = `${SITE_URL}${pathFor('en', page.slug || '')}`;
  const locale = lang === 'en' ? 'en_US' : 'fr_FR';
  const jsonLd = JSON.stringify({
    '@context': 'https://schema.org',
    '@type': ['Organization', 'LodgingBusiness'],
    name: 'Maison Calista',
    url: SITE_URL + '/',
    email: CONTACT_EMAIL,
    address: {
      '@type': 'PostalAddress',
      addressLocality: 'Marrakech',
      addressCountry: 'MA',
    },
    logo: `${SITE_URL}/assets/images/logo/maison-calista-logo.svg`,
    image: OG_IMAGE,
  });
  const header = buildHeader(lang, page.slug, pagePath);
  const footer = buildFooter(lang, pagePath);
  const badge = production
    ? ''
    : `<div class="mc-preview-badge">Maison Calista preview · ${lang.toUpperCase()}</div>`;

  return `<!DOCTYPE html>
<html lang="${lang === 'en' ? 'en-US' : 'fr-FR'}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>${title}</title>
  <meta name="description" content="${description}" />
  <link rel="canonical" href="${canonical}" />
  <link rel="alternate" hreflang="fr" href="${frUrl}" />
  <link rel="alternate" hreflang="en" href="${enUrl}" />
  <link rel="alternate" hreflang="x-default" href="${frUrl}" />
  <meta property="og:type" content="website" />
  <meta property="og:locale" content="${locale}" />
  <meta property="og:site_name" content="Maison Calista" />
  <meta property="og:title" content="${title}" />
  <meta property="og:description" content="${description}" />
  <meta property="og:url" content="${canonical}" />
  <meta property="og:image" content="${OG_IMAGE}" />
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="${title}" />
  <meta name="twitter:description" content="${description}" />
  <meta name="twitter:url" content="${canonical}" />
  <meta name="twitter:image" content="${OG_IMAGE}" />
  <script type="application/ld+json">${jsonLd}</script>
  <link rel="preload" href="/assets/fonts/source-sans-3-latin.woff2" as="font" type="font/woff2" crossorigin />
  <link rel="preload" href="/assets/fonts/cormorant-garamond-latin.woff2" as="font" type="font/woff2" crossorigin />
  <link rel="stylesheet" href="/assets/css/main.css" />
  <link rel="icon" href="/assets/images/logo/maison-calista-logo.svg" type="image/svg+xml" />
  <style>
    :root {
      --wp--preset--color--cream: #F7F2EA;
      --wp--preset--color--cream-deep: #EFE6D8;
      --wp--preset--color--sand: #D9C8B0;
      --wp--preset--color--sand-soft: #E8DDD0;
      --wp--preset--color--gold: #A67C52;
      --wp--preset--color--gold-deep: #8B6540;
      --wp--preset--color--bronze: #6B4F3A;
      --wp--preset--color--charcoal: #2C2A28;
      --wp--preset--color--stone: #5C574F;
      --wp--preset--color--muted: #8A847A;
      --wp--preset--color--white: #FFFFFF;
      --wp--preset--color--ink: #1A1816;
      --wp--preset--font-family--heading: "Cormorant Garamond", Georgia, "Times New Roman", serif;
      --wp--preset--font-family--body: "Source Sans 3", "Segoe UI", sans-serif;
      --wp--preset--font-size--small: 0.9375rem;
      --wp--preset--font-size--medium: 1.125rem;
      --wp--preset--font-size--large: 1.375rem;
      --wp--preset--font-size--x-large: 1.75rem;
      --wp--preset--font-size--xx-large: 2.5rem;
      --wp--preset--font-size--xxx-large: 3.5rem;
      --wp--preset--font-size--hero: clamp(2.75rem, 5vw, 4.5rem);
    }
    body { margin: 0; background: var(--wp--preset--color--cream); color: var(--wp--preset--color--charcoal); }
    .mc-preview-badge {
      position: fixed; z-index: 9999; left: 12px; bottom: 12px;
      background: rgba(26,24,22,.88); color: #F7F2EA; font: 600 12px/1.2 system-ui,sans-serif;
      padding: .55rem .75rem; border-radius: 6px; pointer-events: none; opacity: .85;
    }
    .wp-block-navigation__container {
      display: flex; flex-wrap: wrap; gap: .15rem; list-style: none; margin: 0; padding: 0;
      align-items: center; justify-content: center;
    }
    .wp-block-navigation-item__content {
      display: inline-block; padding: .4rem .55rem; text-decoration: none;
      color: var(--wp--preset--color--ink); font-size: var(--wp--preset--font-size--small);
    }
    .wp-block-navigation-item__content[aria-current="page"] { color: var(--wp--preset--color--gold-deep); font-weight: 600; }
    @media (max-width: 960px) {
      .mc-header__nav .wp-block-navigation__container {
        flex-direction: column; align-items: flex-start;
      }
    }
  </style>
</head>
<body class="maison-calista-theme lang-${lang}${page.slug === '' ? ' mc-is-home' : ''}">
  <a class="mc-skip-link" href="#main-content">${lang === 'en' ? 'Skip to content' : 'Aller au contenu'}</a>
  ${header}
  <main id="main-content" class="wp-block-group">
    ${body}
  </main>
  ${footer}
  ${badge}
  <script src="/assets/js/navigation.js" defer></script>
  <script src="/assets/js/main.js" defer></script>
  ${page.slug === 'gallery' ? '<script src="/assets/js/gallery.js" defer></script>' : ''}
  <script>
    window.maisonCalista = window.maisonCalista || { i18n: {
      menuOpen: ${lang === 'en' ? "'Open menu'" : "'Ouvrir le menu'"},
      menuClose: ${lang === 'en' ? "'Close menu'" : "'Fermer le menu'"},
      lightbox: ${lang === 'en' ? "'Close image preview'" : "'Fermer l’aperçu'"}
    }};
  </script>
</body>
</html>`;
}

function resolvePage(pathname) {
  let lang = 'fr';
  let p = pathname;
  if (p.startsWith('/en/') || p === '/en') {
    lang = 'en';
    p = p.replace(/^\/en/, '') || '/';
  }
  p = p.replace(/\/+$/, '') || '/';
  if (p === '/') return { page: PAGES[0], lang };

  const slug = p.replace(/^\//, '').replace(/\/$/, '');
  const page = PAGES.find((x) => x.slug === slug);
  return page ? { page, lang } : null;
}

function build404(lang = 'fr') {
  const home = lang === 'en' ? '/en/' : '/';
  return `<!DOCTYPE html>
<html lang="${lang === 'en' ? 'en-US' : 'fr-FR'}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>${lang === 'en' ? 'Page not found' : 'Page introuvable'} · Maison Calista</title>
  <link rel="stylesheet" href="/assets/css/main.css" />
  <link rel="icon" href="/assets/images/logo/maison-calista-logo.svg" type="image/svg+xml" />
</head>
<body class="maison-calista-theme lang-${lang}">
  <main class="mc-404 mc-section mc-wide">
    <h1>${lang === 'en' ? 'Page not found' : 'Page introuvable'}</h1>
    <p>${lang === 'en'
      ? 'The page you are looking for may have moved.'
      : 'La page que vous recherchez a peut-être été déplacée.'}</p>
    <p><a class="mc-btn-cta" href="${home}">${lang === 'en' ? 'Back home' : 'Retour à l’accueil'}</a></p>
  </main>
</body>
</html>`;
}

module.exports = {
  ROOT,
  THEME,
  CONTENT,
  PAGES,
  PRICES,
  buildPage,
  build404,
  resolvePage,
};
