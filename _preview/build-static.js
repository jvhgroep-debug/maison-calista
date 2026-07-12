/**
 * Static export for Cloudflare Pages.
 * Output: /site (HTML pages + /theme assets)
 *
 * Usage: node _preview/build-static.js
 */
const fs = require('fs');
const path = require('path');
const { ROOT, THEME, PAGES, buildPage, build404 } = require('./site-engine');

const OUT = path.join(ROOT, 'site');

const SKIP_ASSET_DIRS = new Set([
  'source-whatsapp',
  'gallery', // nested thumbs under photos/gallery
  'scripts',
  'node_modules',
]);

function rimraf(dir) {
  if (fs.existsSync(dir)) fs.rmSync(dir, { recursive: true, force: true });
}

function ensureDir(dir) {
  fs.mkdirSync(dir, { recursive: true });
}

function shouldSkip(relPosix) {
  const parts = relPosix.split('/');
  if (parts.some((p) => SKIP_ASSET_DIRS.has(p))) return true;
  if (relPosix.includes('assets/images/photos/gallery')) return true;
  if (relPosix.includes('assets/images/source-whatsapp')) return true;
  return false;
}

function copyTree(src, dest, base = src) {
  ensureDir(dest);
  for (const name of fs.readdirSync(src)) {
    const from = path.join(src, name);
    const to = path.join(dest, name);
    const rel = path.relative(base, from).replace(/\\/g, '/');
    const st = fs.statSync(from);
    if (shouldSkip(rel)) continue;
    if (st.isDirectory()) copyTree(from, to, base);
    else fs.copyFileSync(from, to);
  }
}

function writePage(relDir, html) {
  const dir = path.join(OUT, relDir);
  ensureDir(dir);
  fs.writeFileSync(path.join(dir, 'index.html'), html, 'utf8');
}

function build() {
  console.log('Building Maison Calista static site for Cloudflare Pages…');
  rimraf(OUT);
  ensureDir(OUT);

  for (const page of PAGES) {
    writePage(page.slug || '.', buildPage(page, 'fr', { production: true }));
    writePage(path.join('en', page.slug || '.'), buildPage(page, 'en', { production: true }));
  }

  fs.writeFileSync(path.join(OUT, '404.html'), build404('fr'), 'utf8');

  // Theme public assets under /theme/...
  copyTree(
    path.join(THEME, 'assets'),
    path.join(OUT, 'theme', 'assets'),
    path.join(THEME, 'assets')
  );

  // Cloudflare Pages helpers
  fs.writeFileSync(
    path.join(OUT, '_redirects'),
    [
      '# Pretty URLs already use trailing-slash directories',
      '/en  /en/  301',
    ].join('\n') + '\n',
    'utf8'
  );

  fs.writeFileSync(
    path.join(OUT, '_headers'),
    [
      '/*',
      '  X-Content-Type-Options: nosniff',
      '  Referrer-Policy: strict-origin-when-cross-origin',
      '',
      '/theme/assets/*',
      '  Cache-Control: public, max-age=31536000, immutable',
    ].join('\n') + '\n',
    'utf8'
  );

  // Count outputs
  let htmlCount = 0;
  function countHtml(dir) {
    for (const name of fs.readdirSync(dir)) {
      const p = path.join(dir, name);
      if (fs.statSync(p).isDirectory()) countHtml(p);
      else if (name.endsWith('.html')) htmlCount += 1;
    }
  }
  countHtml(OUT);

  console.log('Output:', OUT);
  console.log('HTML files:', htmlCount);
  console.log('DONE');
}

build();
