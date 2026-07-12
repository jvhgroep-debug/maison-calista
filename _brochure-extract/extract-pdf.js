/**
 * Extract embedded JPEGs and readable strings from local brochure PDF.
 * Local files only — no network.
 */
const fs = require('fs');
const path = require('path');

const pdfPath = process.argv[2];
const outDir = process.argv[3];
const imgDir = path.join(outDir, 'images');
const textDir = path.join(outDir, 'text');

fs.mkdirSync(imgDir, { recursive: true });
fs.mkdirSync(textDir, { recursive: true });

const buf = fs.readFileSync(pdfPath);
console.log('PDF bytes:', buf.length);

// Extract JPEG by SOI/EOI markers
const starts = [];
for (let i = 0; i < buf.length - 1; i++) {
  if (buf[i] === 0xff && buf[i + 1] === 0xd8) starts.push(i);
}
console.log('JPEG starts:', starts.length);

let extracted = 0;
const seen = new Set();
for (let j = 0; j < starts.length; j++) {
  const start = starts[j];
  const limit = j + 1 < starts.length ? starts[j + 1] : Math.min(start + 12 * 1024 * 1024, buf.length);
  let end = -1;
  for (let k = start + 2; k < limit - 1; k++) {
    if (buf[k] === 0xff && buf[k + 1] === 0xd9) {
      end = k + 2;
      break;
    }
  }
  if (end < 0) continue;
  const len = end - start;
  if (len < 12000) continue; // skip tiny
  const slice = buf.subarray(start, end);
  // dedupe by size+first bytes
  const key = `${len}:${slice[10]}:${slice[20]}:${slice[30]}`;
  if (seen.has(key)) continue;
  seen.add(key);
  const file = path.join(imgDir, `brochure-embed-${String(extracted).padStart(3, '0')}.jpg`);
  fs.writeFileSync(file, slice);
  extracted++;
}
console.log('Extracted unique JPEGs >=12KB:', extracted);

// Text-ish strings from Latin1 decode
const text = buf.toString('latin1');
const re = /\((?:\\.|[^\\)]){3,300}\)/g;
const found = new Set();
let m;
while ((m = re.exec(text)) !== null) {
  let s = m[0].slice(1, -1);
  s = s
    .replace(/\\n/g, ' ')
    .replace(/\\r/g, ' ')
    .replace(/\\t/g, ' ')
    .replace(/\\([()\\])/g, '$1');
  if (/[A-Za-zÀ-ÿ]{4,}/.test(s)) found.add(s.trim());
}

// Also try UTF-16BE style PDF strings often as <00410042...>
const hexRe = /<((?:[0-9A-Fa-f]{4}){3,120})>/g;
while ((m = hexRe.exec(text)) !== null) {
  const hex = m[1];
  let s = '';
  for (let i = 0; i < hex.length; i += 4) {
    const code = parseInt(hex.slice(i, i + 4), 16);
    if (code > 0) s += String.fromCharCode(code);
  }
  if (/[A-Za-zÀ-ÿ]{4,}/.test(s)) found.add(s.trim());
}

const lines = [...found];
fs.writeFileSync(path.join(textDir, 'pdf-strings.txt'), lines.join('\n'), 'utf8');
console.log('Text-like strings:', lines.length);
console.log('--- sample ---');
console.log(lines.slice(0, 80).join('\n'));
