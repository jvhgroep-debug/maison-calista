<?php
/**
 * Enqueue front-end and editor assets.
 *
 * @package Maison_Calista
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front-end styles and scripts.
 */
function maison_calista_enqueue_assets(): void {
	wp_enqueue_style(
		'maison-calista-main',
		MAISON_CALISTA_URI . '/assets/css/main.css',
		array(),
		MAISON_CALISTA_VERSION
	);

	wp_enqueue_script(
		'maison-calista-navigation',
		MAISON_CALISTA_URI . '/assets/js/navigation.js',
		array(),
		MAISON_CALISTA_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	wp_enqueue_script(
		'maison-calista-main',
		MAISON_CALISTA_URI . '/assets/js/main.js',
		array(),
		MAISON_CALISTA_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	$load_gallery = is_page( array( 'gallery', 'galerie' ) ) || is_page_template( 'page-gallery' );
	if ( ! $load_gallery && is_singular() ) {
		$post = get_post();
		if ( $post instanceof WP_Post && false !== strpos( (string) $post->post_content, 'data-mc-gallery' ) ) {
			$load_gallery = true;
		}
	}
	if ( $load_gallery ) {
		wp_enqueue_script(
			'maison-calista-gallery',
			MAISON_CALISTA_URI . '/assets/js/gallery.js',
			array(),
			MAISON_CALISTA_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}

	$whatsapp = get_theme_mod( 'maison_calista_whatsapp', '' );
	wp_localize_script(
		'maison-calista-navigation',
		'maisonCalista',
		array(
			'whatsapp' => sanitize_text_field( (string) $whatsapp ),
			'homeUrl'  => esc_url( home_url( '/' ) ),
			'i18n'     => array(
				'menuOpen'  => ( function_exists( 'maison_calista_is_english' ) && maison_calista_is_english() ) ? 'Open menu' : 'Ouvrir le menu',
				'menuClose' => ( function_exists( 'maison_calista_is_english' ) && maison_calista_is_english() ) ? 'Close menu' : 'Fermer le menu',
				'lightbox'  => ( function_exists( 'maison_calista_is_english' ) && maison_calista_is_english() ) ? 'Close image preview' : "Fermer l’aperçu de l’image",
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'maison_calista_enqueue_assets' );

/**
 * Preload primary fonts when files exist.
 */
function maison_calista_preload_fonts(): void {
	$fonts = array(
		'/assets/fonts/source-sans-3-latin.woff2',
		'/assets/fonts/cormorant-garamond-latin.woff2',
	);

	foreach ( $fonts as $font ) {
		$path = MAISON_CALISTA_DIR . $font;
		if ( file_exists( $path ) ) {
			printf(
				'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
				esc_url( MAISON_CALISTA_URI . $font )
			);
		}
	}
}
add_action( 'wp_head', 'maison_calista_preload_fonts', 1 );
