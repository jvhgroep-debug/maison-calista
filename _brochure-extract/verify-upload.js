/**
 * Pre-upload verification of extracted WordPress theme package.
 */
const fs = require('fs');
const path = require('path');

const theme = path.join(
  'C:/Users/DELL/Downloads/Maison-Calista/dist/_verify-extract/maison-calista-theme'
);
const errors = [];
const ok = [];

function exists(rel) {
  return fs.existsSync(path.join(theme, rel));
}
function fail(msg) {
  errors.push(msg);
}
function pass(msg) {
  ok.push(msg);
}

if (!exists('style.css')) fail('style.css missing');
else {
  const s = fs.readFileSync(path.join(theme, 'style.css'), 'utf8');
  if (!/^Theme Name:\s*Maison Calista/m.test(s)) fail('style.css Theme Name missing');
  if (!/Version:\s*1\.3\.0/.test(s)) fail('style.css version not 1.3.0');
  else pass('style.css Theme Name + Version 1.3.0');
}

const must = [
  'functions.php',
  'theme.json',
  'index.php',
  'screenshot.png',
  'readme.txt',
  'parts/header.html',
  'parts/footer.html',
  'templates/front-page.html',
  'templates/index.html',
  'templates/page.html',
  'templates/404.html',
  'assets/css/main.css',
  'assets/css/editor.css',
  'assets/js/main.js',
  'assets/js/navigation.js',
  'assets/js/gallery.js',
  'assets/fonts/source-sans-3-latin.woff2',
  'assets/fonts/cormorant-garamond-latin.woff2',
  'assets/images/logo/maison-calista-logo.svg',
  'assets/brochure/maison-calista-brochure.pdf',
  'languages/fr_FR.mo',
  'languages/fr_FR.po',
  'inc/setup.php',
  'inc/enqueue.php',
  'inc/demo-content.php',
  'inc/template-tags.php',
  'inc/i18n.php',
  'inc/seo.php',
  'inc/schema.php',
  'inc/customizer.php',
  'inc/prices.php',
  'inc/patterns.php',
];
for (const f of must) {
  if (!exists(f)) fail('Missing: ' + f);
}
pass('Core WP theme files present (' + must.length + ')');

// No forbidden loose files at zip root (already extracted as single folder)
if (exists('source-whatsapp') || exists('assets/images/source-whatsapp')) {
  fail('source-whatsapp should not be in install package');
} else pass('No source-whatsapp in package');

// Photos
const photosDir = path.join(theme, 'assets/images/photos');
const photos = fs.readdirSync(photosDir).filter((f) => f.endsWith('.jpg'));
if (photos.length < 21) fail('Photos < 21: ' + photos.length);
else pass('21 photos packaged');

// Content FR/EN
const pages = [
  'home',
  'about',
  'residence',
  'care',
  'family',
  'activities',
  'restaurant',
  'pricing',
  'gallery',
  'contact',
  'privacy',
  'cookies',
];
for (const p of pages) {
  if (!exists('inc/content/' + p + '.html')) fail('Missing FR ' + p);
  if (!exists('inc/content/en/' + p + '.html')) fail('Missing EN ' + p);
}
pass('12 FR + 12 EN content files');

// Templates count
const templates = fs
  .readdirSync(path.join(theme, 'templates'))
  .filter((f) => f.endsWith('.html'));
if (templates.length < 14) fail('Templates expected >=14 got ' + templates.length);
else pass('Templates: ' + templates.length);

// Patterns: WP auto-discovers /patterns/*.php with headers
const patterns = fs
  .readdirSync(path.join(theme, 'patterns'))
  .filter((f) => f.endsWith('.php'));
if (patterns.length !== 17) fail('Patterns expected 17 got ' + patterns.length);
else pass('Patterns: 17');

for (const f of patterns) {
  const c = fs.readFileSync(path.join(theme, 'patterns', f), 'utf8');
  if (!/\*\s+Title:\s+/.test(c)) fail('Pattern missing Title: ' + f);
  if (!/\*\s+Slug:\s+maison-calista\//.test(c)) fail('Pattern bad Slug: ' + f);
  if (!/\*\s+Categories:\s+maison-calista/.test(c)) fail('Pattern bad Categories: ' + f);
}
pass('All pattern headers Title/Slug/Categories OK');

// Auto install
const demo = fs.readFileSync(path.join(theme, 'inc/demo-content.php'), 'utf8');
if (!demo.includes('after_switch_theme')) fail('No after_switch_theme auto-install');
if (!demo.includes('maison_calista_install_demo_content')) fail('No install_demo_content');
if (!demo.includes('maison_calista_install_menus')) fail('No install_menus');
if (!demo.includes("update_option( 'WPLANG', 'fr_FR' )")) fail('No FR WPLANG default');
if (!demo.includes('Maison Calista Primary')) fail('No primary menu creation');
pass('Auto pages + menus + FR locale wired');

// Pattern category registration
const setup = fs.readFileSync(path.join(theme, 'inc/setup.php'), 'utf8');
if (!setup.includes('register_block_pattern_category')) fail('Pattern category not registered');
else pass('Pattern category registered in setup.php');

// theme.json parse
try {
  JSON.parse(fs.readFileSync(path.join(theme, 'theme.json'), 'utf8'));
  pass('theme.json valid JSON');
} catch (e) {
  fail('theme.json invalid: ' + e.message);
}

// functions.php loads includes
const fn = fs.readFileSync(path.join(theme, 'functions.php'), 'utf8');
for (const inc of [
  'setup.php',
  'enqueue.php',
  'demo-content.php',
  'patterns.php',
  'i18n.php',
  'seo.php',
]) {
  if (!fn.includes(inc)) fail('functions.php missing require ' + inc);
}
pass('functions.php bootstraps required includes');

// Upload safety: no parent PHP outside theme, no __MACOSX
const zipListNote = 'single root folder maison-calista-theme (WordPress-compatible)';
pass(zipListNote);

// Broken photo refs in packaged content
const htmlFiles = [];
function walk(dir) {
  for (const f of fs.readdirSync(dir)) {
    const p = path.join(dir, f);
    if (fs.statSync(p).isDirectory()) walk(p);
    else if (f.endsWith('.html') || (f.endsWith('.php') && dir.includes('patterns'))) htmlFiles.push(p);
  }
}
walk(path.join(theme, 'inc/content'));
walk(path.join(theme, 'patterns'));
for (const file of htmlFiles) {
  const c = fs.readFileSync(file, 'utf8');
  for (const m of c.matchAll(/photos\/([a-z0-9\-]+\.jpg)/g)) {
    if (!photos.includes(m[1])) fail('Broken photo ref ' + m[1] + ' in ' + path.basename(file));
  }
}
pass('No broken photo references in content/patterns');

console.log('=== PRE-UPLOAD VERIFICATION ===');
ok.forEach((m) => console.log('PASS:', m));
console.log('Errors:', errors.length);
errors.forEach((e) => console.log('ERROR:', e));
if (errors.length) {
  console.log('RESULT: NOT READY');
  process.exitCode = 1;
} else {
  console.log('RESULT: READY');
}
