/**
 * Build installable WordPress theme ZIP (clean package).
 * Output: Maison-Calista/dist/maison-calista-theme-1.3.0.zip
 */
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const ROOT = path.join(__dirname, '..');
const THEME = path.join(ROOT, 'maison-calista-theme');
const DIST = path.join(ROOT, 'dist');
const STAGE = path.join(DIST, 'maison-calista-theme');
const VERSION = '1.3.4';
const ZIP_NAME = `maison-calista-theme-${VERSION}.zip`;
const ZIP_PATH = path.join(DIST, ZIP_NAME);

const EXCLUDE_DIR_NAMES = new Set([
  'source-whatsapp',
  'gallery', // duplicate thumbs under photos/gallery
  'scripts',
  'node_modules',
  '.git',
]);

const EXCLUDE_FILES = new Set([
  'SCREENSHOT-README.txt',
  'screenshot-source.svg',
  '.DS_Store',
  'Thumbs.db',
]);

function shouldSkip(relPosix, isDir) {
  const parts = relPosix.split('/');
  if (parts.some((p) => EXCLUDE_DIR_NAMES.has(p))) return true;
  if (!isDir && EXCLUDE_FILES.has(path.basename(relPosix))) return true;
  // Skip nested photos/gallery only
  if (relPosix.includes('assets/images/photos/gallery')) return true;
  if (relPosix.includes('assets/images/source-whatsapp')) return true;
  return false;
}

function copyTree(src, dest, base = src) {
  fs.mkdirSync(dest, { recursive: true });
  for (const name of fs.readdirSync(src)) {
    const from = path.join(src, name);
    const to = path.join(dest, name);
    const rel = path.relative(base, from).replace(/\\/g, '/');
    const st = fs.statSync(from);
    if (shouldSkip(rel, st.isDirectory())) continue;
    if (st.isDirectory()) copyTree(from, to, base);
    else fs.copyFileSync(from, to);
  }
}

function rimraf(p) {
  if (!fs.existsSync(p)) return;
  fs.rmSync(p, { recursive: true, force: true });
}

console.log('Packaging Maison Calista', VERSION);
rimraf(STAGE);
fs.mkdirSync(DIST, { recursive: true });
copyTree(THEME, STAGE);

// Sanity required files
const required = [
  'style.css', 'functions.php', 'theme.json', 'screenshot.png',
  'INSTALL.md', 'HANDOVER.md', 'languages/fr_FR.mo',
  'assets/css/main.css', 'assets/fonts/source-sans-3-latin.woff2',
  'assets/fonts/cormorant-garamond-latin.woff2',
  'assets/images/logo/maison-calista-logo.svg',
  'assets/brochure/maison-calista-brochure.pdf',
];
for (const f of required) {
  if (!fs.existsSync(path.join(STAGE, f))) {
    console.error('Missing in package:', f);
    process.exit(1);
  }
}

const photos = fs.readdirSync(path.join(STAGE, 'assets/images/photos')).filter((f) => f.endsWith('.jpg'));
if (photos.length < 21) {
  console.error('Expected 21 photos, got', photos.length);
  process.exit(1);
}

if (fs.existsSync(ZIP_PATH)) fs.unlinkSync(ZIP_PATH);

// Prefer tar if available (creates proper zip); else PowerShell Compress-Archive
try {
  execSync(`tar -a -c -f "${ZIP_PATH}" -C "${DIST}" maison-calista-theme`, { stdio: 'inherit' });
} catch {
  const ps = `Compress-Archive -Path "${STAGE}" -DestinationPath "${ZIP_PATH}" -Force`;
  execSync(`powershell -NoProfile -Command "${ps.replace(/"/g, '\\"')}"`, { stdio: 'inherit', shell: true });
}

const size = fs.statSync(ZIP_PATH).size;
console.log('Created:', ZIP_PATH);
console.log('Size:', (size / (1024 * 1024)).toFixed(2), 'MB');
console.log('Photos packaged:', photos.length);
console.log('DONE');
