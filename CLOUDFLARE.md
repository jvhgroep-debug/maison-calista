# Cloudflare Workers — Maison Calista (Static Assets)

This project deploys as a **Worker with Static Assets only** (no Worker script).

## `wrangler.jsonc`

```jsonc
{
  "name": "maison-calista",
  "compatibility_date": "2026-07-12",
  "assets": {
    "directory": "./dist",
    "html_handling": "force-trailing-slash",
    "not_found_handling": "404-page"
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
