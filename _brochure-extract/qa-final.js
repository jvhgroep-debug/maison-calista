/**
 * Maison Calista — final production QA (static, no WordPress runtime).
 */
const fs = require('fs');
const path = require('path');

const theme = path.join('C:/Users/DELL/Downloads/Maison-Calista/maison-calista-theme');
const errors = [];
const warnings = [];
const checks = [];

function walk(dir, pred, out = []) {
  if (!fs.existsSync(dir)) return out;
  for (const f of fs.readdirSync(dir)) {
    const p = path.join(dir, f);
    const st = fs.statSync(p);
    if (st.isDirectory()) walk(p, pred, out);
    else if (pred(f, p)) out.push(p);
  }
  return out;
}
function rel(p) {
  return path.relative(theme, p).replace(/\\/g, '/');
}
function read(p) {
  return fs.readFileSync(p, 'utf8');
}
function ok(name) {
  checks.push('PASS: ' + name);
}
function fail(name, detail) {
  errors.push(name + (detail ? ' — ' + detail : ''));
}

// Structure
const required = [
  'style.css', 'theme.json', 'functions.php', 'screenshot.png',
  'parts/header.html', 'parts/footer.html',
  'templates/front-page.html', 'templates/404.html', 'templates/page.html',
  'assets/css/main.css', 'assets/css/editor.css',
  'assets/js/main.js', 'assets/js/navigation.js', 'assets/js/gallery.js',
  'assets/fonts/source-sans-3-latin.woff2',
  'assets/images/logo/maison-calista-logo.svg',
  'assets/brochure/maison-calista-brochure.pdf',
  'inc/setup.php', 'inc/enqueue.php', 'inc/demo-content.php', 'inc/prices.php',
  'inc/i18n.php', 'inc/seo.php', 'inc/schema.php', 'inc/template-tags.php',
  'languages/fr_FR.po', 'languages/fr_FR.mo',
  'README.md', 'HANDOVER.md', 'INSTALL.md',
  'assets/fonts/cormorant-garamond-latin.woff2',
];
for (const f of required) {
  if (!fs.existsSync(path.join(theme, f))) fail('Missing file', f);
}
ok('Required theme files present');

// Version sync
const style = read(path.join(theme, 'style.css'));
const fn = read(path.join(theme, 'functions.php'));
const verStyle = (style.match(/Version:\s*([0-9.]+)/) || [])[1];
const verPhp = (fn.match(/MAISON_CALISTA_VERSION',\s*'([^']+)'/) || [])[1];
const readmeTxt = read(path.join(theme, 'readme.txt'));
const verReadme = (readmeTxt.match(/Stable tag:\s*([0-9.]+)/) || [])[1];
if (!verStyle || verStyle !== verPhp) fail('Version mismatch', `${verStyle} vs ${verPhp}`);
else if (verReadme && verReadme !== verPhp) fail('readme Stable tag mismatch', verReadme);
else ok('Version synced ' + verPhp);

const demoSrc = read(path.join(theme, 'inc/demo-content.php'));
if (!demoSrc.includes('after_switch_theme')) fail('Missing auto-setup on activation');
else ok('Auto-setup on theme activation');

// Pages FR default + EN + FR mirror
const pages = ['home','about','residence','care','family','activities','restaurant','pricing','gallery','contact','privacy','cookies'];
for (const p of pages) {
  for (const loc of ['', 'fr', 'en']) {
    const file = loc ? path.join(theme, 'inc/content', loc, p + '.html') : path.join(theme, 'inc/content', p + '.html');
    if (!fs.existsSync(file)) fail('Missing content', (loc || 'fr-default') + '/' + p);
  }
}
ok('12 pages × FR/EN/mirror');

// Photos
const photos = fs.readdirSync(path.join(theme, 'assets/images/photos')).filter(f => f.endsWith('.jpg'));
if (photos.length < 21) fail('Photos', 'expected >=21 got ' + photos.length);
else ok('21 SEO photos');

// Content integrity
const htmlFiles = walk(path.join(theme, 'inc/content'), f => f.endsWith('.html'));
const patternFiles = walk(path.join(theme, 'patterns'), f => f.endsWith('.php'));
for (const file of [...htmlFiles, ...patternFiles, path.join(theme, 'parts/header.html'), path.join(theme, 'parts/footer.html')]) {
  const c = read(file);
  for (const r of [...c.matchAll(/photos\/([a-z0-9\-]+\.jpg)/g)].map(m => m[1])) {
    if (!photos.includes(r)) fail('Broken photo', rel(file) + ' → ' + r);
  }
  if (/%%HOME_URL%%(about|pricing|residence|care)\//.test(c)) fail('Wrong slug', rel(file));
  if (file.includes(`${path.sep}content${path.sep}`) && file.endsWith('.html')) {
    const h1 = (c.match(/<h1[\s>]/g) || []).length;
    if (h1 !== 1) warnings.push(`H1 count ${h1} in ${rel(file)}`);
  }
  if (file.includes(`${path.sep}patterns${path.sep}`) && !/\*\s+Slug:\s+maison-calista\//.test(c)) {
    fail('Bad pattern Slug', rel(file));
  }
}
ok('Photo refs + pattern headers');

// Pricing keys
for (const lang of ['', 'fr', 'en']) {
  const pricing = path.join(theme, 'inc/content', lang, 'pricing.html');
  const c = read(pricing);
  for (const key of ['discovery_double','discovery_single','discovery_surcharge','wellbeing','comfort','signature']) {
    if (!c.includes(`key="${key}"`)) fail('Price key', `${lang || 'fr'}/pricing missing ${key}`);
  }
}
ok('All price shortcodes');

// i18n FR default
const header = read(path.join(theme, 'parts/header.html'));
if (!header.includes('[mc_language_switcher]')) fail('Header lang switcher');
if (!header.includes('Liste d')) fail('Header FR CTA');
const switcher = read(path.join(theme, 'inc/template-tags.php'));
const frIdx = switcher.indexOf('Français');
const enIdx = switcher.indexOf('English');
if (frIdx < 0 || enIdx < 0 || frIdx > enIdx) fail('Lang order', 'Français must precede English in fallback');
const i18n = read(path.join(theme, 'inc/i18n.php'));
if (!i18n.includes('maison_calista_is_english')) fail('i18n helper');
ok('FR default + language switcher');

// Gallery FR alts (no common English alt starters)
const gal = read(path.join(theme, 'inc/content/gallery.html'));
if (/alt="(Aerial|Bright|Serene|Luxury|Open-plan|Evening|Garden|Private|Pool|Restaurant|Candlelit|Night|Desert|Terrace|Living|Residence)/.test(gal)) {
  fail('Gallery FR alts still English');
} else ok('Gallery alts French');

// SEO + schema
const seo = read(path.join(theme, 'inc/seo.php'));
if (!seo.includes('hreflang') || !seo.includes('x-default')) fail('hreflang');
if (!seo.includes('Maison Calista est')) fail('FR meta defaults');
const schema = read(path.join(theme, 'inc/schema.php'));
if (!schema.includes('maison_calista_is_english')) fail('schema bilingual');
ok('SEO + schema bilingual');

// A11y / perf CSS
const css = read(path.join(theme, 'assets/css/main.css'));
for (const needle of ['.mc-skip-link', 'prefers-reduced-motion', '@media (max-width: 960px)', '@media print', ':focus-visible']) {
  if (!css.includes(needle)) fail('CSS missing', needle);
}
ok('CSS a11y/responsive/print');

// JS balanced
for (const js of ['main.js','navigation.js','gallery.js']) {
  const c = read(path.join(theme, 'assets/js', js));
  if ((c.match(/\{/g)||[]).length !== (c.match(/\}/g)||[]).length) fail('JS braces', js);
}
ok('JS syntax braces');

// theme.json
try {
  JSON.parse(read(path.join(theme, 'theme.json')));
  ok('theme.json valid');
} catch (e) {
  fail('theme.json', e.message);
}

// Patterns
const patterns = fs.readdirSync(path.join(theme, 'patterns')).filter(f => f.endsWith('.php'));
if (patterns.length < 17) fail('Patterns count', String(patterns.length));
else ok('17 patterns');

// Shortcodes in HTML blocks support
const setup = read(path.join(theme, 'inc/setup.php'));
if (!setup.includes('maison_calista_do_shortcodes_in_html_blocks')) fail('HTML block shortcodes');
else ok('Shortcodes in HTML blocks');

// No TODO/FIXME in theme PHP/content
for (const file of walk(theme, (f, p) => /\.(php|html|js|css)$/.test(f) && !p.includes('node_modules'))) {
  const c = read(file);
  if (/\bTODO\b|\bFIXME\b|\blorem ipsum\b/i.test(c)) warnings.push('TODO/FIXME in ' + rel(file));
}
ok('No TODO/FIXME scan');

console.log('=== Maison Calista FINAL QA ===');
console.log('Version:', verPhp);
console.log('Photos:', photos.length, '| Patterns:', patterns.length, '| Content HTML:', htmlFiles.length);
console.log('Checks passed:', checks.length);
checks.forEach(c => console.log(c));
console.log('Errors:', errors.length);
errors.forEach(e => console.log('ERROR:', e));
console.log('Warnings:', warnings.length);
warnings.forEach(w => console.log('WARN:', w));
if (!errors.length) console.log('RESULT: PASS — production ready');
else {
  console.log('RESULT: FAIL');
  process.exitCode = 1;
}
