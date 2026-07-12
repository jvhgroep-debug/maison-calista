const fs = require('fs');
const path = require('path');

const dir = 'C:/Users/DELL/Downloads/Maison-Calista/_brochure-extract/images';
const files = fs.readdirSync(dir).filter(f => f.endsWith('.jpg'));
let ok = 0, bad = 0;
const good = [];
for (const f of files) {
  const buf = fs.readFileSync(path.join(dir, f));
  // basic JPEG SOF0/SOF2 dimension parse
  let w = 0, h = 0;
  for (let i = 2; i < buf.length - 9; ) {
    if (buf[i] !== 0xff) { i++; continue; }
    const marker = buf[i + 1];
    if (marker === 0xd9) break;
    if (marker >= 0xc0 && marker <= 0xcf && marker !== 0xc4 && marker !== 0xc8 && marker !== 0xcc) {
      h = (buf[i + 5] << 8) + buf[i + 6];
      w = (buf[i + 7] << 8) + buf[i + 8];
      break;
    }
    const len = (buf[i + 2] << 8) + buf[i + 3];
    i += 2 + len;
  }
  if (w >= 200 && h >= 200) {
    ok++;
    good.push({ f, w, h, kb: Math.round(buf.length / 1024) });
  } else bad++;
}
good.sort((a, b) => b.kb - a.kb);
console.log('Valid:', ok, 'Invalid/corrupt:', bad);
console.log(good.slice(0, 30));
fs.writeFileSync(
  'C:/Users/DELL/Downloads/Maison-Calista/_brochure-extract/text/valid-embeds.json',
  JSON.stringify(good, null, 2)
);
