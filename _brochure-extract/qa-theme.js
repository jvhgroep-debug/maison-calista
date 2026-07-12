/**
 * Maison Calista theme QA — local static checks (no WordPress runtime).
 */
const fs = require('fs');
const path = require('path');

const theme = path.join('C:/Users/DELL/Downloads/Maison-Calista/maison-calista-theme');
const errors = [];
const warnings = [];

function walk(dir, pred, out = []) {
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

// Required structure
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
  'README.md', 'HANDOVER.md',
];
for (const f of required) {
  if (!fs.existsSync(path.join(theme, f))) errors.push('Missing required file: ' + f);
}

// Pages — French is default (content root + fr/); English lives in en/
const pages = ['home','about','residence','care','family','activities','restaurant','pricing','gallery','contact','privacy','cookies'];
for (const p of pages) {
  const frRoot = path.join(theme, 'inc/content', p + '.html');
  const fr = path.join(theme, 'inc/content/fr', p + '.html');
  const en = path.join(theme, 'inc/content/en', p + '.html');
  if (!fs.existsSync(frRoot)) errors.push('Missing FR (default) content: ' + p);
  if (!fs.existsSync(fr)) errors.push('Missing FR mirror content: ' + p);
  if (!fs.existsSync(en)) errors.push('Missing EN content: en/' + p);
}

// Language switcher: Français first, English second
const header = fs.readFileSync(path.join(theme, 'parts/header.html'), 'utf8');
if (!header.includes('[mc_language_switcher]')) errors.push('Header missing language switcher');
if (!header.includes('Liste d')) {
  errors.push('Header CTA should be French by default');
}

// Photos
const photos = fs.readdirSync(path.join(theme, 'assets/images/photos')).filter(f => f.endsWith('.jpg'));
if (photos.length < 21) errors.push('Expected >=21 photos, got ' + photos.length);

// Photo refs
const htmlFiles = walk(path.join(theme, 'inc/content'), f => f.endsWith('.html'));
const patternFiles = walk(path.join(theme, 'patterns'), f => f.endsWith('.php'));
for (const file of [...htmlFiles, ...patternFiles, path.join(theme, 'parts/header.html'), path.join(theme, 'parts/footer.html')]) {
  const c = fs.readFileSync(file, 'utf8');
  const refs = [...c.matchAll(/photos\/([a-z0-9\-]+\.jpg)/g)].map(m => m[1]);
  for (const r of refs) {
    if (!photos.includes(r)) errors.push(`Broken photo in ${rel(file)}: ${r}`);
  }
  // Wrong slugs
  if (/%%HOME_URL%%(about|pricing|residence|care)\//.test(c)) {
    errors.push('Short/wrong slug in ' + rel(file));
  }
  // H1 count for content pages
  if (file.includes(`${path.sep}content${path.sep}`) && file.endsWith('.html') && !file.includes(`${path.sep}index.php`)) {
    const h1 = (c.match(/<h1[\s>]/g) || []).length;
    if (h1 !== 1) warnings.push(`H1 count ${h1} in ${rel(file)}`);
  }
  // Pattern headers
  if (file.includes(`${path.sep}patterns${path.sep}`)) {
    if (!/\*\s+Slug:\s+maison-calista\//.test(c)) errors.push('Bad pattern Slug header: ' + rel(file));
  }
}

// Pricing shortcodes (FR root, FR mirror, EN)
for (const lang of ['', 'fr', 'en']) {
  const pricing = path.join(theme, 'inc/content', lang, 'pricing.html');
  const label = lang === '' ? 'fr-default' : lang;
  if (!fs.existsSync(pricing)) {
    errors.push('Missing pricing.html for ' + label);
    continue;
  }
  const c = fs.readFileSync(pricing, 'utf8');
  for (const key of ['discovery_double','discovery_single','discovery_surcharge','wellbeing','comfort','signature']) {
    if (!c.includes(`key="${key}"`)) errors.push(`Missing price key ${key} in ${label}/pricing.html`);
  }
}

// i18n helpers
const i18n = fs.readFileSync(path.join(theme, 'inc/i18n.php'), 'utf8');
if (!i18n.includes('maison_calista_is_english')) errors.push('Missing maison_calista_is_english()');
const switcherSrc = fs.readFileSync(path.join(theme, 'inc/template-tags.php'), 'utf8');
if (!switcherSrc.includes('Français') || !switcherSrc.includes('English')) {
  errors.push('Language switcher labels missing Français/English');
}

// PHP syntax-ish checks
const phpFiles = walk(theme, (f, p) => f.endsWith('.php') && !p.includes('node_modules'));
for (const file of phpFiles) {
  const c = fs.readFileSync(file, 'utf8');
  if (c.includes('<?php') && (c.match(/\{/g) || []).length !== (c.match(/\}/g) || []).length) {
    warnings.push('Unbalanced braces?: ' + rel(file));
  }
  if (/__\(\s*\$/.test(c)) errors.push('Variable used in __(): ' + rel(file));
}

// theme.json parse
try {
  JSON.parse(fs.readFileSync(path.join(theme, 'theme.json'), 'utf8'));
} catch (e) {
  errors.push('theme.json invalid JSON: ' + e.message);
}

// CSS basic
const css = fs.readFileSync(path.join(theme, 'assets/css/main.css'), 'utf8');
if (!css.includes('@media (max-width: 960px)')) errors.push('Missing mobile nav breakpoint CSS');
if (!css.includes('prefers-reduced-motion')) errors.push('Missing reduced-motion support');
if (!css.includes('.mc-skip-link')) errors.push('Missing skip link CSS');

// JS no obvious syntax errors - check balanced
for (const js of ['main.js','navigation.js','gallery.js']) {
  const c = fs.readFileSync(path.join(theme, 'assets/js', js), 'utf8');
  if ((c.match(/\{/g)||[]).length !== (c.match(/\}/g)||[]).length) errors.push('Unbalanced JS braces: ' + js);
}

// Patterns count
const patterns = fs.readdirSync(path.join(theme, 'patterns')).filter(f => f.endsWith('.php'));
if (patterns.length < 17) errors.push('Expected 17 patterns, got ' + patterns.length);

console.log('=== Maison Calista QA ===');
console.log('Photos:', photos.length);
console.log('Patterns:', patterns.length);
console.log('Content files:', htmlFiles.length);
console.log('Errors:', errors.length);
errors.forEach(e => console.log('ERROR:', e));
console.log('Warnings:', warnings.length);
warnings.forEach(w => console.log('WARN:', w));
if (!errors.length) console.log('RESULT: PASS');
else {
  console.log('RESULT: FAIL');
  process.exitCode = 1;
}
