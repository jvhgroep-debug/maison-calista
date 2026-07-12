# Maison Calista

Standalone static website (French default + English) for **Cloudflare Pages**.

## Quick start

```bash
npm run build      # → production /dist
npm run preview    # → http://localhost:3000
```

## Deploy (Cloudflare Workers Static Assets)

- **Build command:** `npm run build`
- **Assets directory:** `./dist` (set in `wrangler.jsonc` → `assets.directory`)
- **No Worker script** — static files only
- **Node:** 20

See [CLOUDFLARE.md](./CLOUDFLARE.md).

## Source

Page HTML and media live under `maison-calista-theme/` (content + assets).
The build copies them into a clean static tree under `/dist`.
The large brochure PDF is excluded (Workers 25 MiB asset limit).
