/**
 * Quick brace/balance check for theme PHP files.
 */
const fs = require('fs');
const path = require('path');
const theme = path.join(__dirname, '..', 'maison-calista-theme');

function strip(c) {
  return c
    .replace(/\/\*[\s\S]*?\*\//g, '')
    .replace(/\/\/.*$/gm, '')
    .replace(/'(?:\\.|[^'\\])*'/g, "''")
    .replace(/"(?:\\.|[^"\\])*"/g, '""');
}

let bad = 0;
function walk(dir) {
  for (const f of fs.readdirSync(dir)) {
    const p = path.join(dir, f);
    if (fs.statSync(p).isDirectory()) walk(p);
    else if (f.endsWith('.php')) {
      const s = strip(fs.readFileSync(p, 'utf8'));
      const o = (s.match(/\{/g) || []).length;
      const c = (s.match(/\}/g) || []).length;
      if (o !== c) {
        console.log('MISMATCH', path.relative(theme, p), o, c);
        bad++;
      }
    }
  }
}
walk(theme);
console.log(bad ? 'FAIL' : 'PHP braces OK');
process.exitCode = bad ? 1 : 0;
