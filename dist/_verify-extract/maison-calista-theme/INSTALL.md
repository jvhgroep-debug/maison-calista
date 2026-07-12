# Maison Calista — Installatiehandleiding

## Vereisten
- WordPress 6.4 of nieuwer
- PHP 8.0+
- Schone WordPress-installatie (of bestaande site)

## Installeren (5 minuten)

### Optie A — ZIP uploaden
1. Log in op WordPress als beheerder.
2. Ga naar **Weergave → Thema’s → Nieuwe toevoegen → Thema uploaden**.
3. Kies `maison-calista-theme-1.3.0.zip`.
4. Klik **Nu installeren**, daarna **Activeren**.

### Optie B — Map uploaden (FTP/SFTP)
1. Pak de ZIP uit.
2. Upload de map `maison-calista-theme` naar `wp-content/themes/`.
3. Activeer **Maison Calista** onder **Weergave → Thema’s**.

## Na activatie
1. Pagina’s, Franse menu’s en homepage worden **automatisch** aangemaakt.
2. Controleer onder **Pagina’s** of o.a. Accueil, La résidence, Contact aanwezig zijn.
3. Ga naar **Weergave → Personaliseren → Maison Calista Settings**:
   - WhatsApp (leeg = “To be confirmed”)
   - Google Maps embed-URL
   - Social URLs (leeg = verborgen)
   - Brochureprijzen bevestigen/aanpassen
   - Juridische banner aan/uit na review
4. Optioneel logo: **Identiteit van de site → Logo**.
5. Aanbevolen plugins: Yoast SEO, Fluent Forms, **Polylang** (FR standaard + EN), LiteSpeed Cache, Wordfence.

## Talen
- **Standaard:** Frans
- **Engels:** bestanden in thema + meta `_maison_calista_content_en` na setup  
  → Installeer Polylang, stel Français in als default, maak EN-vertalingen.

## Opnieuw synchroniseren
**Weergave → Thema’s** → *Re-sync pages from theme files* (overschrijft pagina-inhoud vanuit thema-bestanden).

## Support-contact site
- Website: https://www.maisoncalista.com  
- E-mail: contact@maisoncalista.com
