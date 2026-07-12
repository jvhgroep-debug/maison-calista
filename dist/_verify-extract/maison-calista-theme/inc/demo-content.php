<?php
/**
 * One-click demo content installer for Maison Calista pages, menus and reading settings.
 *
 * @package Maison_Calista
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin notice + setup action.
 */
function maison_calista_setup_admin_notice(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'themes' !== $screen->id ) {
		return;
	}

	$installed = (bool) get_option( 'maison_calista_demo_installed' );
	$url       = wp_nonce_url( admin_url( 'themes.php?maison_calista_setup=1' ), 'maison_calista_setup' );
	$resync    = wp_nonce_url( admin_url( 'themes.php?maison_calista_setup=1&maison_calista_force=1' ), 'maison_calista_setup' );

	if ( ! $installed ) {
		echo '<div class="notice notice-info"><p>';
		echo esc_html__( 'Maison Calista theme is active. Create all pages, menus and homepage content in one click.', 'maison-calista' );
		echo ' <a class="button button-primary" href="' . esc_url( $url ) . '">' . esc_html__( 'Run Maison Calista Setup', 'maison-calista' ) . '</a>';
		echo '</p></div>';
		return;
	}

	echo '<div class="notice notice-info is-dismissible"><p>';
	echo esc_html__( 'Maison Calista pages are installed.', 'maison-calista' );
	echo ' <a href="' . esc_url( $resync ) . '">' . esc_html__( 'Re-sync pages from theme files', 'maison-calista' ) . '</a>';
	echo '</p></div>';
}
add_action( 'admin_notices', 'maison_calista_setup_admin_notice' );

/**
 * Handle setup request.
 */
function maison_calista_maybe_run_setup(): void {
	if ( ! isset( $_GET['maison_calista_setup'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	check_admin_referer( 'maison_calista_setup' );
	maison_calista_install_demo_content();
	wp_safe_redirect( admin_url( 'themes.php?maison_calista_setup_done=1' ) );
	exit;
}
add_action( 'admin_init', 'maison_calista_maybe_run_setup' );

/**
 * Auto-install pages, menus and French defaults on first theme activation.
 */
function maison_calista_after_switch_theme(): void {
	if ( ! get_option( 'maison_calista_demo_installed' ) ) {
		maison_calista_install_demo_content();
	} elseif ( function_exists( 'maison_calista_seed_placeholder_defaults' ) ) {
		maison_calista_seed_placeholder_defaults();
	}
}
add_action( 'after_switch_theme', 'maison_calista_after_switch_theme' );

/**
 * Success notice.
 */
function maison_calista_setup_done_notice(): void {
	if ( empty( $_GET['maison_calista_setup_done'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Maison Calista pages, menus and homepage settings were created successfully.', 'maison-calista' ) . '</p></div>';
}
add_action( 'admin_notices', 'maison_calista_setup_done_notice' );

/**
 * Load HTML content file.
 * French (`fr` / root) is the default source language; English lives in `en/`.
 *
 * @param string $file Content filename.
 * @param string $lang Language code: fr|en.
 */
function maison_calista_load_content( string $file, string $lang = 'fr' ): string {
	$base = MAISON_CALISTA_DIR . '/inc/content/';

	if ( 'en' === $lang ) {
		$path = $base . 'en/' . $file;
	} else {
		// Primary French files at content root, with fr/ as fallback.
		$path = $base . $file;
		if ( ! file_exists( $path ) ) {
			$path = $base . 'fr/' . $file;
		}
	}

	if ( ! file_exists( $path ) ) {
		return '';
	}

	$content = (string) file_get_contents( $path );
	return str_replace(
		array( '%%THEME_URI%%', '%%HOME_URL%%' ),
		array( esc_url( MAISON_CALISTA_URI ), esc_url( trailingslashit( home_url() ) ) ),
		$content
	);
}

/**
 * Create or update a page.
 *
 * @return int Page ID.
 */
function maison_calista_upsert_page( string $title, string $slug, string $content, string $template = '' ): int {
	$existing = get_page_by_path( $slug );
	$data     = array(
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => $content,
	);

	if ( $existing instanceof WP_Post ) {
		$data['ID'] = $existing->ID;
		$page_id    = (int) wp_update_post( $data, true );
	} else {
		$page_id = (int) wp_insert_post( $data, true );
	}

	if ( $page_id && $template ) {
		update_post_meta( $page_id, '_wp_page_template', $template );
	}

	return is_wp_error( $page_id ) ? 0 : $page_id;
}

/**
 * Install all demo pages and menus.
 */
function maison_calista_install_demo_content(): void {
	// French titles (default site language). English titles stored in post meta for Polylang.
	$pages = array(
		array( 'Accueil', 'home', 'home.html', '', 'Home' ),
		array( 'À propos de Maison Calista', 'about-maison-calista', 'about.html', 'page-about', 'About Maison Calista' ),
		array( 'La résidence', 'the-residence', 'residence.html', 'page-residence', 'The Residence' ),
		array( 'Accompagnement & soins', 'care-support', 'care.html', 'page-care', 'Care & Support' ),
		array( 'Famille', 'family', 'family.html', 'page-family', 'Family' ),
		array( 'Activités', 'activities', 'activities.html', 'page-activities', 'Activities' ),
		array( 'Restaurant', 'restaurant', 'restaurant.html', 'page-restaurant', 'Restaurant' ),
		array( 'Séjours & tarifs', 'stays-pricing', 'pricing.html', 'page-pricing', 'Stays & Pricing' ),
		array( 'Galerie', 'gallery', 'gallery.html', 'page-gallery', 'Gallery' ),
		array( 'Contact', 'contact', 'contact.html', 'page-contact', 'Contact' ),
		array( 'Politique de confidentialité', 'privacy-policy', 'privacy.html', '', 'Privacy Policy' ),
		array( 'Politique de cookies', 'cookie-policy', 'cookies.html', '', 'Cookie Policy' ),
	);

	$ids = array();
	foreach ( $pages as $page ) {
		$content         = maison_calista_load_content( $page[2], 'fr' );
		$ids[ $page[1] ] = maison_calista_upsert_page( $page[0], $page[1], $content, $page[3] );
	}

	// Store English HTML + title for Polylang / WPML editors.
	foreach ( $pages as $page ) {
		if ( empty( $ids[ $page[1] ] ) ) {
			continue;
		}
		$en = maison_calista_load_content( $page[2], 'en' );
		if ( $en ) {
			update_post_meta( $ids[ $page[1] ], '_maison_calista_content_en', $en );
		}
		// Keep FR copy as well for convenience.
		$fr = maison_calista_load_content( $page[2], 'fr' );
		if ( $fr ) {
			update_post_meta( $ids[ $page[1] ], '_maison_calista_content_fr', $fr );
		}
		update_post_meta( $ids[ $page[1] ], '_maison_calista_title_en', $page[4] );
	}

	if ( ! empty( $ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
	}
	if ( ! empty( $ids['privacy-policy'] ) ) {
		update_option( 'wp_page_for_privacy_policy', $ids['privacy-policy'] );
	}

	// Encourage French as WordPress site language when unset.
	if ( ! get_option( 'WPLANG' ) ) {
		update_option( 'WPLANG', 'fr_FR' );
	}

	maison_calista_install_menus( $ids );
	if ( function_exists( 'maison_calista_seed_placeholder_defaults' ) ) {
		delete_option( 'maison_calista_placeholders_seeded' );
		maison_calista_seed_placeholder_defaults();
	}
	update_option( 'maison_calista_demo_installed', 1 );
}

/**
 * Create primary + footer menus.
 *
 * @param array<string,int> $ids Page IDs keyed by slug.
 */
function maison_calista_install_menus( array $ids ): void {
	$primary_items = array(
		'about-maison-calista' => 'À propos',
		'the-residence'        => 'La résidence',
		'care-support'         => 'Accompagnement',
		'family'               => 'Famille',
		'activities'           => 'Activités',
		'restaurant'           => 'Restaurant',
		'stays-pricing'        => 'Séjours & tarifs',
		'gallery'              => 'Galerie',
		'contact'              => 'Contact',
	);

	// Remove previous theme menus to avoid duplicates on re-run.
	foreach ( array( 'Maison Calista Primary', 'Maison Calista Footer' ) as $menu_name ) {
		$existing = wp_get_nav_menu_object( $menu_name );
		if ( $existing ) {
			wp_delete_nav_menu( (int) $existing->term_id );
		}
	}

	$menu_id = wp_create_nav_menu( 'Maison Calista Primary' );
	if ( is_wp_error( $menu_id ) ) {
		return;
	}

	foreach ( $primary_items as $slug => $label ) {
		if ( empty( $ids[ $slug ] ) ) {
			continue;
		}
		wp_update_nav_menu_item(
			(int) $menu_id,
			0,
			array(
				'menu-item-title'     => $label,
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $ids[ $slug ],
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			)
		);
	}

	$locations            = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = (int) $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	$footer_id = wp_create_nav_menu( 'Maison Calista Footer' );
	if ( ! is_wp_error( $footer_id ) ) {
		foreach ( array( 'privacy-policy', 'cookie-policy', 'contact' ) as $slug ) {
			if ( empty( $ids[ $slug ] ) ) {
				continue;
			}
			wp_update_nav_menu_item(
				(int) $footer_id,
				0,
				array(
					'menu-item-title'     => get_the_title( $ids[ $slug ] ),
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $ids[ $slug ],
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				)
			);
		}
		$locations           = get_theme_mod( 'nav_menu_locations', array() );
		$locations['footer'] = (int) $footer_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}
}

/**
 * WP-CLI friendly alias.
 */
function maison_calista_cli_install(): void {
	maison_calista_install_demo_content();
	WP_CLI::success( 'Maison Calista demo content installed.' );
}
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'maison-calista install', 'maison_calista_cli_install' );
}
