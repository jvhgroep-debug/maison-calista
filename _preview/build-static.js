/**
 * Static export for Cloudflare Pages / production deploy.
 * Output: /dist (index.html, all pages, assets, CSS, JS)
 *
 * Usage: npm run build
 */
const fs = require('fs');
const path = require('path');
const { ROOT, THEME, PAGES, buildPage, build404 } = require('./site-engine');
const { SITE_URL, buildSitemapXml, buildRobotsTxt, writeUtf8NoBom, assertValidSitemapXml } = require('./sitemap');

const OUT = path.join(ROOT, 'dist');

const SKIP_ASSET_DIRS = new Set([
  'source-whatsapp',
  'gallery',
  'scripts',
  'node_modules',
]);

/** Cloudflare Workers Static Assets max file size is 25 MiB. */
const MAX_ASSET_BYTES = 25 * 1024 * 1024;

function rimraf(dir) {
  if (fs.existsSync(dir)) fs.rmSync(dir, { recursive: true, force: true });
}

function ensureDir(dir) {
  fs.mkdirSync(dir, { recursive: true });
}

function shouldSkip(relPosix, fileSize = 0) {
  const parts = relPosix.split('/');
  if (parts.some((p) => SKIP_ASSET_DIRS.has(p))) return true;
  if (relPosix.includes('assets/images/photos/gallery')) return true;
  if (relPosix.includes('assets/images/source-whatsapp')) return true;
  // Brochure PDF exceeds Workers 25 MiB asset limit — keep out of /dist.
  if (parts.includes('brochure')) return true;
  if (relPosix.replace(/\\/g, '/').endsWith('maison-calista-brochure.pdf')) return true;
  if (fileSize > MAX_ASSET_BYTES) return true;
  return false;
}

function copyTree(src, dest, base = src) {
  ensureDir(dest);
  for (const name of fs.readdirSync(src)) {
    const from = path.join(src, name);
    const to = path.join(dest, name);
    const rel = path.relative(base, from).replace(/\\/g, '/');
    const st = fs.statSync(from);
    if (st.isDirectory()) {
      if (shouldSkip(rel)) continue;
      copyTree(from, to, base);
    } else {
      if (shouldSkip(rel, st.size)) {
        if (st.size > MAX_ASSET_BYTES || rel.includes('brochure')) {
          console.warn('Skipping oversized/excluded asset:', rel, `(${(st.size / (1024 * 1024)).toFixed(1)} MiB)`);
        }
        continue;
      }
      fs.copyFileSync(from, to);
    }
  }
}

function writePage(relDir, html) {
  const dir = path.join(OUT, relDir);
  ensureDir(dir);
  fs.writeFileSync(path.join(dir, 'index.html'), html, 'utf8');
}

function build() {
  console.log('Building Maison Calista static website → /dist');
  rimraf(OUT);
  ensureDir(OUT);

  for (const page of PAGES) {
    writePage(page.slug || '.', buildPage(page, 'fr', { production: true }));
    writePage(path.join('en', page.slug || '.'), buildPage(page, 'en', { production: true }));
  }

  fs.writeFileSync(path.join(OUT, '404.html'), build404('fr'), 'utf8');

  // Public assets at /assets/...
  copyTree(
    path.join(THEME, 'assets'),
    path.join(OUT, 'assets'),
    path.join(THEME, 'assets')
  );

  fs.writeFileSync(
    path.join(OUT, '_redirects'),
    ['/en  /en/  301', ''].join('\n'),
    'utf8'
  );

  fs.writeFileSync(
    path.join(OUT, '_headers'),
    [
      '/sitemap.xml',
      '  Content-Type: application/xml; charset=utf-8',
      '  X-Content-Type-Options: nosniff',
      '  Cache-Control: public, max-age=3600',
      '',
      '/robots.txt',
      '  Content-Type: text/plain; charset=utf-8',
      '  Cache-Control: public, max-age=3600',
      '',
      '/*',
      '  X-Content-Type-Options: nosniff',
      '  Referrer-Policy: strict-origin-when-cross-origin',
      '',
      '/assets/*',
      '  Cache-Control: public, max-age=31536000, immutable',
      '',
    ].join('\n'),
    'utf8'
  );

  // SEO: sitemap + robots (all FR/EN URLs + hreflang) — UTF-8 without BOM
  const lastmod = new Date().toISOString().slice(0, 10);
  const sitemapXml = buildSitemapXml(PAGES, lastmod);
  assertValidSitemapXml(sitemapXml);
  writeUtf8NoBom(path.join(OUT, 'sitemap.xml'), sitemapXml);
  writeUtf8NoBom(path.join(OUT, 'robots.txt'), buildRobotsTxt());

  // Workers Static Assets ignore file (safety net for maps / junk).
  fs.writeFileSync(path.join(OUT, '.assetsignore'), ['*.map', ''].join('\n'), 'utf8');

  let htmlCount = 0;
  function countHtml(dir) {
    for (const name of fs.readdirSync(dir)) {
      const p = path.join(dir, name);
      if (fs.statSync(p).isDirectory()) countHtml(p);
      else if (name.endsWith('.html')) htmlCount += 1;
    }
  }
  countHtml(OUT);

  const required = [
    'index.html',
    'en/index.html',
    'about-maison-calista/index.html',
    'assets/css/main.css',
    'assets/js/main.js',
    'assets/js/navigation.js',
    'assets/images/logo/maison-calista-logo.svg',
    'sitemap.xml',
    'robots.txt',
  ];
  for (const rel of required) {
    if (!fs.existsSync(path.join(OUT, rel))) {
      console.error('Missing required file:', rel);
      process.exit(1);
    }
  }

  console.log('Output:', OUT);
  console.log('HTML files:', htmlCount);
  console.log('Sitemap:', SITE_URL + '/sitemap.xml');
  console.log('DONE');
}

build();
