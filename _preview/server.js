/**
 * Maison Calista — local static preview server (no WordPress required).
 * Same asset paths as production /dist (`/assets/...`).
 *
 * Usage: npm run preview
 * URL:   http://localhost:3000
 */
const http = require('http');
const fs = require('fs');
const path = require('path');
const { URL } = require('url');
const { THEME, buildPage, resolvePage } = require('./site-engine');

const PORT = Number(process.env.PORT) || 3000;
const ASSETS = path.join(THEME, 'assets');

const MIME = {
  '.html': 'text/html; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.js': 'application/javascript; charset=utf-8',
  '.json': 'application/json; charset=utf-8',
  '.svg': 'image/svg+xml',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.webp': 'image/webp',
  '.woff2': 'font/woff2',
  '.woff': 'font/woff',
  '.pdf': 'application/pdf',
  '.ico': 'image/x-icon',
};

function sendFile(res, filePath) {
  if (!fs.existsSync(filePath) || fs.statSync(filePath).isDirectory()) {
    res.writeHead(404).end('Not found');
    return;
  }
  const ext = path.extname(filePath).toLowerCase();
  res.writeHead(200, {
    'Content-Type': MIME[ext] || 'application/octet-stream',
    'Cache-Control': 'no-cache',
  });
  fs.createReadStream(filePath).pipe(res);
}

const server = http.createServer((req, res) => {
  try {
    const u = new URL(req.url, `http://localhost:${PORT}`);
    let pathname = decodeURIComponent(u.pathname);

    if (pathname.startsWith('/assets/')) {
      const rel = pathname.slice('/assets/'.length);
      const filePath = path.normalize(path.join(ASSETS, rel));
      if (!filePath.startsWith(ASSETS)) {
        res.writeHead(403).end('Forbidden');
        return;
      }
      sendFile(res, filePath);
      return;
    }

    // Legacy /theme/assets alias → /assets
    if (pathname.startsWith('/theme/assets/')) {
      const rel = pathname.slice('/theme/assets/'.length);
      sendFile(res, path.normalize(path.join(ASSETS, rel)));
      return;
    }

    const hit = resolvePage(pathname);
    if (!hit) {
      res.writeHead(404, { 'Content-Type': 'text/html; charset=utf-8' });
      res.end(`<!DOCTYPE html><html lang="fr"><body style="font-family:system-ui;padding:3rem;background:#F7F2EA">
        <h1>Page introuvable</h1>
        <p><a href="/">Retour à l’accueil</a></p>
      </body></html>`);
      return;
    }

    const html = buildPage(hit.page, hit.lang, { production: false });
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8', 'Cache-Control': 'no-cache' });
    res.end(html);
  } catch (err) {
    console.error(err);
    res.writeHead(500).end('Server error: ' + err.message);
  }
});

server.listen(PORT, '127.0.0.1', () => {
  console.log('');
  console.log('Maison Calista local preview');
  console.log('----------------------------');
  console.log(`URL:  http://localhost:${PORT}`);
  console.log(`EN:   http://localhost:${PORT}/en/`);
  console.log(`Assets: ${ASSETS}`);
  console.log('Press Ctrl+C to stop.');
  console.log('');
});
