/**
 * Replace placeholders and old photo paths with organised brochure photos.
 * Local project files only.
 */
const fs = require('fs');
const path = require('path');

const theme = 'C:/Users/DELL/Downloads/Maison-Calista/maison-calista-theme';
const photo = '%%THEME_URI%%/assets/images/photos';

const heroes = {
  'inc/content/home.html': ['maison-calista-restaurant-terrace-pool-dusk.jpg', 'Restaurant terrace at dusk overlooking the illuminated pool and Atlas Mountains'],
  'inc/content/about.html': ['maison-calista-living-dining-arched-window.jpg', 'Warm living and dining lounge at Maison Calista'],
  'inc/content/residence.html': ['maison-calista-pool-atlas-mountains-day.jpg', 'Pool terrace with Atlas Mountains view'],
  'inc/content/care.html': ['maison-calista-living-room-terrace-light.jpg', 'Calm living space filled with natural light'],
  'inc/content/family.html': ['maison-calista-living-dining-garden-view.jpg', 'Shared living space welcoming family visits'],
  'inc/content/activities.html': ['maison-calista-desert-lounge-sunset.jpg', 'Desert lounge experience at sunset'],
  'inc/content/restaurant.html': ['maison-calista-restaurant-pool-axis-day.jpg', 'Restaurant terrace looking toward the pool and Atlas'],
  'inc/content/pricing.html': ['maison-calista-residence-aerial-atlas.jpg', 'Aerial view of Maison Calista near Marrakech'],
  'inc/content/fr/home.html': ['maison-calista-restaurant-terrace-pool-dusk.jpg', 'Terrasse restaurant au crépuscule avec vue sur la piscine et l Atlas'],
  'inc/content/fr/about.html': ['maison-calista-living-dining-arched-window.jpg', 'Salon lumineux de Maison Calista'],
  'inc/content/fr/residence.html': ['maison-calista-pool-atlas-mountains-day.jpg', 'Terrasse piscine avec vue sur l Atlas'],
  'inc/content/fr/care.html': ['maison-calista-living-room-terrace-light.jpg', 'Espace de vie calme et lumineux'],
  'inc/content/fr/family.html': ['maison-calista-living-dining-garden-view.jpg', 'Espace de vie pour les visites familiales'],
  'inc/content/fr/activities.html': ['maison-calista-desert-lounge-sunset.jpg', 'Lounge desert au coucher du soleil'],
  'inc/content/fr/restaurant.html': ['maison-calista-restaurant-pool-axis-day.jpg', 'Terrasse restaurant face a la piscine'],
  'inc/content/fr/pricing.html': ['maison-calista-residence-aerial-atlas.jpg', 'Vue aerienne de Maison Calista'],
  'inc/content/fr/care.html': ['maison-calista-living-room-terrace-light.jpg', 'Espace de vie calme'],
};

function heroHtml(file, alt) {
  return `<img src="${photo}/${file}" alt="${alt}" width="1920" height="1080" loading="eager" decoding="async" />`;
}

function walk(dir, files = []) {
  for (const f of fs.readdirSync(dir)) {
    const p = path.join(dir, f);
    const st = fs.statSync(p);
    if (st.isDirectory()) walk(p, files);
    else if (/\.(html|php)$/.test(f)) files.push(p);
  }
  return files;
}

// Path replacements from old names / placeholders to new SEO names
const pathRepl = [
  [/maison-calista-hero-evening\.jpg/g, 'maison-calista-restaurant-terrace-pool-dusk.jpg'],
  [/maison-calista-residence-aerial\.jpg/g, 'maison-calista-residence-aerial-sunset.jpg'],
  [/maison-calista-residence-dusk\.jpg/g, 'maison-calista-residence-aerial-atlas.jpg'],
  [/maison-calista-residence-pool\.jpg/g, 'maison-calista-pool-atlas-mountains-day.jpg'],
  [/maison-calista-private-room\.jpg/g, 'maison-calista-twin-bedroom-bohemian.jpg'],
  [/maison-calista-living-dining\.jpg/g, 'maison-calista-living-dining-arched-window.jpg'],
  [/maison-calista-restaurant-evening\.jpg/g, 'maison-calista-restaurant-lounge-evening.jpg'],
  [/maison-calista-restaurant-day\.jpg/g, 'maison-calista-restaurant-terrace-day-atlas.jpg'],
  [/maison-calista-atlas-view\.jpg/g, 'maison-calista-residence-aerial-atlas.jpg'],
  [/maison-calista-gardens\.jpg/g, 'maison-calista-garden-courtyard.jpg'],
  [/maison-calista-garden-path\.jpg/g, 'maison-calista-garden-courtyard.jpg'],
  [/maison-calista-poolside\.jpg/g, 'maison-calista-pool-atlas-mountains-day.jpg'],
  [/maison-calista-pool-day\.jpg/g, 'maison-calista-pool-atlas-mountains-day.jpg'],
  [/maison-calista-courtyard\.jpg/g, 'maison-calista-garden-courtyard.jpg'],
  [/maison-calista-terrace\.jpg/g, 'maison-calista-terrace-details.jpg'],
  [/maison-calista-interior-lounge\.jpg/g, 'maison-calista-living-mashrabiya-evening.jpg'],
  [/maison-calista-interior-arch\.jpg/g, 'maison-calista-living-room-terrace-light.jpg'],
  [/maison-calista-room-detail\.jpg/g, 'maison-calista-suite-bedroom-evening.jpg'],
  [/maison-calista-suite-terrace\.jpg/g, 'maison-calista-private-patio-plunge-dusk.jpg'],
  [/maison-calista-family-moments\.jpg/g, 'maison-calista-living-dining-garden-view.jpg'],
  [/maison-calista-details\.jpg/g, 'maison-calista-terrace-details.jpg'],
  [/assets\/images\/placeholders\/[^"'\s>]+\.svg/g, 'assets/images/photos/maison-calista-pool-atlas-mountains-day.jpg'],
];

let updated = 0;
for (const file of walk(theme)) {
  let c = fs.readFileSync(file, 'utf8');
  const orig = c;

  // Replace hero placeholders
  const rel = path.relative(theme, file).replace(/\\/g, '/');
  if (heroes[rel]) {
    const [img, alt] = heroes[rel];
    c = c.replace(/<div class="mc-placeholder mc-placeholder--hero"[^>]*>[\s\S]*?<\/div>/, heroHtml(img, alt));
  }

  for (const [re, to] of pathRepl) c = c.replace(re, to);

  // Generic remaining mc-placeholder blocks used as media (not maps note)
  c = c.replace(
    /<div class="mc-placeholder"(?! mc-placeholder--hero)[^>]*>[\s\S]*?<\/div>/g,
    (m) => {
      if (m.includes('Map') || m.includes('Carte') || m.includes('Google Maps') || m.includes('mc-maps')) return m;
      return `<img src="${photo}/maison-calista-living-dining-arched-window.jpg" alt="Maison Calista residence interior" loading="lazy" width="1200" height="900" />`;
    }
  );

  if (c !== orig) {
    fs.writeFileSync(file, c);
    updated++;
    console.log('updated', rel);
  }
}

console.log('Files updated:', updated);
