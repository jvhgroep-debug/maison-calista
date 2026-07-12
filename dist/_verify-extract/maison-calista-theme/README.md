# Maison Calista WordPress Theme

Custom Full Site Editing (Gutenberg) block theme for **Maison Calista** — exclusive boutique residence near Marrakech, Morocco.

Warm cream & gold palette · elegant typography · brochure-based content · **French default** + full English translation · **v1.3.0**.

**Install:** upload `dist/maison-calista-theme-1.3.0.zip` or see `INSTALL.md`.

---

## Quick install

1. Copy `maison-calista-theme` into `wp-content/themes/`.
2. Activate **Maison Calista** under **Appearance → Themes**.
3. Click **Run Maison Calista Setup** (admin notice) — creates all pages, primary/footer menus, and sets the homepage.
4. Open **Appearance → Customize → Maison Calista Settings** and set WhatsApp, Maps embed, Fluent Forms ID, social links, and prices.
5. Install recommended plugins (below).

No Elementor. Native blocks + theme patterns only.

---

## What’s included

| Area | Details |
|------|---------|
| Brochure | `assets/brochure/maison-calista-brochure.pdf` (French source) |
| Pages | Accueil, À propos, Résidence, Accompagnement, Famille, Activités, Restaurant, Séjours & tarifs, Galerie, Contact, Confidentialité, Cookies, 404 |
| Patterns | 17 reusable block patterns (French primary) |
| Photos | 21 SEO-named JPEGs in `assets/images/photos/` (from project WhatsApp / brochure imagery). See `manifest.json` |
| Fonts | Source Sans 3 locally (matches brochure sans: Open Sans / Lato / Myriad) |
| i18n | **Français (default)** + English translation; switcher top-right |
| SEO | Meta/OG/canonical fallbacks + JSON-LD + hreflang, Yoast-compatible |

---

## Recommended plugins

- **Yoast SEO** — titles, meta, social
- **Fluent Forms** — contact / waiting list
- **Polylang** (or WPML) — Français (default) + English
- **LiteSpeed Cache** — performance
- **Wordfence** — security

---

## Theme structure

```
maison-calista-theme/
├── assets/css|js|fonts|icons|images/
├── inc/                 # setup, enqueue, SEO, schema, customizer, demo-content…
├── inc/content/         # French page HTML (primary)
├── inc/content/en/      # English translations
├── inc/content/fr/      # French mirror (fallback)
├── languages/           # .pot + fr_FR.po
├── parts/               # header, footer
├── patterns/            # Gutenberg patterns
├── styles/              # style variations
├── templates/           # FSE templates
├── theme.json
├── functions.php
├── style.css
├── screenshot.png
├── README.md
└── HANDOVER.md
```

---

## Customizer settings

**Appearance → Customize → Maison Calista Settings**

- Contact email
- WhatsApp number (international digits)
- Google Maps embed URL + public link
- Fluent Forms form ID
- Social URLs
- Package prices (Discovery / Well-Being / Comfort / Signature)

---

## Shortcodes

| Shortcode | Purpose |
|-----------|---------|
| `[mc_language_switcher]` | 🇫🇷 Français / 🇬🇧 English (Polylang/WPML aware; FR first) |
| `[mc_last_updated]` | Localized “Dernière mise à jour” / “Last updated” |
| `[mc_year]` | Current year |
| `[mc_contact_form]` | Fluent Forms or fallback form |
| `[mc_whatsapp]` | WhatsApp button — or **To be confirmed** if empty |
| `[mc_maps]` | Maps embed — or disabled placeholder if empty |
| `[mc_location]` | Location label (Customizer) |
| `[mc_social_links]` | Footer social links (hidden when empty) |
| `[mc_legal_review_notice]` | Legal review banner (toggle in Customizer) |
| `[mc_site_logo]` | Site Identity logo or official theme SVG |
| `[mc_price key="…"]` | Package price from Customizer |

---

## Translations (French default)

1. Site language should be **Français (`fr_FR`)**. Setup sets `WPLANG` when empty.
2. Install **Polylang**; set French as default language, then create English translations of each page.
3. After setup, English HTML is stored in post meta `_maison_calista_content_en` (and title in `_maison_calista_title_en`). Paste into Polylang EN pages.
4. Source files: French in `inc/content/*.html`, English in `inc/content/en/`.
5. Compile MO if needed: `msgfmt languages/fr_FR.po -o languages/fr_FR.mo` (or Loco Translate).

Language switcher (header top-right): **🇫🇷 Français** (default) · **🇬🇧 English**.

---

## Replacing images / colours / prices

- **Images:** replace files in `assets/images/photos/` (keep filenames) or edit pages in the block editor.
- **Colours:** edit `theme.json` palette (`cream`, `gold`, `sand`, `charcoal`…).
- **Prices:** Customizer → Maison Calista Settings (and/or edit the Stays & Pricing page blocks).

Convert to WebP on the server (LiteSpeed Image Optimize / ShortPixel) if desired — JPEGs are already optimized for delivery.

---

## Deployment & maintenance

1. Deploy theme folder + WordPress + recommended plugins.
2. Run theme setup once on staging, then migrate DB or re-run setup on production.
3. Purge LiteSpeed cache after content changes.
4. Review prices and legal pages periodically.
5. Keep WordPress, theme, and plugins updated.

See **HANDOVER.md** for the short admin checklist.

---

## Owner input still required

- Exact WhatsApp number
- Exact Google Maps pin / address
- Final price confirmation before go-live
- Social profile URLs
- Legal review of Privacy & Cookie pages
- Optional: higher-res brochure masters for sharper WebP exports
