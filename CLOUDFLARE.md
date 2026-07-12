# Cloudflare Pages — Maison Calista

This repository deploys a **static** version of the Maison Calista website
(same content/look as the local Node preview). The WordPress theme in
`maison-calista-theme/` is for STRATO hosting and is **not** executed on Cloudflare Pages.

## Detected settings (use these in the Cloudflare dashboard)

| Setting | Value |
|--------|--------|
| **Git repository** | `jvhgroep-debug/maison-calista` |
| **Production branch** | `main` |
| **Root directory** | `/` (repository root — leave blank) |
| **Framework preset** | None / Direct Upload equivalent — **None** |
| **Build command** | `npm run build` |
| **Build output directory** | `site` |
| **Node.js version** | `20` (or 18+) |

## Optional Wrangler CLI deploy

```bash
npm run build
npx wrangler pages deploy site --project-name=maison-calista
```

Or: `npm run deploy` (requires `wrangler login` once).
