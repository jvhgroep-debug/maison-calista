<?php
/**
 * Theme chrome (header / footer / primary nav) matching the local preview shell.
 *
 * @package Maison_Calista
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base home URL for the active language.
 */
function maison_calista_lang_home_url(): string {
	if ( function_exists( 'maison_calista_is_english' ) && maison_calista_is_english() ) {
		if ( function_exists( 'pll_home_url' ) ) {
			return trailingslashit( (string) pll_home_url( 'en' ) );
		}
		return trailingslashit( home_url( '/en' ) );
	}
	return trailingslashit( home_url( '/' ) );
}

/**
 * Primary navigation items (preview parity).
 *
 * @return array<int, array{slug:string,label:string}>
 */
function maison_calista_primary_nav_items(): array {
	$is_en = function_exists( 'maison_calista_is_english' ) && maison_calista_is_english();

	$items = array(
		array( 'slug' => 'about-maison-calista', 'fr' => 'À propos', 'en' => 'About' ),
		array( 'slug' => 'the-residence', 'fr' => 'La résidence', 'en' => 'The Residence' ),
		array( 'slug' => 'care-support', 'fr' => 'Accompagnement', 'en' => 'Care & Support' ),
		array( 'slug' => 'family', 'fr' => 'Famille', 'en' => 'Family' ),
		array( 'slug' => 'activities', 'fr' => 'Activités', 'en' => 'Activities' ),
		array( 'slug' => 'restaurant', 'fr' => 'Restaurant', 'en' => 'Restaurant' ),
		array( 'slug' => 'stays-pricing', 'fr' => 'Séjours & tarifs', 'en' => 'Stays & Pricing' ),
		array( 'slug' => 'gallery', 'fr' => 'Galerie', 'en' => 'Gallery' ),
		array( 'slug' => 'contact', 'fr' => 'Contact', 'en' => 'Contact' ),
	);

	$out = array();
	foreach ( $items as $item ) {
		$out[] = array(
			'slug'  => $item['slug'],
			'label' => $is_en ? $item['en'] : $item['fr'],
		);
	}
	return $out;
}

/**
 * Current page slug for aria-current.
 */
function maison_calista_current_page_slug(): string {
	if ( is_front_page() ) {
		return '';
	}
	if ( is_page() ) {
		$post = get_post();
		return $post instanceof WP_Post ? (string) $post->post_name : '';
	}
	return '';
}

/**
 * Primary nav markup (same structure as localhost preview).
 */
function maison_calista_primary_nav(): string {
	$home    = maison_calista_lang_home_url();
	$current = maison_calista_current_page_slug();
	$lis     = '';

	foreach ( maison_calista_primary_nav_items() as $item ) {
		$href   = $home . $item['slug'] . '/';
		$active = ( $current === $item['slug'] ) ? ' aria-current="page"' : '';
		$lis   .= sprintf(
			'<li class="wp-block-navigation-item"><a class="wp-block-navigation-item__content" href="%s"%s>%s</a></li>',
			esc_url( $href ),
			$active,
			esc_html( $item['label'] )
		);
	}

	return '<ul class="wp-block-navigation__container">' . $lis . '</ul>';
}
add_shortcode( 'mc_primary_nav', 'maison_calista_primary_nav' );

/**
 * Full site header (preview parity).
 */
function maison_calista_theme_header(): string {
	$is_en     = function_exists( 'maison_calista_is_english' ) && maison_calista_is_english();
	$home      = maison_calista_lang_home_url();
	$contact   = $home . 'contact/';
	$cta       = $is_en ? 'Waiting list' : 'Liste d’attente';
	$menu_open = $is_en ? 'Open menu' : 'Ouvrir le menu';
	$nav_label = $is_en ? 'Primary navigation' : 'Navigation principale';
	$skip      = $is_en ? 'Skip to content' : 'Aller au contenu';

	$logo = do_shortcode( '[mc_site_logo]' );
	$lang = do_shortcode( '[mc_language_switcher]' );
	$nav  = maison_calista_primary_nav();

	return sprintf(
		'<a class="mc-skip-link" href="#main-content">%1$s</a>
<header class="wp-block-group mc-header">
	<div class="mc-header__inner">
		<a class="mc-header__brand" href="%2$s">
			%3$s
			<span class="mc-header__brand-text screen-reader-text">Maison Calista</span>
		</a>
		<button class="mc-nav-toggle" type="button" aria-expanded="false" aria-controls="mc-primary-nav" aria-label="%4$s">
			<span></span><span></span><span></span>
		</button>
		<nav class="mc-header__nav" id="mc-primary-nav" aria-label="%5$s">%6$s</nav>
		<div class="mc-header__actions">
			%7$s
			<a class="mc-btn-cta" href="%8$s">%9$s</a>
		</div>
	</div>
</header>',
		esc_html( $skip ),
		esc_url( $home ),
		$logo,
		esc_attr( $menu_open ),
		esc_attr( $nav_label ),
		$nav,
		$lang,
		esc_url( $contact ),
		esc_html( $cta )
	);
}
add_shortcode( 'mc_theme_header', 'maison_calista_theme_header' );

/**
 * Full site footer (preview parity, FR/EN).
 */
function maison_calista_theme_footer(): string {
	$is_en = function_exists( 'maison_calista_is_english' ) && maison_calista_is_english();
	$home  = maison_calista_lang_home_url();

	$location = do_shortcode( '[mc_location]' );
	$lang     = do_shortcode( '[mc_language_switcher]' );
	$social   = do_shortcode( '[mc_social_links]' );
	$year     = do_shortcode( '[mc_year]' );
	$updated  = do_shortcode( '[mc_last_updated]' );

	if ( $is_en ) {
		return sprintf(
			'<footer class="wp-block-group mc-footer">
	<div class="mc-footer__grid">
		<div>
			<div class="mc-footer__brand">Maison Calista</div>
			<p>An exclusive boutique residence near Marrakech — warmth, light, humanity and quality of life.</p>
			<p><a href="mailto:contact@maisoncalista.com">contact@maisoncalista.com</a><br />%1$s</p>
		</div>
		<div>
			<h3 class="mc-footer__heading">Explore</h3>
			<ul class="mc-footer__list">
				<li><a href="%2$sabout-maison-calista/">About</a></li>
				<li><a href="%2$sthe-residence/">The residence</a></li>
				<li><a href="%2$scare-support/">Care &amp; support</a></li>
				<li><a href="%2$sactivities/">Activities</a></li>
				<li><a href="%2$srestaurant/">Restaurant</a></li>
			</ul>
		</div>
		<div>
			<h3 class="mc-footer__heading">Stays</h3>
			<ul class="mc-footer__list">
				<li><a href="%2$sstays-pricing/">Stays &amp; pricing</a></li>
				<li><a href="%2$sfamily/">Family</a></li>
				<li><a href="%2$sgallery/">Gallery</a></li>
				<li><a href="%2$scontact/">Contact</a></li>
			</ul>
		</div>
		<div>
			<h3 class="mc-footer__heading">Legal &amp; language</h3>
			<ul class="mc-footer__list">
				<li><a href="%2$sprivacy-policy/">Privacy</a></li>
				<li><a href="%2$scookie-policy/">Cookies</a></li>
			</ul>
			%3$s
			%4$s
		</div>
	</div>
	<div class="mc-footer__meta">
		<p>© %5$s Maison Calista. All rights reserved.</p>
		%6$s
	</div>
</footer>',
			$location,
			esc_url( $home ),
			$lang,
			$social,
			$year,
			$updated
		);
	}

	return sprintf(
		'<footer class="wp-block-group mc-footer">
	<div class="mc-footer__grid">
		<div>
			<div class="mc-footer__brand">Maison Calista</div>
			<p>Une résidence boutique exclusive près de Marrakech — chaleur, lumière, humanité et qualité de vie.</p>
			<p><a href="mailto:contact@maisoncalista.com">contact@maisoncalista.com</a><br />%1$s</p>
		</div>
		<div>
			<h3 class="mc-footer__heading">Explorer</h3>
			<ul class="mc-footer__list">
				<li><a href="%2$sabout-maison-calista/">À propos</a></li>
				<li><a href="%2$sthe-residence/">La résidence</a></li>
				<li><a href="%2$scare-support/">Accompagnement</a></li>
				<li><a href="%2$sactivities/">Activités</a></li>
				<li><a href="%2$srestaurant/">Restaurant</a></li>
			</ul>
		</div>
		<div>
			<h3 class="mc-footer__heading">Séjours</h3>
			<ul class="mc-footer__list">
				<li><a href="%2$sstays-pricing/">Séjours &amp; tarifs</a></li>
				<li><a href="%2$sfamily/">Famille</a></li>
				<li><a href="%2$sgallery/">Galerie</a></li>
				<li><a href="%2$scontact/">Contact</a></li>
			</ul>
		</div>
		<div>
			<h3 class="mc-footer__heading">Légal &amp; langue</h3>
			<ul class="mc-footer__list">
				<li><a href="%2$sprivacy-policy/">Confidentialité</a></li>
				<li><a href="%2$scookie-policy/">Cookies</a></li>
			</ul>
			%3$s
			%4$s
		</div>
	</div>
	<div class="mc-footer__meta">
		<p>© %5$s Maison Calista. Tous droits réservés.</p>
		%6$s
	</div>
</footer>',
		$location,
		esc_url( $home ),
		$lang,
		$social,
		$year,
		$updated
	);
}
add_shortcode( 'mc_theme_footer', 'maison_calista_theme_footer' );
