<?php
/**
 * SEO helpers (Yoast-compatible). French is the default language.
 *
 * @package Maison_Calista
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default meta descriptions per page slug (French source + English translation).
 *
 * @return array<string, string>
 */
function maison_calista_default_meta(): array {
	if ( function_exists( 'maison_calista_is_english' ) && maison_calista_is_english() ) {
		return array(
			'home'                 => 'Maison Calista is an exclusive boutique residence near Marrakech with 12 elegant rooms, Atlas views, personal support, and a warm quality of life.',
			'about-maison-calista' => 'Discover why Maison Calista was created: warmth, light, humanity and quality of life for residents near Marrakech, Morocco.',
			'the-residence'        => 'An intimate 12-room residence near Marrakech in bohemian-chic style with private terraces, gardens, pool and Atlas mountain views.',
			'care-support'         => 'Personal daily support at Maison Calista with a dedicated team and coordinated access to doctors, nurses and specialists.',
			'family'               => 'Stay connected: daily visits by arrangement, video calls, and family stays including meals and airport transfers.',
			'activities'           => 'Nature, culture, crafts, cooking, well-being, creativity and adventure activities around Maison Calista and Marrakech.',
			'restaurant'           => 'Three cuisines, a thousand flavours: Moroccan, French and Italian meals prepared on site with personal preferences in mind.',
			'stays-pricing'        => 'Discovery Stay and Well-Being, Comfort and Signature packages at Maison Calista near Marrakech.',
			'gallery'              => 'Gallery of Maison Calista: residence, rooms, gardens, pool, restaurant, activities and Marrakech moments.',
			'contact'              => 'Contact Maison Calista in Marrakech to join the waiting list or ask about stays and packages.',
			'privacy-policy'       => 'Privacy Policy for Maison Calista website visitors and enquiries.',
			'cookie-policy'        => 'Cookie Policy for the Maison Calista website.',
		);
	}

	return array(
		'home'                 => 'Maison Calista est une résidence boutique exclusive près de Marrakech : 12 chambres élégantes, vue sur l’Atlas, accompagnement personnalisé et qualité de vie.',
		'about-maison-calista' => 'Découvrez pourquoi Maison Calista a été créée : chaleur, lumière, humanité et qualité de vie près de Marrakech, au Maroc.',
		'the-residence'        => 'Une résidence intimiste de 12 chambres près de Marrakech, style bohème-chic, terrasses privées, jardins, piscine et vue sur l’Atlas.',
		'care-support'         => 'Accompagnement quotidien personnalisé à Maison Calista, avec une équipe dédiée et l’accès à médecins, infirmiers et spécialistes.',
		'family'               => 'Restez proches : visites selon le planning, appels vidéo et séjours familiaux avec repas et transferts aéroport inclus.',
		'activities'           => 'Nature, culture, artisanat, cuisine, bien-être, créativité et aventure autour de Maison Calista et de Marrakech.',
		'restaurant'           => 'Trois cuisines, mille saveurs : marocaine, française et italienne, préparées sur place selon vos préférences.',
		'stays-pricing'        => 'Séjour Découverte et formules Bien-Être, Confort et Signature à Maison Calista près de Marrakech.',
		'gallery'              => 'Galerie Maison Calista : résidence, chambres, jardins, piscine, restaurant, activités et moments à Marrakech.',
		'contact'              => 'Contactez Maison Calista à Marrakech pour rejoindre la liste d’attente ou vous renseigner sur les séjours.',
		'privacy-policy'       => 'Politique de confidentialité du site Maison Calista.',
		'cookie-policy'        => 'Politique de cookies du site Maison Calista.',
	);
}

/**
 * Output fallback meta description when Yoast is not active.
 */
function maison_calista_fallback_meta_description(): void {
	if ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) ) {
		return;
	}

	$slug = is_front_page() ? 'home' : ( is_singular() ? get_post_field( 'post_name', get_queried_object_id() ) : '' );
	$meta = maison_calista_default_meta();

	if ( isset( $meta[ $slug ] ) ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $meta[ $slug ] ) );
	}
}
add_action( 'wp_head', 'maison_calista_fallback_meta_description', 1 );

/**
 * Open Graph fallbacks when Yoast is inactive.
 */
function maison_calista_og_tags(): void {
	if ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) ) {
		return;
	}

	if ( ! is_singular() && ! is_front_page() ) {
		return;
	}

	$title = wp_get_document_title();
	$url   = is_singular() ? get_permalink() : home_url( '/' );
	$desc  = '';
	$slug  = is_front_page() ? 'home' : get_post_field( 'post_name', get_queried_object_id() );
	$meta  = maison_calista_default_meta();
	if ( isset( $meta[ $slug ] ) ) {
		$desc = $meta[ $slug ];
	}

	$image = MAISON_CALISTA_URI . '/assets/images/photos/maison-calista-restaurant-terrace-pool-dusk.jpg';
	if ( is_singular() && has_post_thumbnail() ) {
		$thumb = wp_get_attachment_image_url( get_post_thumbnail_id(), 'large' );
		if ( $thumb ) {
			$image = $thumb;
		}
	}

	$locale = ( function_exists( 'maison_calista_is_english' ) && maison_calista_is_english() ) ? 'en_US' : 'fr_FR';

	echo '<meta property="og:type" content="website" />' . "\n";
	printf( '<meta property="og:locale" content="%s" />' . "\n", esc_attr( $locale ) );
	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $url ) );
	printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $desc ) );
	printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $image ) );
	echo '<meta property="og:site_name" content="Maison Calista" />' . "\n";
	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
}
add_action( 'wp_head', 'maison_calista_og_tags', 2 );

/**
 * Canonical fallback.
 */
function maison_calista_canonical(): void {
	if ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) ) {
		return;
	}

	if ( is_singular() ) {
		printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( get_permalink() ) );
	} elseif ( is_front_page() ) {
		printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( home_url( '/' ) ) );
	}
}
add_action( 'wp_head', 'maison_calista_canonical', 3 );

/**
 * hreflang hints when no multilingual plugin is active.
 */
function maison_calista_hreflang_tags(): void {
	if ( function_exists( 'pll_the_languages' ) || defined( 'ICL_SITEPRESS_VERSION' ) ) {
		return;
	}

	$fr = apply_filters( 'maison_calista_lang_url_fr', home_url( '/' ) );
	$en = apply_filters( 'maison_calista_lang_url_en', home_url( '/en/' ) );
	printf( '<link rel="alternate" hreflang="fr" href="%s" />' . "\n", esc_url( $fr ) );
	printf( '<link rel="alternate" hreflang="en" href="%s" />' . "\n", esc_url( $en ) );
	printf( '<link rel="alternate" hreflang="x-default" href="%s" />' . "\n", esc_url( $fr ) );
}
add_action( 'wp_head', 'maison_calista_hreflang_tags', 4 );
