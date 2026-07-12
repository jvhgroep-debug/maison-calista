# Maison Calista

Standalone static website (French default + English) for **Cloudflare Pages**.

## Quick start

```bash
npm run build      # → production /dist
npm run preview    # → http://localhost:3000
```

## Deploy (Cloudflare Pages)

- **Build command:** `npm run build`
- **Output directory:** `dist`
- **Root directory:** *(empty)*
- **Node:** 20

See [CLOUDFLARE.md](./CLOUDFLARE.md).

## Source

Page HTML and media live under `maison-calista-theme/` (content + assets).  
The build copies them into a clean static tree under `/dist` — no WordPress runtime required.
