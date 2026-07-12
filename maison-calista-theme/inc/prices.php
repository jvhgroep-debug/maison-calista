<?php
/**
 * Editable package prices (Customizer-backed shortcodes).
 *
 * @package Maison_Calista
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Price key → customizer setting map.
 *
 * @return array<string, array{mod:string,fallback:string}>
 */
function maison_calista_price_map(): array {
	return array(
		'discovery_double'    => array(
			'mod'      => 'maison_calista_price_discovery_double',
			'fallback' => '1899',
		),
		'discovery_single'    => array(
			'mod'      => 'maison_calista_price_discovery_single',
			'fallback' => '2599',
		),
		'discovery_surcharge' => array(
			'mod'      => 'maison_calista_price_discovery_surcharge',
			'fallback' => '319',
		),
		'wellbeing'           => array(
			'mod'      => 'maison_calista_price_wellbeing',
			'fallback' => '1899',
		),
		'comfort'             => array(
			'mod'      => 'maison_calista_price_comfort',
			'fallback' => '2599',
		),
		'signature'           => array(
			'mod'      => 'maison_calista_price_signature',
			'fallback' => '3799',
		),
	);
}

/**
 * Format a price value for display.
 */
function maison_calista_get_price( string $key ): string {
	$map = maison_calista_price_map();
	if ( ! isset( $map[ $key ] ) ) {
		return '';
	}
	return maison_calista_format_price( $map[ $key ]['mod'], $map[ $key ]['fallback'] );
}

/**
 * Shortcode: [mc_price key="wellbeing"]
 *
 * @param array<string,string> $atts Attributes.
 */
function maison_calista_price_shortcode( $atts = array() ): string {
	if ( ! is_array( $atts ) ) {
		$atts = array();
	}
	$atts = shortcode_atts(
		array(
			'key' => '',
		),
		$atts,
		'mc_price'
	);
	$price = maison_calista_get_price( sanitize_key( (string) $atts['key'] ) );
	return $price ? esc_html( $price ) : '';
}
add_shortcode( 'mc_price', 'maison_calista_price_shortcode' );
