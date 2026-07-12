/**
 * Production sitemap.xml + robots.txt (sitemap.org + Google hreflang).
 * Emits UTF-8 XML without BOM for correct browser XML rendering.
 */
const SITE_URL = (process.env.SITE_URL || 'https://www.associationcalista.com').replace(/\/+$/, '');

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

/** @param {string} slug */
function priorityFor(slug) {
  if (!slug) return '1.0';
  if (slug === 'contact' || slug === 'stays-pricing') return '0.9';
  if (slug === 'privacy-policy' || slug === 'cookie-policy') return '0.3';
  return '0.8';
}

/** @param {string} slug */
function changefreqFor(slug) {
  if (!slug) return 'weekly';
  if (slug === 'privacy-policy' || slug === 'cookie-policy') return 'yearly';
  return 'monthly';
}

/** Escape text for XML element/attribute content. */
function escapeXml(value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

/**
 * Build one <url> entry with xhtml hreflang alternates (Google multilingual sitemap).
 * @param {string} loc
 * @param {string} frLoc
 * @param {string} enLoc
 * @param {string} lastmod
 * @param {string} changefreq
 * @param {string} priority
 */
function urlEntry(loc, frLoc, enLoc, lastmod, changefreq, priority) {
  return [
    '  <url>',
    `    <loc>${escapeXml(loc)}</loc>`,
    `    <lastmod>${escapeXml(lastmod)}</lastmod>`,
    `    <changefreq>${escapeXml(changefreq)}</changefreq>`,
    `    <priority>${escapeXml(priority)}</priority>`,
    `    <xhtml:link rel="alternate" hreflang="fr" href="${escapeXml(frLoc)}"/>`,
    `    <xhtml:link rel="alternate" hreflang="en" href="${escapeXml(enLoc)}"/>`,
    `    <xhtml:link rel="alternate" hreflang="x-default" href="${escapeXml(frLoc)}"/>`,
    '  </url>',
  ].join('\n');
}

/**
 * @param {{ slug: string }[]} pages
 * @param {string} [lastmod] ISO date YYYY-MM-DD
 * @returns {string} Well-formed UTF-8 XML document (no BOM)
 */
function buildSitemapXml(pages, lastmod = new Date().toISOString().slice(0, 10)) {
  const entries = [];

  for (const page of pages) {
    const slug = page.slug || '';
    const frLoc = abs(pathFor('fr', slug));
    const enLoc = abs(pathFor('en', slug));
    const priority = priorityFor(slug);
    const changefreq = changefreqFor(slug);

    entries.push(urlEntry(frLoc, frLoc, enLoc, lastmod, changefreq, priority));
    entries.push(urlEntry(enLoc, frLoc, enLoc, lastmod, changefreq, priority));
  }

  // sitemap.org urlset + Google xhtml alternate links
  // https://www.sitemaps.org/protocol.html
  // https://developers.google.com/search/docs/specialty/international/localized-versions#sitemap
  const xml =
    '<?xml version="1.0" encoding="UTF-8"?>\n' +
    '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"\n' +
    '        xmlns:xhtml="http://www.w3.org/1999/xhtml">\n' +
    entries.join('\n') +
    '\n</urlset>\n';

  return xml;
}

function buildRobotsTxt() {
  return (
    'User-agent: *\n' +
    'Allow: /\n' +
    '\n' +
    'Sitemap: ' +
    SITE_URL +
    '/sitemap.xml\n'
  );
}

/**
 * Write UTF-8 file without BOM (critical for XML declaration parsing).
 * @param {string} filePath
 * @param {string} content
 */
function writeUtf8NoBom(filePath, content) {
  const fs = require('fs');
  fs.writeFileSync(filePath, Buffer.from(content, 'utf8'));
}

/**
 * Minimal well-formedness check for our sitemap document.
 * @param {string} xml
 */
function assertValidSitemapXml(xml) {
  if (xml.charCodeAt(0) === 0xfeff) {
    throw new Error('sitemap.xml must not start with a UTF-8 BOM');
  }
  if (!xml.startsWith('<?xml version="1.0" encoding="UTF-8"?>')) {
    throw new Error('sitemap.xml missing XML declaration');
  }
  if (!xml.includes('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"')) {
    throw new Error('sitemap.xml missing urlset namespace');
  }
  if (!xml.includes('xmlns:xhtml="http://www.w3.org/1999/xhtml"')) {
    throw new Error('sitemap.xml missing xhtml namespace');
  }
  if (!xml.trimEnd().endsWith('</urlset>')) {
    throw new Error('sitemap.xml missing closing urlset');
  }
  const urls = (xml.match(/<url>/g) || []).length;
  const locs = (xml.match(/<loc>/g) || []).length;
  if (urls < 2 || urls !== locs) {
    throw new Error(`sitemap.xml url/loc mismatch (${urls} urls, ${locs} locs)`);
  }
  if (!(xml.match(/hreflang="fr"/g) || []).length) {
    throw new Error('sitemap.xml missing hreflang=fr');
  }
  // Reject HTML/plain wrappers
  if (/<!DOCTYPE html|<html[\s>]/i.test(xml)) {
    throw new Error('sitemap.xml looks like HTML, not XML');
  }
  const legacyHost = ['maison', 'calista', '.com'].join('');
  if (xml.toLowerCase().includes(legacyHost)) {
    throw new Error('sitemap.xml still contains the previous production domain');
  }
  if (!xml.includes(SITE_URL)) {
    throw new Error(`sitemap.xml missing SITE_URL ${SITE_URL}`);
  }
  const badLoc = xml.match(/<loc>([^<]+)<\/loc>/g) || [];
  for (const loc of badLoc) {
    if (!loc.includes(SITE_URL)) {
      throw new Error(`sitemap.xml loc uses unexpected host: ${loc}`);
    }
  }
}

module.exports = {
  SITE_URL,
  buildSitemapXml,
  buildRobotsTxt,
  writeUtf8NoBom,
  assertValidSitemapXml,
  pathFor,
  abs,
};
