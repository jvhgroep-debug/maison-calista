=== Maison Calista ===
Contributors: maisoncalista
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 1.3.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: full-site-editing, block-patterns, block-styles, custom-colors, custom-logo, custom-menu, editor-style, featured-images, rtl-language-support, translation-ready, wide-blocks

Custom Gutenberg block theme for Maison Calista — exclusive boutique residence near Marrakech. French default + English translation.

== Description ==

Maison Calista is a warm, elegant Full Site Editing (FSE) WordPress theme built from the official French brochure. French is the default language; English is a full professional translation.

**Features**

* Full Site Editing with `theme.json` (cream / sand / gold palette)
* One-click setup: pages, French menus, homepage, brochure prices
* 12 content pages (FR + EN source files) + 404
* 17 block patterns, custom page templates, header/footer parts
* 21 SEO-named photos + official logo + brochure PDF
* Self-hosted Cormorant Garamond (titles) + Source Sans 3 (body)
* Language switcher: 🇫🇷 Français (default) · 🇬🇧 English
* Customizer: WhatsApp, Maps, social, Fluent Forms ID, prices, legal banner
* SEO meta/OG/hreflang helpers + JSON-LD schema (Yoast-compatible)
* Accessible gallery lightbox, skip link, focus styles, print CSS
* Deferred JS, lazy images, font preload for performance

**Recommended plugins**

* Yoast SEO
* Fluent Forms
* Polylang (French default + English)
* LiteSpeed Cache
* Wordfence Security

== Installation ==

1. Upload and activate the theme (ZIP via Appearance → Themes → Add New → Upload, or copy folder to `wp-content/themes/`).
2. On first activation, pages and menus are created automatically (French).
3. If needed, open **Appearance → Themes** and use **Run Maison Calista Setup** / **Re-sync pages from theme files**.
4. Configure **Appearance → Customize → Maison Calista Settings**.
5. Optional: install Polylang, set Français as default, create English translations using post meta `_maison_calista_content_en`.

== Frequently Asked Questions ==

= What language is default? =

French (`fr_FR`). English content ships in `inc/content/en/` and post meta after setup.

= How do I replace placeholders? =

Use **Customize → Maison Calista Settings** for WhatsApp, Maps, social URLs and prices. Logo: **Site Identity**. Legal banner: uncheck after lawyer review.

= How do I use Fluent Forms? =

Install Fluent Forms, enter the form ID in Customizer, keep `[mc_contact_form]` on the Contact page.

== Changelog ==

= 1.3.4 =
* Preview parity: full-bleed FSE templates, theme chrome header/footer/nav matching localhost preview.
* Reset customized templates/global styles on setup; widen theme.json layout; neutralize constrained content width.

= 1.3.3 =
* Fix demo content not installing when the same theme ZIP is re-uploaded/replaced (self-healing auto-setup).
* Auto-create pages, menus, front page, FSE navigation, French defaults and English content linking on activate and admin load.
* Add `/en/` fallback content swap when Polylang is not yet configured; Polylang FR/EN linking when the plugin is active.

= 1.3.2 =
* Fix PHP 8.x parse fatals from invalid `\u{…}` Unicode escapes in theme PHP strings (activation-critical on STRATO/PHP 8.2+).
* Verified clean install + activate on WordPress 7.0.1 / PHP 8.2 with Polylang, Yoast SEO, Fluent Forms.

= 1.3.1 =
* Fix critical activation/shortcode TypeErrors (empty shortcode atts, WP_Error handling, safer theme switch setup).

= 1.3.0 =
* Production release: FR default, EN translation, placeholders via Customizer, brand link styles, auto-setup on activation, installable package.

= 1.2.3 =
* Brand link styles, Cormorant Garamond titles.

= 1.2.2 =
* Customizer placeholders (WhatsApp TBC, maps, legal banner).

= 1.2.1 =
* A11y/perf polish, compiled fr_FR.mo.

= 1.2.0 =
* French as default language + English translation.

= 1.0.0 =
* Initial FSE theme.

== Credits ==

* Cormorant Garamond — SIL Open Font License 1.1
* Source Sans 3 — SIL Open Font License 1.1
* Photography and brochure assets provided for Maison Calista
* Theme developed for Maison Calista, Marrakech, Morocco
