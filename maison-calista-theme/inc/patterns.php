<?php
/**
 * Pattern registration helper.
 * HTML/PHP patterns in /patterns are auto-discovered by WordPress 6+.
 *
 * @package Maison_Calista
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ensure theme pattern category exists (also registered in setup.php).
 */
function maison_calista_patterns_ready(): void {
	// Reserved for dynamic pattern registration if needed later.
}
add_action( 'init', 'maison_calista_patterns_ready', 20 );
