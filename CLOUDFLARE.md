# Cloudflare Pages — Maison Calista (static site)

Production output is the **`dist/`** folder built by `npm run build`.

## Cloudflare dashboard settings

| Setting | Value |
|--------|--------|
| **Git repository** | `jvhgroep-debug/maison-calista` |
| **Production branch** | `main` |
| **Root directory** | *(empty)* |
| **Framework preset** | None |
| **Build command** | `npm run build` |
| **Build output directory** | `dist` |
| **Node.js version** | `20` (`NODE_VERSION=20`) |

## Local

```bash
npm run build      # writes /dist
npm run preview    # http://localhost:3000
npm run deploy     # wrangler pages deploy dist
```
