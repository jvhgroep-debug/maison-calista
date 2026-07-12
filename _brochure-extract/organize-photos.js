/**
 * Organize Maison Calista project images — local files only.
 */
const fs = require('fs');
const path = require('path');

const root = 'C:/Users/DELL/Downloads/Maison-Calista';
const theme = path.join(root, 'maison-calista-theme');
const srcDir = path.join(theme, 'assets/images/source-whatsapp');
const photosDir = path.join(theme, 'assets/images/photos');
const brochureDir = path.join(theme, 'assets/brochure');

fs.mkdirSync(photosDir, { recursive: true });
fs.mkdirSync(path.join(photosDir, 'gallery'), { recursive: true });
fs.mkdirSync(brochureDir, { recursive: true });

// Definitive SEO names from visual audit of each WhatsApp file
const map = {
  'WhatsApp Image 2026-07-11 at 2.jpeg': {
    name: 'maison-calista-restaurant-terrace-pool-dusk.jpg',
    alt: 'Restaurant terrace at dusk overlooking the illuminated pool and Atlas Mountains',
    cats: ['restaurant', 'pool', 'residence'],
  },
  'WhatsApp Image 2026-07-11 at 3.jpeg': {
    name: 'maison-calista-restaurant-lounge-evening.jpg',
    alt: 'Candlelit restaurant lounge overlooking the pool at twilight',
    cats: ['restaurant'],
  },
  'WhatsApp Image 2026-07-11 at 4.jpeg': {
    name: 'maison-calista-restaurant-day-pool-view.jpg',
    alt: 'Daytime outdoor restaurant with woven lamps and pool view',
    cats: ['restaurant', 'pool'],
  },
  'WhatsApp Image 2026-07-11 at 5.jpeg': {
    name: 'maison-calista-residence-aerial-sunset.jpg',
    alt: 'Aerial view of Maison Calista at sunset with glowing pool near Marrakech',
    cats: ['residence', 'pool', 'marrakech'],
  },
  'WhatsApp Image 2026-07-11 at 6.jpeg': {
    name: 'maison-calista-residence-aerial-atlas.jpg',
    alt: 'Aerial view of the residence oasis with snow-capped Atlas Mountains',
    cats: ['residence', 'atlas', 'marrakech'],
  },
  'WhatsApp Image 2026-07-11 at 7.jpeg': {
    name: 'maison-calista-twin-bedroom-bohemian.jpg',
    alt: 'Bright twin bedroom with woven pendants and sculpted relief wall',
    cats: ['rooms'],
  },
  'WhatsApp Image 2026-07-11 at 8.jpeg': {
    name: 'maison-calista-studio-living-kitchen.jpg',
    alt: 'Open-plan studio with arched niches, dining table and kitchenette',
    cats: ['rooms'],
  },
  'WhatsApp Image 2026-07-11 at 9.jpeg': {
    name: 'maison-calista-suite-bedroom-evening.jpg',
    alt: 'Serene suite bedroom with woven pendants and warm cove lighting',
    cats: ['rooms'],
  },
  'WhatsApp Image 2026-07-11 at 10.jpeg': {
    name: 'maison-calista-living-dining-arched-window.jpg',
    alt: 'Living and dining lounge with arched wooden window and rattan lamps',
    cats: ['rooms', 'family'],
  },
  'WhatsApp Image 2026-07-11 at 11.jpeg': {
    name: 'maison-calista-living-room-terrace-light.jpg',
    alt: 'Mediterranean living room opening onto a sunny private terrace',
    cats: ['rooms', 'family'],
  },
  'WhatsApp Image 2026-07-11 at 12.jpeg': {
    name: 'maison-calista-living-mashrabiya-evening.jpg',
    alt: 'Evening living space with mashrabiya panel and arched glass doors',
    cats: ['rooms'],
  },
  'WhatsApp Image 2026-07-11 at 13.jpeg': {
    name: 'maison-calista-bedroom-moroccan-lanterns.jpg',
    alt: 'Luxury bedroom with Moorish alcove and golden Moroccan lanterns',
    cats: ['rooms'],
  },
  'WhatsApp Image 2026-07-11 at 14.jpeg': {
    name: 'maison-calista-terrace-details.jpg',
    alt: 'Terrace seating details at Maison Calista',
    cats: ['gardens', 'activities'],
  },
  'WhatsApp Image 2026-07-11 at 14.01.05.jpeg': {
    name: 'maison-calista-restaurant-terrace-day-atlas.jpg',
    alt: 'Restaurant terrace by day overlooking pool, palms and Atlas Mountains',
    cats: ['restaurant', 'pool', 'atlas'],
  },
  'WhatsApp Image 2026-07-11 at 15.jpeg': {
    name: 'maison-calista-restaurant-pool-axis-day.jpg',
    alt: 'Symmetrical view from restaurant through the pool toward the Atlas',
    cats: ['restaurant', 'pool', 'residence'],
  },
  'WhatsApp Image 2026-07-11 at 16.jpeg': {
    name: 'maison-calista-living-dining-garden-view.jpg',
    alt: 'Living and dining room with dark wood beams opening to gardens',
    cats: ['rooms', 'family'],
  },
  'WhatsApp Image 2026-07-11 at 17.jpeg': {
    name: 'maison-calista-restaurant-terrace-night.jpg',
    alt: 'Night dining terrace with candlelight and illuminated pool',
    cats: ['restaurant', 'pool'],
  },
  'WhatsApp Image 2026-07-11 at 18.jpeg': {
    name: 'maison-calista-desert-lounge-sunset.jpg',
    alt: 'Desert lounge under a stretch tent with fire pit at sunset',
    cats: ['activities', 'marrakech'],
  },
  'WhatsApp Image 2026-07-11 at 19.jpeg': {
    name: 'maison-calista-private-patio-plunge-dusk.jpg',
    alt: 'Private patio with plunge pool and villa exterior at dusk',
    cats: ['rooms', 'gardens'],
  },
  'WhatsApp Image 2026-07-11 at 20.jpeg': {
    name: 'maison-calista-garden-courtyard.jpg',
    alt: 'Garden courtyard landscaping at Maison Calista',
    cats: ['gardens'],
  },
  'WhatsApp Image 2026-07-11 at 21.jpeg': {
    name: 'maison-calista-pool-atlas-mountains-day.jpg',
    alt: 'Pool terrace with palms, bougainvillea and snow-capped Atlas Mountains',
    cats: ['pool', 'atlas', 'residence'],
  },
};

// Clean previous photos (keep README if present)
for (const f of fs.readdirSync(photosDir)) {
  const p = path.join(photosDir, f);
  if (f === 'gallery' || f === 'README.txt') continue;
  if (fs.statSync(p).isFile()) fs.unlinkSync(p);
}
const galDir = path.join(photosDir, 'gallery');
if (fs.existsSync(galDir)) {
  for (const f of fs.readdirSync(galDir)) {
    fs.unlinkSync(path.join(galDir, f));
  }
}

const manifest = [];
for (const [srcName, meta] of Object.entries(map)) {
  const src = path.join(srcDir, srcName);
  if (!fs.existsSync(src)) {
    console.warn('MISSING source:', srcName);
    continue;
  }
  const dest = path.join(photosDir, meta.name);
  fs.copyFileSync(src, dest);
  fs.copyFileSync(src, path.join(galDir, meta.name));
  manifest.push({ source: srcName, file: meta.name, alt: meta.alt, categories: meta.cats });
  console.log('OK', meta.name);
}

fs.writeFileSync(path.join(photosDir, 'manifest.json'), JSON.stringify(manifest, null, 2));
fs.writeFileSync(
  path.join(photosDir, 'README.txt'),
  `Maison Calista photography
All images sourced from project WhatsApp exports (brochure photography).
Original filenames preserved in ../source-whatsapp/
Organised SEO filenames listed in manifest.json
Do not replace with external stock imagery.
`
);

// Copy brochure PDF into theme for handover
const pdfSrc = path.join(root, 'folder Maison Calista.pdf');
if (fs.existsSync(pdfSrc)) {
  fs.copyFileSync(pdfSrc, path.join(brochureDir, 'maison-calista-brochure.pdf'));
  console.log('Brochure PDF copied to assets/brochure/');
}

console.log('Organised', manifest.length, 'photos');
