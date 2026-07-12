<?php
/**
 * Customizer settings for contact, pricing and integrations.
 * All owner-specific values are editable here without code changes.
 *
 * @package Maison_Calista
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Customizer options.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function maison_calista_customize_register( WP_Customize_Manager $wp_customize ): void {
	$wp_customize->add_section(
		'maison_calista_settings',
		array(
			'title'       => __( 'Maison Calista Settings', 'maison-calista' ),
			'description' => __( 'Replace temporary placeholders before go-live: WhatsApp, Maps, social links, prices and legal notice. Leave fields empty to keep placeholders (social links stay hidden).', 'maison-calista' ),
			'priority'    => 30,
		)
	);

	$fields = array(
		'maison_calista_contact_email'             => array(
			'label'       => __( 'Contact email', 'maison-calista' ),
			'default'     => 'contact@maisoncalista.com',
			'type'        => 'email',
			'description' => '',
		),
		'maison_calista_whatsapp'                  => array(
			'label'       => __( 'WhatsApp number (international digits)', 'maison-calista' ),
			'default'     => '',
			'type'        => 'text',
			'description' => __( 'Leave empty to show “To be confirmed”. Enter digits only (e.g. 2126xxxxxxx) to activate the WhatsApp button.', 'maison-calista' ),
		),
		'maison_calista_maps_embed'                => array(
			'label'       => __( 'Google Maps embed URL', 'maison-calista' ),
			'default'     => '',
			'type'        => 'url',
			'description' => __( 'Leave empty for a disabled map placeholder. Paste the iframe src URL when the exact pin is ready.', 'maison-calista' ),
		),
		'maison_calista_maps_url'                  => array(
			'label'       => __( 'Google Maps link (optional)', 'maison-calista' ),
			'default'     => '',
			'type'        => 'url',
			'description' => __( 'Optional “Open in Google Maps” link shown under the map once available.', 'maison-calista' ),
		),
		'maison_calista_location_label'            => array(
			'label'       => __( 'Location label', 'maison-calista' ),
			'default'     => 'Près de Marrakech, Maroc',
			'type'        => 'text',
			'description' => __( 'Shown on the contact page and map placeholder. English override below.', 'maison-calista' ),
		),
		'maison_calista_location_label_en'         => array(
			'label'       => __( 'Location label (English)', 'maison-calista' ),
			'default'     => 'Near Marrakech, Morocco',
			'type'        => 'text',
			'description' => '',
		),
		'maison_calista_fluent_form_id'            => array(
			'label'       => __( 'Fluent Forms ID', 'maison-calista' ),
			'default'     => '0',
			'type'        => 'number',
			'description' => __( 'Set to your Fluent Forms form ID when the plugin is installed. 0 = built-in form.', 'maison-calista' ),
		),
		'maison_calista_social_facebook'           => array(
			'label'       => __( 'Facebook URL', 'maison-calista' ),
			'default'     => '',
			'type'        => 'url',
			'description' => __( 'Leave empty to hide. Add a full URL to show the link in the footer.', 'maison-calista' ),
		),
		'maison_calista_social_instagram'          => array(
			'label'       => __( 'Instagram URL', 'maison-calista' ),
			'default'     => '',
			'type'        => 'url',
			'description' => __( 'Leave empty to hide.', 'maison-calista' ),
		),
		'maison_calista_social_linkedin'           => array(
			'label'       => __( 'LinkedIn URL', 'maison-calista' ),
			'default'     => '',
			'type'        => 'url',
			'description' => __( 'Leave empty to hide.', 'maison-calista' ),
		),
		'maison_calista_price_discovery_double'    => array(
			'label'       => __( 'Discovery double / person / month (EUR)', 'maison-calista' ),
			'default'     => '1899',
			'type'        => 'text',
			'description' => __( 'Brochure provisional price — confirm before go-live.', 'maison-calista' ),
		),
		'maison_calista_price_discovery_single'    => array(
			'label'       => __( 'Discovery single / month (EUR)', 'maison-calista' ),
			'default'     => '2599',
			'type'        => 'text',
			'description' => __( 'Brochure provisional price — confirm before go-live.', 'maison-calista' ),
		),
		'maison_calista_price_discovery_surcharge' => array(
			'label'       => __( 'Discovery surcharge / person / month (EUR)', 'maison-calista' ),
			'default'     => '319',
			'type'        => 'text',
			'description' => __( 'Brochure provisional price — confirm before go-live.', 'maison-calista' ),
		),
		'maison_calista_price_wellbeing'           => array(
			'label'       => __( 'Well-Being package / month (EUR)', 'maison-calista' ),
			'default'     => '1899',
			'type'        => 'text',
			'description' => __( 'Brochure provisional price — confirm before go-live.', 'maison-calista' ),
		),
		'maison_calista_price_comfort'             => array(
			'label'       => __( 'Comfort package / month (EUR)', 'maison-calista' ),
			'default'     => '2599',
			'type'        => 'text',
			'description' => __( 'Brochure provisional price — confirm before go-live.', 'maison-calista' ),
		),
		'maison_calista_price_signature'           => array(
			'label'       => __( 'Signature package / month (EUR)', 'maison-calista' ),
			'default'     => '3799',
			'type'        => 'text',
			'description' => __( 'Brochure provisional price — confirm before go-live.', 'maison-calista' ),
		),
	);

	foreach ( $fields as $id => $config ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $config['default'],
				'sanitize_callback' => 'maison_calista_sanitize_customizer',
				'transport'         => 'refresh',
			)
		);
		$control = array(
			'label'   => $config['label'],
			'section' => 'maison_calista_settings',
			'type'    => $config['type'],
		);
		if ( ! empty( $config['description'] ) ) {
			$control['description'] = $config['description'];
		}
		$wp_customize->add_control( $id, $control );
	}

	$wp_customize->add_setting(
		'maison_calista_legal_notice_enabled',
		array(
			'default'           => true,
			'sanitize_callback' => static function ( $value ) {
				return (bool) $value;
			},
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'maison_calista_legal_notice_enabled',
		array(
			'label'       => __( 'Show legal review banner on Privacy & Cookies', 'maison-calista' ),
			'description' => __( 'Turn off after a lawyer has reviewed and approved the legal pages.', 'maison-calista' ),
			'section'     => 'maison_calista_settings',
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'maison_calista_legal_notice_fr',
		array(
			'default'           => "\u{00C0} R\u{00C9}VISER AVANT LA MISE EN LIGNE \u{2014} Cette page est un mod\u{00E8}le professionnel \u{00E0} titre informatif uniquement. Elle doit \u{00EA}tre contr\u{00F4}l\u{00E9}e et valid\u{00E9}e par un juriste qualifi\u{00E9} avant publication.",
			'sanitize_callback' => 'sanitize_textarea_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'maison_calista_legal_notice_fr',
		array(
			'label'   => __( 'Legal review notice (French)', 'maison-calista' ),
			'section' => 'maison_calista_settings',
			'type'    => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'maison_calista_legal_notice_en',
		array(
			'default'           => 'LEGAL REVIEW REQUIRED BEFORE GO-LIVE — This page is a professional template for general information only. It must be reviewed and approved by qualified legal counsel before publication.',
			'sanitize_callback' => 'sanitize_textarea_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'maison_calista_legal_notice_en',
		array(
			'label'   => __( 'Legal review notice (English)', 'maison-calista' ),
			'section' => 'maison_calista_settings',
			'type'    => 'textarea',
		)
	);
}
add_action( 'customize_register', 'maison_calista_customize_register' );

/**
 * Sanitize customizer values.
 *
 * @param mixed                     $value   Value.
 * @param WP_Customize_Setting|null $setting Setting.
 * @return mixed
 */
function maison_calista_sanitize_customizer( $value, $setting = null ) {
	$id = ( is_object( $setting ) && isset( $setting->id ) ) ? (string) $setting->id : '';

	if ( false !== strpos( $id, 'email' ) ) {
		return sanitize_email( (string) $value );
	}
	if ( false !== strpos( $id, 'url' ) || false !== strpos( $id, 'social' ) || false !== strpos( $id, 'maps' ) ) {
		return esc_url_raw( (string) $value );
	}
	if ( false !== strpos( $id, 'form_id' ) ) {
		return absint( $value );
	}
	if ( false !== strpos( $id, 'price' ) ) {
		return preg_replace( '/[^\d]/', '', (string) $value );
	}

	return sanitize_text_field( (string) $value );
}

/**
 * Format price for display with euro sign.
 */
function maison_calista_format_price( string $mod_key, string $fallback ): string {
	$raw = (string) get_theme_mod( $mod_key, $fallback );
	$raw = preg_replace( '/[^\d]/', '', $raw );
	if ( '' === $raw ) {
		$raw = $fallback;
	}
	return '€' . number_format_i18n( (float) $raw, 0 );
}

/**
 * Seed brochure prices into theme mods once (idempotent).
 */
function maison_calista_seed_placeholder_defaults(): void {
	if ( get_option( 'maison_calista_placeholders_seeded' ) ) {
		return;
	}

	$defaults = array(
		'maison_calista_price_discovery_double'    => '1899',
		'maison_calista_price_discovery_single'    => '2599',
		'maison_calista_price_discovery_surcharge' => '319',
		'maison_calista_price_wellbeing'           => '1899',
		'maison_calista_price_comfort'             => '2599',
		'maison_calista_price_signature'           => '3799',
		'maison_calista_contact_email'             => 'contact@maisoncalista.com',
		'maison_calista_location_label'            => "Pr\u{00E8}s de Marrakech, Maroc",
		'maison_calista_location_label_en'         => 'Near Marrakech, Morocco',
		'maison_calista_legal_notice_enabled'      => true,
	);

	foreach ( $defaults as $key => $value ) {
		$current = get_theme_mod( $key, null );
		if ( null === $current || '' === $current ) {
			set_theme_mod( $key, $value );
		}
	}

	foreach ( array( 'maison_calista_whatsapp', 'maison_calista_maps_embed', 'maison_calista_maps_url', 'maison_calista_social_facebook', 'maison_calista_social_instagram', 'maison_calista_social_linkedin' ) as $empty_key ) {
		if ( null === get_theme_mod( $empty_key, null ) ) {
			set_theme_mod( $empty_key, '' );
		}
	}

	update_option( 'maison_calista_placeholders_seeded', 1 );
}
