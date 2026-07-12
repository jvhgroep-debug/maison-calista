const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');

const base = 'C:/Users/DELL/Downloads/Maison-Calista/_brochure-extract';
const strings = fs.readFileSync(path.join(base, 'text/pdf-strings.txt'), 'utf8').split(/\r?\n/);

const keywords = [
  'Calista','Marrakech','chambre','mois','Discovery','Signature','Atlas','piscine',
  'restaurant','famille','Lato','Cormorant','bien','forfait','1899','2599','3799',
  'Maroc','Morocco','chambres','Well','Comfort','cuisine','Marocaine','Française',
  'Italienne','attente','jardin','terrasse','accompagnement','résidence','residence'
];

const hits = {};
for (const k of keywords) hits[k] = [];

const readable = [];
for (const line of strings) {
  const t = line.trim();
  if (t.length < 6 || t.length > 180) continue;
  if (/endstream|endobj|DeviceCMYK|ColorSpace|Adobe Illustrator|Filter\b|Length\b/.test(t)) continue;
  if (!/[A-Za-zÀ-ÿ]{4,}/.test(t)) continue;
  // mostly letters/spaces/punctuation
  const letters = (t.match(/[A-Za-zÀ-ÿ0-9€\s.,;:!?'’"“”\-/–—%]/g) || []).join('');
  if (letters.length / t.length < 0.85) continue;
  if ((t.match(/[aeiouyAEIOUYÀàÂâÉéÈèÊêËëÎîÏïÔôÙùÛû]/g) || []).length < 2) continue;
  readable.push(t);
  for (const k of keywords) {
    if (t.toLowerCase().includes(k.toLowerCase()) && hits[k].length < 8) hits[k].push(t);
  }
}

fs.writeFileSync(path.join(base, 'text/readable.txt'), [...new Set(readable)].join('\n'), 'utf8');
fs.writeFileSync(path.join(base, 'text/keyword-hits.json'), JSON.stringify(hits, null, 2), 'utf8');
console.log('Readable:', [...new Set(readable)].length);
console.log('Sample readable:');
console.log([...new Set(readable)].slice(0, 80).join('\n'));
console.log('\nKeyword hits summary:');
for (const [k, v] of Object.entries(hits)) {
  if (v.length) console.log(k + ':', v.length, '|', v[0].slice(0, 100));
}

// List image files sorted by size
const imgs = fs.readdirSync(path.join(base, 'images'))
  .filter(f => f.endsWith('.jpg'))
  .map(f => {
    const p = path.join(base, 'images', f);
    return { f, size: fs.statSync(p).size };
  })
  .sort((a, b) => b.size - a.size);
console.log('\nTop 25 extracted images by size:');
imgs.slice(0, 25).forEach(i => console.log((i.size/1024).toFixed(0) + 'KB', i.f));
console.log('Total extracted jpgs:', imgs.length);
