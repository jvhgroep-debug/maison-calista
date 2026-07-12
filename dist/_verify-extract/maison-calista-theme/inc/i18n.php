<?php
/**
 * Internationalization — French is the default site language.
 *
 * @package Maison_Calista
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Recommended locale mapping for Polylang / WPML.
 * French is primary; English is the translation.
 *
 * @return array<string, string>
 */
function maison_calista_supported_locales(): array {
	return array(
		'fr' => 'fr_FR',
		'en' => 'en_US',
	);
}

/**
 * Whether the current request should be treated as English.
 * French is the site default; English only when Polylang/WPML says so,
 * or (without a multilingual plugin) when the URL uses the /en/ prefix.
 */
function maison_calista_is_english(): bool {
	if ( function_exists( 'pll_current_language' ) ) {
		return 'en' === pll_current_language( 'slug' );
	}
	if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
		return 'en' === ICL_LANGUAGE_CODE;
	}

	$request = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	if ( $request && preg_match( '#/(?:index\.php/)?en(/|$)#', $request ) ) {
		return true;
	}

	return false;
}

/**
 * Body class for language.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function maison_calista_body_classes( array $classes ): array {
	$classes[] = 'maison-calista-theme';
	$classes[] = maison_calista_is_english() ? 'lang-en' : 'lang-fr';

	if ( is_front_page() ) {
		$classes[] = 'mc-is-home';
	}

	return $classes;
}
add_filter( 'body_class', 'maison_calista_body_classes' );

/**
 * Prefer French as document language when WordPress locale is still English
 * but no multilingual plugin is active (fresh installs).
 *
 * @param string $output Language attributes.
 * @return string
 */
function maison_calista_language_attributes( string $output ): string {
	if ( function_exists( 'pll_current_language' ) || defined( 'ICL_LANGUAGE_CODE' ) ) {
		return $output;
	}

	if ( maison_calista_is_english() ) {
		return 'lang="en-US"';
	}

	return 'lang="fr-FR"';
}
add_filter( 'language_attributes', 'maison_calista_language_attributes' );
