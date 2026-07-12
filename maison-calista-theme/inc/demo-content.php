<?php
/**
 * One-click / auto demo content installer for Maison Calista.
 *
 * Creates pages, menus, front page, French defaults and English meta
 * on first activation AND self-heals if setup is incomplete (e.g. same
 * theme re-uploaded after a failed earlier activate).
 *
 * @package Maison_Calista
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Bump when installer behaviour or required pages change. */
const MAISON_CALISTA_DEMO_SCHEMA = '1.3.4';

/**
 * Required page slugs for a complete install.
 *
 * @return string[]
 */
function maison_calista_required_page_slugs(): array {
	return array(
		'home',
		'about-maison-calista',
		'the-residence',
		'care-support',
		'family',
		'activities',
		'restaurant',
		'stays-pricing',
		'gallery',
		'contact',
		'privacy-policy',
		'cookie-policy',
	);
}

/**
 * Page definitions: FR title, slug, content file, template, EN title.
 *
 * @return array<int, array{0:string,1:string,2:string,3:string,4:string}>
 */
function maison_calista_demo_page_defs(): array {
	return array(
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
}

/**
 * Whether demo content is fully present and wired.
 */
function maison_calista_demo_is_complete(): bool {
	foreach ( maison_calista_required_page_slugs() as $slug ) {
		$page = get_page_by_path( $slug );
		if ( ! $page instanceof WP_Post ) {
			return false;
		}
	}

	if ( 'page' !== get_option( 'show_on_front' ) ) {
		return false;
	}

	$home = get_page_by_path( 'home' );
	if ( ! $home instanceof WP_Post || (int) get_option( 'page_on_front' ) !== (int) $home->ID ) {
		return false;
	}

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( empty( $locations['primary'] ) ) {
		return false;
	}

	if ( MAISON_CALISTA_DEMO_SCHEMA !== (string) get_option( 'maison_calista_demo_schema', '' ) ) {
		return false;
	}

	return true;
}

/**
 * Admin notice + setup action.
 */
function maison_calista_setup_admin_notice(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ! in_array( $screen->id, array( 'themes', 'dashboard', 'edit-page' ), true ) ) {
		return;
	}

	$error = get_transient( 'maison_calista_setup_error' );
	$url   = wp_nonce_url( admin_url( 'themes.php?maison_calista_setup=1&maison_calista_force=1' ), 'maison_calista_setup' );

	if ( $error ) {
		echo '<div class="notice notice-error"><p>';
		echo esc_html__( 'Maison Calista setup error: ', 'maison-calista' ) . esc_html( (string) $error );
		echo ' <a class="button button-primary" href="' . esc_url( $url ) . '">' . esc_html__( 'Retry setup', 'maison-calista' ) . '</a>';
		echo '</p></div>';
	}

	if ( ! maison_calista_demo_is_complete() ) {
		echo '<div class="notice notice-warning"><p>';
		echo esc_html__( 'Maison Calista pages are not installed yet. Click to create all pages, menus and the homepage automatically.', 'maison-calista' );
		echo ' <a class="button button-primary" href="' . esc_url( $url ) . '">' . esc_html__( 'Run Maison Calista Setup', 'maison-calista' ) . '</a>';
		echo '</p></div>';
		return;
	}

	if ( $screen && 'themes' === $screen->id ) {
		$resync = wp_nonce_url( admin_url( 'themes.php?maison_calista_setup=1&maison_calista_force=1' ), 'maison_calista_setup' );
		echo '<div class="notice notice-info is-dismissible"><p>';
		echo esc_html__( 'Maison Calista pages are installed.', 'maison-calista' );
		echo ' <a href="' . esc_url( $resync ) . '">' . esc_html__( 'Re-sync pages from theme files', 'maison-calista' ) . '</a>';
		echo '</p></div>';
	}
}
add_action( 'admin_notices', 'maison_calista_setup_admin_notice' );

/**
 * Handle manual setup request.
 */
function maison_calista_maybe_run_setup(): void {
	if ( ! isset( $_GET['maison_calista_setup'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	check_admin_referer( 'maison_calista_setup' );
	delete_option( 'maison_calista_demo_installed' );
	delete_option( 'maison_calista_demo_schema' );
	maison_calista_install_demo_content( true );
	wp_safe_redirect( admin_url( 'themes.php?maison_calista_setup_done=1' ) );
	exit;
}
add_action( 'admin_init', 'maison_calista_maybe_run_setup' );

/**
 * Auto-install on theme switch (next request after activate).
 */
function maison_calista_after_switch_theme(): void {
	maison_calista_run_setup_safe( false );
}
add_action( 'after_switch_theme', 'maison_calista_after_switch_theme' );

/**
 * Self-heal: if theme is active but pages are missing (common after
 * re-uploading the same theme ZIP), install automatically in admin.
 */
function maison_calista_auto_install_on_admin(): void {
	if ( wp_doing_ajax() || wp_installing() ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( maison_calista_demo_is_complete() ) {
		return;
	}

	// Avoid repeat loops within the same request / burst.
	if ( get_transient( 'maison_calista_setup_running' ) ) {
		return;
	}

	maison_calista_run_setup_safe( false );
}
add_action( 'admin_init', 'maison_calista_auto_install_on_admin', 5 );

/**
 * Also attempt once on front-end if still incomplete (covers hosts that
 * skip admin after theme upload/replace while stylesheet stays active).
 */
function maison_calista_auto_install_on_init(): void {
	if ( is_admin() || wp_installing() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}
	if ( maison_calista_demo_is_complete() ) {
		return;
	}
	if ( get_transient( 'maison_calista_setup_running' ) ) {
		return;
	}

	maison_calista_run_setup_safe( false );
}
add_action( 'init', 'maison_calista_auto_install_on_init', 20 );

/**
 * Run installer with lock + error capture (never fatal the site).
 *
 * @param bool $force Force re-write of page content.
 */
function maison_calista_run_setup_safe( bool $force = false ): void {
	set_transient( 'maison_calista_setup_running', 1, 2 * MINUTE_IN_SECONDS );
	try {
		if ( ! $force && MAISON_CALISTA_DEMO_SCHEMA !== (string) get_option( 'maison_calista_demo_schema', '' ) ) {
			$force = true;
		}
		if ( function_exists( 'wp_raise_memory_limit' ) ) {
			wp_raise_memory_limit( 'admin' );
		}
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 120 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}
		maison_calista_install_demo_content( $force );
		delete_transient( 'maison_calista_setup_error' );
	} catch ( Throwable $e ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && function_exists( 'error_log' ) ) {
			error_log( 'Maison Calista setup error: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
		set_transient( 'maison_calista_setup_error', $e->getMessage(), DAY_IN_SECONDS );
	}
	delete_transient( 'maison_calista_setup_running' );
}

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
		$path = $base . $file;
		if ( ! file_exists( $path ) ) {
			$path = $base . 'fr/' . $file;
		}
	}

	if ( ! file_exists( $path ) ) {
		return '';
	}

	$content = (string) file_get_contents( $path );
	$home    = ( 'en' === $lang )
		? trailingslashit( home_url( '/en' ) )
		: trailingslashit( home_url( '/' ) );

	return str_replace(
		array( '%%THEME_URI%%', '%%HOME_URL%%' ),
		array( esc_url( MAISON_CALISTA_URI ), esc_url( $home ) ),
		$content
	);
}

/**
 * Create or update a page.
 *
 * @return int Page ID.
 */
function maison_calista_upsert_page( string $title, string $slug, string $content, string $template = '', bool $force = false ): int {
	$existing = get_page_by_path( $slug );

	// Prefer updating the site privacy policy page when slug matches.
	if ( ! $existing && 'privacy-policy' === $slug ) {
		$privacy_id = (int) get_option( 'wp_page_for_privacy_policy' );
		if ( $privacy_id > 0 ) {
			$existing = get_post( $privacy_id );
		}
	}

	$data = array(
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => $content,
		'post_author'  => maison_calista_setup_author_id(),
	);

	if ( $existing instanceof WP_Post ) {
		$data['ID'] = $existing->ID;
		if ( ! $force && ! empty( $existing->post_content ) && false !== strpos( (string) $existing->post_content, 'mc-hero' ) ) {
			// Keep existing Maison Calista content unless force re-sync.
			unset( $data['post_content'] );
		}
		$result = wp_update_post( wp_slash( $data ), true );
	} else {
		$result = wp_insert_post( wp_slash( $data ), true );
	}

	if ( is_wp_error( $result ) ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && function_exists( 'error_log' ) ) {
			error_log( 'Maison Calista page error (' . $slug . '): ' . $result->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
		return 0;
	}

	$page_id = (int) $result;

	if ( $page_id && $template ) {
		update_post_meta( $page_id, '_wp_page_template', $template );
	}

	return $page_id;
}

/**
 * Author for auto-created pages.
 */
function maison_calista_setup_author_id(): int {
	$user_id = get_current_user_id();
	if ( $user_id > 0 ) {
		return $user_id;
	}
	$admins = get_users(
		array(
			'role'   => 'administrator',
			'number' => 1,
			'fields' => 'ID',
		)
	);
	return ! empty( $admins[0] ) ? (int) $admins[0] : 1;
}

/**
 * Install all demo pages and menus.
 *
 * @param bool $force Force overwrite page HTML from theme files.
 */
function maison_calista_install_demo_content( bool $force = false ): void {
	$pages = maison_calista_demo_page_defs();
	$ids   = array();

	foreach ( $pages as $page ) {
		$content         = maison_calista_load_content( $page[2], 'fr' );
		$ids[ $page[1] ] = maison_calista_upsert_page( $page[0], $page[1], $content, $page[3], $force );
	}

	$created = 0;
	foreach ( $pages as $page ) {
		if ( empty( $ids[ $page[1] ] ) ) {
			continue;
		}
		++$created;

		$en = maison_calista_load_content( $page[2], 'en' );
		if ( $en ) {
			update_post_meta( $ids[ $page[1] ], '_maison_calista_content_en', $en );
		}
		$fr = maison_calista_load_content( $page[2], 'fr' );
		if ( $fr ) {
			update_post_meta( $ids[ $page[1] ], '_maison_calista_content_fr', $fr );
		}
		update_post_meta( $ids[ $page[1] ], '_maison_calista_title_en', $page[4] );
		update_post_meta( $ids[ $page[1] ], '_maison_calista_title_fr', $page[0] );
	}

	if ( $created < 8 ) {
		throw new RuntimeException(
			sprintf(
				/* translators: %d: number of pages created */
				'Only %d Maison Calista pages could be created. Check file permissions and PHP error logs.',
				$created
			)
		);
	}

	if ( ! empty( $ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $ids['home'] );
	}
	if ( ! empty( $ids['privacy-policy'] ) ) {
		update_option( 'wp_page_for_privacy_policy', (int) $ids['privacy-policy'] );
	}

	// French as default site language (language pack optional).
	update_option( 'WPLANG', 'fr_FR' );

	maison_calista_install_menus( $ids );
	maison_calista_assign_fse_navigation( $ids );
	maison_calista_setup_polylang_translations( $ids, $pages, $force );

	if ( function_exists( 'maison_calista_seed_placeholder_defaults' ) ) {
		delete_option( 'maison_calista_placeholders_seeded' );
		maison_calista_seed_placeholder_defaults();
	}

	// Ensure theme template parts win over stale customizations from a previous theme.
	maison_calista_reset_custom_template_parts();

	update_option( 'maison_calista_demo_installed', 1 );
	update_option( 'maison_calista_demo_schema', MAISON_CALISTA_DEMO_SCHEMA );
	update_option( 'maison_calista_demo_installed_at', time() );

	flush_rewrite_rules( false );
}

/**
 * Create primary + footer menus and assign to theme locations.
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

	foreach ( array( 'Maison Calista Primary', 'Maison Calista Footer' ) as $menu_name ) {
		$existing = wp_get_nav_menu_object( $menu_name );
		if ( $existing ) {
			wp_delete_nav_menu( (int) $existing->term_id );
		}
	}

	$menu_id = wp_create_nav_menu( 'Maison Calista Primary' );
	if ( is_wp_error( $menu_id ) ) {
		throw new RuntimeException( 'Could not create primary menu: ' . $menu_id->get_error_message() );
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

	$locations            = (array) get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = (int) $menu_id;

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
		$locations['footer'] = (int) $footer_id;
	}

	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Ensure FSE Navigation block (ref) can resolve the classic primary menu.
 * Also stores page map for language switcher helpers.
 *
 * @param array<string,int> $ids Page IDs.
 */
function maison_calista_assign_fse_navigation( array $ids ): void {
	update_option( 'maison_calista_page_map', $ids );

	// Publish a wp_navigation post synced from the classic menu when possible (WP 6.3+).
	if ( ! function_exists( 'wp_create_nav_menu' ) ) {
		return;
	}

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( empty( $locations['primary'] ) || ! function_exists( 'wp_get_nav_menu_items' ) ) {
		return;
	}

	$items = wp_get_nav_menu_items( (int) $locations['primary'] );
	if ( ! is_array( $items ) || ! $items ) {
		return;
	}

	$blocks = '';
	foreach ( $items as $item ) {
		$url   = isset( $item->url ) ? $item->url : '';
		$label = isset( $item->title ) ? $item->title : '';
		if ( ! $url || ! $label ) {
			continue;
		}
		$blocks .= sprintf(
			'<!-- wp:navigation-link {"label":%s,"type":"page","id":%d,"url":%s,"kind":"post-type"} /-->' . "\n",
			wp_json_encode( $label ),
			(int) $item->object_id,
			wp_json_encode( $url )
		);
	}

	if ( ! $blocks ) {
		return;
	}

	$existing = get_posts(
		array(
			'post_type'      => 'wp_navigation',
			'name'           => 'maison-calista-primary',
			'posts_per_page' => 1,
			'post_status'    => 'any',
		)
	);

	$nav_data = array(
		'post_title'   => 'Maison Calista Primary',
		'post_name'    => 'maison-calista-primary',
		'post_status'  => 'publish',
		'post_type'    => 'wp_navigation',
		'post_content' => $blocks,
		'post_author'  => maison_calista_setup_author_id(),
	);

	if ( ! empty( $existing[0] ) ) {
		$nav_data['ID'] = (int) $existing[0]->ID;
		$nav_id         = wp_update_post( wp_slash( $nav_data ), true );
	} else {
		$nav_id = wp_insert_post( wp_slash( $nav_data ), true );
	}

	if ( ! is_wp_error( $nav_id ) && $nav_id ) {
		update_option( 'maison_calista_navigation_id', (int) $nav_id );
	}
}

/**
 * Prefer theme file templates / parts / global styles over customized DB copies.
 */
function maison_calista_reset_custom_template_parts(): void {
	$types = array( 'wp_template_part', 'wp_template', 'wp_global_styles' );

	foreach ( $types as $post_type ) {
		$posts = get_posts(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => 100,
				'post_status'    => 'any',
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'wp_theme',
						'field'    => 'name',
						'terms'    => array( get_stylesheet(), get_template() ),
					),
				),
			)
		);

		foreach ( $posts as $post ) {
			wp_delete_post( (int) $post->ID, true );
		}
	}

	// Also clear any orphan global styles without theme term.
	$globals = get_posts(
		array(
			'post_type'      => 'wp_global_styles',
			'posts_per_page' => 20,
			'post_status'    => 'any',
			's'              => 'maison-calista',
		)
	);
	foreach ( $globals as $post ) {
		wp_delete_post( (int) $post->ID, true );
	}

	delete_option( 'maison_calista_navigation_id' );
}

/**
 * When Polylang is active, create/link English translations.
 *
 * @param array<string,int>                                                 $ids   FR page IDs.
 * @param array<int, array{0:string,1:string,2:string,3:string,4:string}> $pages Page defs.
 * @param bool                                                              $force Force content overwrite.
 */
function maison_calista_setup_polylang_translations( array $ids, array $pages, bool $force = false ): void {
	if ( ! function_exists( 'pll_set_post_language' ) || ! function_exists( 'pll_save_post_translations' ) ) {
		return;
	}

	maison_calista_ensure_polylang_languages();

	$translations_map = array();

	foreach ( $pages as $page ) {
		$slug  = $page[1];
		$fr_id = isset( $ids[ $slug ] ) ? (int) $ids[ $slug ] : 0;
		if ( $fr_id <= 0 ) {
			continue;
		}

		pll_set_post_language( $fr_id, 'fr' );

		$en_title   = $page[4];
		$en_content = maison_calista_load_content( $page[2], 'en' );
		if ( ! $en_content ) {
			$en_content = (string) get_post_meta( $fr_id, '_maison_calista_content_en', true );
		}

		$en_id = 0;
		if ( function_exists( 'pll_get_post' ) ) {
			$en_id = (int) pll_get_post( $fr_id, 'en' );
		}

		if ( $en_id <= 0 ) {
			// Look for existing EN page by slug suffix.
			$candidate = get_page_by_path( $slug . '-en' );
			if ( $candidate instanceof WP_Post ) {
				$en_id = (int) $candidate->ID;
			}
		}

		$en_data = array(
			'post_title'   => $en_title,
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => $en_content,
			'post_author'  => maison_calista_setup_author_id(),
		);

		if ( $en_id > 0 ) {
			$en_data['ID'] = $en_id;
			if ( ! $force ) {
				unset( $en_data['post_content'] );
			}
			$result = wp_update_post( wp_slash( $en_data ), true );
		} else {
			// Unique slug before language assignment if FR already took the slug.
			$en_data['post_name'] = $slug . '-en';
			$result               = wp_insert_post( wp_slash( $en_data ), true );
		}

		if ( is_wp_error( $result ) || ! $result ) {
			continue;
		}

		$en_id = (int) $result;
		pll_set_post_language( $en_id, 'en' );

		if ( ! empty( $page[3] ) ) {
			update_post_meta( $en_id, '_wp_page_template', $page[3] );
		}

		pll_save_post_translations(
			array(
				'fr' => $fr_id,
				'en' => $en_id,
			)
		);

		$translations_map[ $slug ] = array(
			'fr' => $fr_id,
			'en' => $en_id,
		);
	}

	if ( $translations_map ) {
		update_option( 'maison_calista_polylang_map', $translations_map );
	}

	if ( function_exists( 'PLL' ) && isset( PLL()->options ) && is_array( PLL()->options ) ) {
		$opts = PLL()->options;
		if ( empty( $opts['default_lang'] ) || 'fr' !== $opts['default_lang'] ) {
			$opts['default_lang'] = 'fr';
			update_option( 'polylang', $opts );
		}
	}
}

/**
 * Register FR + EN in Polylang when languages are still empty.
 */
function maison_calista_ensure_polylang_languages(): void {
	if ( ! function_exists( 'pll_languages_list' ) ) {
		return;
	}

	$existing = pll_languages_list( array( 'fields' => 'slug' ) );
	if ( is_array( $existing ) && in_array( 'fr', $existing, true ) && in_array( 'en', $existing, true ) ) {
		return;
	}

	if ( ! function_exists( 'PLL' ) || ! PLL()->model || ! method_exists( PLL()->model, 'add_language' ) ) {
		return;
	}

	$to_add = array();
	if ( ! is_array( $existing ) || ! in_array( 'fr', $existing, true ) ) {
		$to_add[] = array(
			'name'       => 'Français',
			'slug'       => 'fr',
			'locale'     => 'fr_FR',
			'rtl'        => 0,
			'term_group' => 0,
		);
	}
	if ( ! is_array( $existing ) || ! in_array( 'en', $existing, true ) ) {
		$to_add[] = array(
			'name'       => 'English',
			'slug'       => 'en',
			'locale'     => 'en_US',
			'rtl'        => 0,
			'term_group' => 1,
		);
	}

	foreach ( $to_add as $lang ) {
		PLL()->model->add_language( $lang );
	}
}

/**
 * WP-CLI friendly alias.
 */
function maison_calista_cli_install(): void {
	maison_calista_install_demo_content( true );
	WP_CLI::success( 'Maison Calista demo content installed.' );
}
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'maison-calista install', 'maison_calista_cli_install' );
}
