/**
 * Production sitemap.xml + robots.txt for the static Maison Calista site.
 */
const SITE_URL = (process.env.SITE_URL || 'https://www.maisoncalista.com').replace(/\/+$/, '');

/** @param {string} slug */
function pathFor(lang, slug) {
  if (lang === 'en') {
    return slug ? `/en/${slug}/` : '/en/';
  }
  return slug ? `/${slug}/` : '/';
}

/** @param {string} pathname */
function abs(pathname) {
  return `${SITE_URL}${pathname}`;
}

/**
 * Priority hints: home highest, legal lower.
 * @param {string} slug
 */
function priorityFor(slug) {
  if (!slug) return '1.0';
  if (slug === 'contact' || slug === 'stays-pricing') return '0.9';
  if (slug === 'privacy-policy' || slug === 'cookie-policy') return '0.3';
  return '0.8';
}

/**
 * @param {string} slug
 */
function changefreqFor(slug) {
  if (!slug) return 'weekly';
  if (slug === 'privacy-policy' || slug === 'cookie-policy') return 'yearly';
  if (slug === 'gallery' || slug === 'stays-pricing') return 'monthly';
  return 'monthly';
}

/**
 * @param {{ slug: string }[]} pages
 * @param {string} [lastmod] ISO date YYYY-MM-DD
 */
function buildSitemapXml(pages, lastmod = new Date().toISOString().slice(0, 10)) {
  const urls = [];

  for (const page of pages) {
    const slug = page.slug || '';
    const frPath = pathFor('fr', slug);
    const enPath = pathFor('en', slug);
    const frLoc = abs(frPath);
    const enLoc = abs(enPath);
    const priority = priorityFor(slug);
    const changefreq = changefreqFor(slug);

    const hreflang = [
      `    <xhtml:link rel="alternate" hreflang="fr" href="${escapeXml(frLoc)}" />`,
      `    <xhtml:link rel="alternate" hreflang="en" href="${escapeXml(enLoc)}" />`,
      `    <xhtml:link rel="alternate" hreflang="x-default" href="${escapeXml(frLoc)}" />`,
    ].join('\n');

    for (const loc of [frLoc, enLoc]) {
      urls.push(`  <url>
    <loc>${escapeXml(loc)}</loc>
${hreflang}
    <lastmod>${lastmod}</lastmod>
    <changefreq>${changefreq}</changefreq>
    <priority>${priority}</priority>
  </url>`);
    }
  }

  return `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
${urls.join('\n')}
</urlset>
`;
}

function buildRobotsTxt() {
  return `User-agent: *
Allow: /

# Static Maison Calista site (FR default + EN)
Sitemap: ${SITE_URL}/sitemap.xml
`;
}

/** @param {string} value */
function escapeXml(value) {
  return value
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&apos;');
}

module.exports = {
  SITE_URL,
  buildSitemapXml,
  buildRobotsTxt,
  pathFor,
  abs,
};
