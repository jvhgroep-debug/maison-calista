# Cloudflare Workers — Maison Calista (Static Assets)

This project deploys as a **Worker with Static Assets only** (no Worker script).

## `wrangler.jsonc`

Static assets from `./dist`, plus a tiny Worker that only forces
`Content-Type: application/xml` for `/sitemap.xml` (so Chrome shows the XML
tree instead of stripping tags as HTML).

```jsonc
{
  "name": "maison-calista",
  "compatibility_date": "2026-07-12",
  "main": "./workers/assets-headers.js",
  "assets": {
    "directory": "./dist",
    "binding": "ASSETS",
    "run_worker_first": ["/sitemap.xml"]
  }
}
```

## Build

```bash
npm run build   # → ./dist
```

## Dashboard / CI

| Setting | Value |
|--------|--------|
| Build command | `npm run build` |
| Deploy / assets directory | `./dist` (via `assets.directory` in wrangler.jsonc) |
| Node | `20` |

After the config is on `main`, open the failed deployment in Cloudflare and click **Retry deployment**.
