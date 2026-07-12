const fs = require('fs');
const c = fs.readFileSync(
  'C:/Users/DELL/Downloads/Maison-Calista/maison-calista-theme/inc/template-tags.php',
  'utf8'
);
const cleaned = c.replace(/\\u\{[0-9A-Fa-f]+\}/g, 'U');
let depth = 0;
let line = 0;
const stack = [];
for (const L of cleaned.split(/\n/)) {
  line++;
  // also ignore braces inside single/double quotes roughly
  let inS = false,
    inD = false,
    esc = false;
  for (let i = 0; i < L.length; i++) {
    const ch = L[i];
    if (esc) {
      esc = false;
      continue;
    }
    if (ch === '\\') {
      esc = true;
      continue;
    }
    if (!inD && ch === "'") {
      inS = !inS;
      continue;
    }
    if (!inS && ch === '"') {
      inD = !inD;
      continue;
    }
    if (inS || inD) continue;
    if (ch === '{') {
      depth++;
      stack.push({ line, snip: L.trim().slice(0, 70) });
    }
    if (ch === '}') {
      depth--;
      stack.pop();
    }
  }
}
console.log('depth', depth);
console.log('unclosed', stack);
