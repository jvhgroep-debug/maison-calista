const fs = require('fs');
const path = require('path');

const root = 'C:/Users/DELL/Downloads/Maison-Calista/maison-calista-theme';
function walk(dir, out = []) {
  for (const f of fs.readdirSync(dir)) {
    const p = path.join(dir, f);
    if (fs.statSync(p).isDirectory()) walk(p, out);
    else if (f.endsWith('.php')) out.push(p);
  }
  return out;
}

const files = walk(path.join(root, 'inc'));
let total = 0;
for (const p of files) {
  let c = fs.readFileSync(p, 'utf8');
  const matches = [...c.matchAll(/\\u\{([0-9A-Fa-f]+)\}/g)];
  if (!matches.length) continue;
  console.log(path.relative(root, p), matches.map((m) => m[0]).join(', '));
  const next = c.replace(/\\u\{([0-9A-Fa-f]+)\}/g, (_, hex) => {
    const cp = parseInt(hex, 16);
    if (cp >= 0xd800 && cp <= 0xdfff) {
      throw new Error('surrogate ' + hex);
    }
    return String.fromCodePoint(cp);
  });
  fs.writeFileSync(p, next, 'utf8');
  total += matches.length;
  console.log('  -> replaced', matches.length);
}
console.log('Total replacements:', total);

// Re-scan
for (const p of files) {
  const c = fs.readFileSync(p, 'utf8');
  if (/\\u\{/.test(c)) console.log('STILL HAS', path.relative(root, p));
}
