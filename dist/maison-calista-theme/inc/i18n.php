<?php
/**
 * Internationalization — French is the default site language.
 *
 * Without Polylang/WPML: `/en/` URL prefix serves English content from
 * post meta (`_maison_calista_content_en`), matching the local preview.
 * With Polylang: plugin language detection + linked translation pages win.
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
 * Whether a multilingual plugin is handling languages.
 */
function maison_calista_has_lang_plugin(): bool {
	return function_exists( 'pll_current_language' ) || defined( 'ICL_LANGUAGE_CODE' );
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

	$qv = get_query_var( 'maison_calista_lang' );
	if ( 'en' === $qv ) {
		return true;
	}

	$request = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	if ( $request && preg_match( '#/(?:index\.php/)?en(/|$)#', $request ) ) {
		return true;
	}

	return false;
}

/**
 * Register /en/ rewrite rules (fallback when Polylang is not active).
 */
function maison_calista_register_lang_rewrites(): void {
	if ( maison_calista_has_lang_plugin() ) {
		return;
	}

	add_rewrite_rule( '^en/?$', 'index.php?maison_calista_lang=en&pagename=home', 'top' );
	add_rewrite_rule( '^en/(.+?)/?$', 'index.php?maison_calista_lang=en&pagename=$matches[1]', 'top' );
}
add_action( 'init', 'maison_calista_register_lang_rewrites', 5 );

/**
 * @param string[] $vars Query vars.
 * @return string[]
 */
function maison_calista_lang_query_vars( $vars ) {
	if ( ! is_array( $vars ) ) {
		$vars = array();
	}
	$vars[] = 'maison_calista_lang';
	return $vars;
}
add_filter( 'query_vars', 'maison_calista_lang_query_vars' );

/**
 * Map /en/{slug}/ to the French page and swap content via filters.
 *
 * @param WP_Query $query Query.
 */
function maison_calista_parse_en_request( $query ): void {
	if ( maison_calista_has_lang_plugin() || ! $query instanceof WP_Query || ! $query->is_main_query() ) {
		return;
	}
	if ( is_admin() ) {
		return;
	}

	$lang = $query->get( 'maison_calista_lang' );
	if ( 'en' !== $lang ) {
		return;
	}

	$pagename = (string) $query->get( 'pagename' );
	if ( '' === $pagename || 'home' === $pagename ) {
		$home_id = (int) get_option( 'page_on_front' );
		if ( $home_id > 0 ) {
			$query->set( 'page_id', $home_id );
			$query->set( 'pagename', '' );
			$query->set( 'page', '' );
			$query->is_page     = true;
			$query->is_singular = true;
			$query->is_home     = false;
			$query->is_front_page = true;
		}
		return;
	}

	// Strip accidental en/ prefix from pagename.
	$pagename = preg_replace( '#^en/#', '', $pagename );
	$query->set( 'pagename', $pagename );
}
add_action( 'pre_get_posts', 'maison_calista_parse_en_request' );

/**
 * Swap page content to English meta when using /en/ fallback.
 *
 * @param string $content Content.
 * @return string
 */
function maison_calista_filter_content_language( $content ) {
	if ( ! is_string( $content ) ) {
		$content = '';
	}
	if ( maison_calista_has_lang_plugin() || ! maison_calista_is_english() || ! is_singular( 'page' ) ) {
		return $content;
	}

	$en = get_post_meta( get_the_ID(), '_maison_calista_content_en', true );
	return is_string( $en ) && $en !== '' ? $en : $content;
}
add_filter( 'the_content', 'maison_calista_filter_content_language', 4 );

/**
 * Swap page title to English meta when using /en/ fallback.
 *
 * @param string $title Title.
 * @param int    $post_id Post ID.
 * @return string
 */
function maison_calista_filter_title_language( $title, $post_id = 0 ) {
	if ( ! is_string( $title ) ) {
		$title = '';
	}
	if ( maison_calista_has_lang_plugin() || ! maison_calista_is_english() ) {
		return $title;
	}

	$post_id = $post_id ? (int) $post_id : get_the_ID();
	if ( $post_id <= 0 ) {
		return $title;
	}

	$en = get_post_meta( $post_id, '_maison_calista_title_en', true );
	return is_string( $en ) && $en !== '' ? $en : $title;
}
add_filter( 'the_title', 'maison_calista_filter_title_language', 10, 2 );

/**
 * Language URLs for the current (or given) page — FR default, EN via /en/.
 *
 * @param int $post_id Optional page ID.
 * @return array{fr:string,en:string}
 */
function maison_calista_lang_urls_for_page( int $post_id = 0 ): array {
	if ( $post_id <= 0 && is_singular( 'page' ) ) {
		$post_id = get_the_ID();
	}

	$fr = home_url( '/' );
	$en = home_url( '/en/' );

	if ( $post_id > 0 ) {
		$post = get_post( $post_id );
		if ( $post instanceof WP_Post && 'page' === $post->post_type ) {
			$home_id = (int) get_option( 'page_on_front' );
			if ( (int) $post->ID === $home_id || 'home' === $post->post_name ) {
				$fr = home_url( '/' );
				$en = home_url( '/en/' );
			} else {
				$fr = get_permalink( $post );
				$en = home_url( '/en/' . $post->post_name . '/' );
			}
		}
	}

	return array(
		'fr' => (string) $fr,
		'en' => (string) $en,
	);
}

/**
 * Filters for language switcher shortcode.
 *
 * @param string $url URL.
 * @return string
 */
function maison_calista_filter_lang_url_fr( $url ) {
	$urls = maison_calista_lang_urls_for_page();
	return $urls['fr'] ?: $url;
}
add_filter( 'maison_calista_lang_url_fr', 'maison_calista_filter_lang_url_fr' );

/**
 * @param string $url URL.
 * @return string
 */
function maison_calista_filter_lang_url_en( $url ) {
	$urls = maison_calista_lang_urls_for_page();
	return $urls['en'] ?: $url;
}
add_filter( 'maison_calista_lang_url_en', 'maison_calista_filter_lang_url_en' );

/**
 * Body class for language.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function maison_calista_body_classes( $classes ) {
	if ( ! is_array( $classes ) ) {
		$classes = array();
	}
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
function maison_calista_language_attributes( $output ) {
	if ( function_exists( 'pll_current_language' ) || defined( 'ICL_LANGUAGE_CODE' ) ) {
		return is_string( $output ) ? $output : '';
	}

	if ( maison_calista_is_english() ) {
		return 'lang="en-US"';
	}

	return 'lang="fr-FR"';
}
add_filter( 'language_attributes', 'maison_calista_language_attributes' );

/**
 * Prefer French as the frontend locale when no multilingual plugin is active.
 * (WPLANG alone does nothing until the fr_FR language pack is installed.)
 *
 * @param string $locale Locale.
 * @return string
 */
function maison_calista_filter_locale( $locale ) {
	if ( ! is_string( $locale ) ) {
		$locale = 'en_US';
	}
	if ( maison_calista_has_lang_plugin() ) {
		return $locale;
	}
	// Keep wp-admin in the user’s / WordPress default language.
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $locale;
	}
	return maison_calista_is_english() ? 'en_US' : 'fr_FR';
}
add_filter( 'locale', 'maison_calista_filter_locale', 1 );

/**
 * Point FSE Navigation block at the auto-created wp_navigation post.
 *
 * @param array<string,mixed> $parsed_block Block.
 * @return array<string,mixed>
 */
function maison_calista_navigation_block_ref( $parsed_block ) {
	if ( ! is_array( $parsed_block ) || empty( $parsed_block['blockName'] ) ) {
		return $parsed_block;
	}
	if ( 'core/navigation' !== $parsed_block['blockName'] ) {
		return $parsed_block;
	}

	$nav_id = (int) get_option( 'maison_calista_navigation_id' );
	if ( $nav_id > 0 ) {
		if ( empty( $parsed_block['attrs'] ) || ! is_array( $parsed_block['attrs'] ) ) {
			$parsed_block['attrs'] = array();
		}
		$parsed_block['attrs']['ref'] = $nav_id;
	}

	return $parsed_block;
}
add_filter( 'render_block_data', 'maison_calista_navigation_block_ref' );
