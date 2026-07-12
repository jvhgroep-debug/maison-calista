<?php
/**
 * Theme supports and setup.
 *
 * @package Maison_Calista
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme supports after setup.
 */
function maison_calista_setup(): void {
	load_theme_textdomain( 'maison-calista', MAISON_CALISTA_DIR . '/languages' );

	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		)
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'maison-calista' ),
			'footer'  => __( 'Footer Menu', 'maison-calista' ),
		)
	);

	add_image_size( 'maison-calista-hero', 1920, 1080, true );
	add_image_size( 'maison-calista-card', 800, 600, true );
	add_image_size( 'maison-calista-gallery', 1200, 900, true );
}
add_action( 'after_setup_theme', 'maison_calista_setup' );

/**
 * Register block pattern category.
 */
function maison_calista_register_pattern_categories(): void {
	register_block_pattern_category(
		'maison-calista',
		array(
			'label' => __( 'Maison Calista', 'maison-calista' ),
		)
	);
}
add_action( 'init', 'maison_calista_register_pattern_categories' );

/**
 * Soft compatibility notes for recommended plugins (no hard dependency).
 */
function maison_calista_plugin_compat(): void {
	// Yoast SEO, Fluent Forms, LiteSpeed Cache, and Wordfence are supported via
	// standard WordPress hooks and markup. Theme avoids conflicting inline trackers.
}
add_action( 'after_setup_theme', 'maison_calista_plugin_compat' );

/**
 * Show contact form status notices.
 *
 * @param string $content Post content.
 * @return string
 */
function maison_calista_contact_notices( string $content ): string {
	if ( ! is_page( 'contact' ) || ! isset( $_GET['contact'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return $content;
	}

	$status = sanitize_key( (string) wp_unslash( $_GET['contact'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$notice = '';

	if ( 'sent' === $status ) {
		$msg = ( function_exists( 'maison_calista_is_english' ) && maison_calista_is_english() )
			? 'Thank you. Your message has been sent. We will respond with care and discretion.'
			: "Merci. Votre message a bien \u{00E9}t\u{00E9} envoy\u{00E9}. Nous vous r\u{00E9}pondrons avec attention et discr\u{00E9}tion.";
		$notice = '<div class="mc-form-notice mc-form-notice--success" role="status">' . esc_html( $msg ) . '</div>';
	} elseif ( 'invalid' === $status ) {
		$msg = ( function_exists( 'maison_calista_is_english' ) && maison_calista_is_english() )
			? 'Please complete all required fields and accept the privacy notice.'
			: "Veuillez remplir tous les champs obligatoires et accepter la notice de confidentialit\u{00E9}.";
		$notice = '<div class="mc-form-notice mc-form-notice--error" role="alert">' . esc_html( $msg ) . '</div>';
	}

	return $notice . $content;
}
add_filter( 'the_content', 'maison_calista_contact_notices', 8 );

/**
 * Replace theme URI / home URL placeholders in template parts and patterns.
 *
 * @param string $content Block content.
 * @return string
 */
function maison_calista_replace_asset_placeholders( string $content ): string {
	return str_replace(
		array( '%%THEME_URI%%', '%%HOME_URL%%' ),
		array( esc_url( MAISON_CALISTA_URI ), esc_url( trailingslashit( home_url() ) ) ),
		$content
	);
}
add_filter( 'render_block', 'maison_calista_replace_asset_placeholders', 5 );
add_filter( 'the_content', 'maison_calista_replace_asset_placeholders', 5 );

/**
 * Allow shortcodes inside Custom HTML blocks (contact form, WhatsApp, dates).
 *
 * @param string $block_content Rendered block HTML.
 * @param array  $block         Block data.
 * @return string
 */
function maison_calista_do_shortcodes_in_html_blocks( string $block_content, array $block ): string {
	if ( isset( $block['blockName'] ) && 'core/html' === $block['blockName'] ) {
		$block_content = do_shortcode( $block_content );
	}
	return $block_content;
}
add_filter( 'render_block', 'maison_calista_do_shortcodes_in_html_blocks', 9, 2 );
