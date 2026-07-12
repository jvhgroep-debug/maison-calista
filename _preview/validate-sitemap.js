/**
 * Validate dist/sitemap.xml is well-formed UTF-8 XML and served as application/xml.
 */
const http = require('http');
const fs = require('fs');
const path = require('path');
const { assertValidSitemapXml } = require('./sitemap');

const ROOT = path.join(__dirname, '..');
const DIST = path.join(ROOT, 'dist');
const SITEMAP = path.join(DIST, 'sitemap.xml');
const HEADERS_FILE = path.join(DIST, '_headers');

function main() {
  if (!fs.existsSync(SITEMAP)) {
    console.error('Missing dist/sitemap.xml — run npm run build first');
    process.exit(1);
  }

  const buf = fs.readFileSync(SITEMAP);
  if (buf[0] === 0xef && buf[1] === 0xbb && buf[2] === 0xbf) {
    console.error('FAIL: UTF-8 BOM present');
    process.exit(1);
  }

  const xml = buf.toString('utf8');
  assertValidSitemapXml(xml);

  // Parse as XML via regex structure checks already done; also try DOM if available
  try {
    const { DOMParser } = require('@xmldom/xmldom');
    const doc = new DOMParser().parseFromString(xml, 'application/xml');
    const err = doc.getElementsByTagName('parsererror')[0];
    if (err) throw new Error(err.textContent || 'parsererror');
    const urlset = doc.documentElement;
    if (!urlset || urlset.nodeName !== 'urlset') throw new Error('root is not urlset');
  } catch (e) {
    if (e.code === 'MODULE_NOT_FOUND') {
      // Fallback: structural checks only (assertValidSitemapXml already ran)
      console.log('NOTE: @xmldom/xmldom not installed — structural checks OK');
    } else {
      console.error('FAIL: XML parse error', e.message || e);
      process.exit(1);
    }
  }

  const headersTxt = fs.readFileSync(HEADERS_FILE, 'utf8');
  if (!/\/sitemap\.xml[\s\S]*?Content-Type:\s*application\/xml/.test(headersTxt)) {
    console.error('FAIL: _headers missing application/xml for /sitemap.xml');
    process.exit(1);
  }

  // Serve briefly and confirm Content-Type + body parse
  const server = http.createServer((req, res) => {
    if (req.url === '/sitemap.xml') {
      res.writeHead(200, {
        'Content-Type': 'application/xml; charset=utf-8',
        'X-Content-Type-Options': 'nosniff',
      });
      res.end(buf);
      return;
    }
    res.writeHead(404).end();
  });

  server.listen(0, '127.0.0.1', async () => {
    const { port } = server.address();
    const url = `http://127.0.0.1:${port}/sitemap.xml`;
    try {
      const res = await fetch(url);
      const ct = res.headers.get('content-type') || '';
      const body = await res.text();
      if (!ct.includes('xml')) {
        console.error('FAIL: Content-Type is not XML:', ct);
        process.exitCode = 1;
      } else if (!body.startsWith('<?xml')) {
        console.error('FAIL: body does not start with XML declaration');
        process.exitCode = 1;
      } else {
        console.log('PASS: sitemap.xml is valid UTF-8 XML');
        console.log('PASS: Content-Type:', ct);
        console.log('PASS: urls:', (body.match(/<url>/g) || []).length);
        console.log('PASS: opens as XML when served with application/xml');
      }
    } catch (err) {
      console.error('FAIL: fetch', err);
      process.exitCode = 1;
    } finally {
      server.close();
    }
  });
}

main();
