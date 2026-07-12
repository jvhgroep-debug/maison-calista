/**
 * Run STRATO-like WordPress activation test via Playground CLI.
 * PHP 8.2 + WordPress 6.7 (current STRATO-compatible stack).
 */
const { spawn } = require('child_process');
const path = require('path');
const fs = require('fs');

const ROOT = path.join(__dirname, '..');
const THEME = path.join(ROOT, 'maison-calista-theme');
const BLUEPRINT = path.join(__dirname, 'activation-blueprint.json');
const LOG = path.join(ROOT, 'dist', 'activation-test-log.txt');
const PORT = 9407;

fs.mkdirSync(path.join(ROOT, 'dist'), { recursive: true });

const mount = `${THEME}:/wordpress/wp-content/themes/maison-calista-theme`;
const args = [
  '--yes',
  '@wp-playground/cli@latest',
  'server',
  `--php=8.2`,
  `--wp=6.7`,
  `--port=${PORT}`,
  `--blueprint=${BLUEPRINT}`,
  `--mount=${mount}`,
  '--login',
  '--verbosity=normal',
];

console.log('Starting Playground activation test...');
console.log('PHP 8.2 · WordPress 6.7 · theme mount + Polylang/Yoast/Fluent Forms');

const child = spawn('npx', args, {
  cwd: ROOT,
  shell: true,
  stdio: ['ignore', 'pipe', 'pipe'],
});

let buf = '';
let settled = false;

function finish(code) {
  if (settled) return;
  settled = true;
  fs.writeFileSync(LOG, buf, 'utf8');
  try {
    child.kill('SIGTERM');
  } catch (_) {}
  process.exit(code);
}

function consider(chunk) {
  buf += chunk.toString();
  process.stdout.write(chunk);
  if (buf.includes('RESULT=PASS')) {
    console.log('\n=== ACTIVATION TEST PASSED ===');
    finish(0);
  }
  if (buf.includes('RESULT=FAIL') || buf.includes('ERR_COUNT=') && /ERROR:/.test(buf)) {
    // wait a moment for full output
  }
  if (/RESULT=FAIL/.test(buf)) {
    console.log('\n=== ACTIVATION TEST FAILED ===');
    finish(1);
  }
}

child.stdout.on('data', consider);
child.stderr.on('data', consider);

setTimeout(() => {
  // After blueprint, hit runPHP endpoint isn't automatic for server mode.
  // Blueprint steps run on boot — look for PASS/FAIL in logs.
  // Also try HTTP fetch of a custom endpoint if we added one.
  if (!settled) {
    // Give blueprint more time; if theme activated without RESULT, probe admin
    fetch(`http://127.0.0.1:${PORT}/`).then(async (r) => {
      const t = await r.text();
      buf += '\nHTTP_HOME_STATUS=' + r.status + '\n';
      buf += 'HOME_HAS_CALISTA=' + (t.includes('Maison Calista') || t.includes('maison-calista')) + '\n';
      // Request a mu-plugin style check via ?mc_activation_test=1 if not present
    }).catch(() => {}).finally(() => {
      // If blueprint runPHP printed already, settled. Else fail timeout soon.
    });
  }
}, 90000);

setTimeout(() => {
  if (!settled) {
    console.log('\nTIMEOUT — incomplete activation test');
    console.log('See', LOG);
    finish(2);
  }
}, 300000);
