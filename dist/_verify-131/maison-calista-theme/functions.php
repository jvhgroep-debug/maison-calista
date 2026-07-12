<?php
/**
 * Maison Calista theme bootstrap.
 *
 * @package Maison_Calista
 * @since 1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MAISON_CALISTA_VERSION', '1.3.1' );
define( 'MAISON_CALISTA_DIR', get_template_directory() );
define( 'MAISON_CALISTA_URI', get_template_directory_uri() );

require_once MAISON_CALISTA_DIR . '/inc/setup.php';
require_once MAISON_CALISTA_DIR . '/inc/enqueue.php';
require_once MAISON_CALISTA_DIR . '/inc/template-tags.php';
require_once MAISON_CALISTA_DIR . '/inc/prices.php';
require_once MAISON_CALISTA_DIR . '/inc/i18n.php';
require_once MAISON_CALISTA_DIR . '/inc/seo.php';
require_once MAISON_CALISTA_DIR . '/inc/schema.php';
require_once MAISON_CALISTA_DIR . '/inc/customizer.php';
require_once MAISON_CALISTA_DIR . '/inc/patterns.php';
require_once MAISON_CALISTA_DIR . '/inc/demo-content.php';
