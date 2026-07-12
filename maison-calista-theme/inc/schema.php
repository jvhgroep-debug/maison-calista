<?php
/**
 * Structured data (JSON-LD) preparation.
 *
 * @package Maison_Calista
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output Organization / LocalBusiness / page-specific schema.
 */
function maison_calista_schema_jsonld(): void {
	if ( defined( 'WPSEO_VERSION' ) && apply_filters( 'maison_calista_disable_schema_when_yoast', true ) ) {
		// Keep Organization lightweight; Yoast often owns primary graph.
		// Still output Residence/Contact extras only if filter allows.
	}

	$is_en   = function_exists( 'maison_calista_is_english' ) && maison_calista_is_english();
	$email    = sanitize_email( (string) get_theme_mod( 'maison_calista_contact_email', 'contact@associationcalista.com' ) );
	$maps_url = esc_url( (string) get_theme_mod( 'maison_calista_maps_url', 'https://maps.google.com/?q=Marrakech+Morocco' ) );

	$organization = array(
		'@context' => 'https://schema.org',
		'@type'    => array( 'Organization', 'LodgingBusiness' ),
		'name'     => 'Maison Calista',
		'url'      => home_url( '/' ),
		'email'    => $email,
		'address'  => array(
			'@type'          => 'PostalAddress',
			'addressLocality' => 'Marrakech',
			'addressCountry'  => 'MA',
		),
		'logo'     => MAISON_CALISTA_URI . '/assets/images/logo/maison-calista-logo.svg',
		'image'    => MAISON_CALISTA_URI . '/assets/images/photos/maison-calista-pool-atlas-mountains-day.jpg',
		'sameAs'   => array_filter(
			array(
				get_theme_mod( 'maison_calista_social_facebook', '' ),
				get_theme_mod( 'maison_calista_social_instagram', '' ),
				get_theme_mod( 'maison_calista_social_linkedin', '' ),
			)
		),
	);

	$graphs = array( $organization );

	if ( is_front_page() || is_page( 'the-residence' ) ) {
		$graphs[] = array(
			'@context' => 'https://schema.org',
			'@type'    => 'Accommodation',
			'name'     => 'Maison Calista',
			'description' => $is_en
				? 'Exclusive boutique residence with 12 rooms near Marrakech, Morocco.'
				: 'Résidence boutique exclusive de 12 chambres près de Marrakech, Maroc.',
			'numberOfRooms' => 12,
			'address'  => array(
				'@type'           => 'PostalAddress',
				'addressLocality' => 'Marrakech',
				'addressCountry'  => 'MA',
			),
			'amenityFeature' => array(
				array(
					'@type' => 'LocationFeatureSpecification',
					'name'  => $is_en ? 'Swimming pool' : 'Piscine',
				),
				array(
					'@type' => 'LocationFeatureSpecification',
					'name'  => $is_en ? 'Private terrace' : 'Terrasse privée',
				),
				array(
					'@type' => 'LocationFeatureSpecification',
					'name'  => 'Restaurant',
				),
			),
		);
	}

	if ( is_page( 'contact' ) ) {
		$graphs[] = array(
			'@context' => 'https://schema.org',
			'@type'    => 'ContactPage',
			'name'     => $is_en ? 'Contact Maison Calista' : 'Contacter Maison Calista',
			'url'      => get_permalink(),
			'mainEntity' => array(
				'@type' => 'Organization',
				'name'  => 'Maison Calista',
				'email' => $email,
				'url'   => $maps_url,
			),
		);
	}

	if ( is_page( 'stays-pricing' ) ) {
		$graphs[] = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => array(
				array(
					'@type'          => 'Question',
					'name'           => $is_en
						? 'What is the Discovery Stay?'
						: 'Qu\'est-ce que le Séjour Découverte ?',
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => $is_en
							? 'A three-month stay to discover Maison Calista with full board, activities, pool access and daily support, without complex formalities or obligation afterwards.'
							: 'Un séjour de trois mois pour découvrir Maison Calista avec pension complète, activités, accès piscine et accompagnement quotidien, sans formalités complexes ni obligation par la suite.',
					),
				),
				array(
					'@type'          => 'Question',
					'name'           => $is_en
						? 'How many rooms are available?'
						: 'Combien de chambres sont disponibles ?',
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => $is_en
							? 'Maison Calista offers only 12 exclusive rooms. Admission is via a waiting list.'
							: 'Maison Calista propose seulement 12 chambres exclusives. L\'admission se fait via une liste d\'attente.',
					),
				),
			),
		);
	}

	// BreadcrumbList on singular pages.
	if ( is_singular( 'page' ) && ! is_front_page() ) {
		$graphs[] = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array(
					'@type'    => 'ListItem',
					'position' => 1,
					'name'     => $is_en ? 'Home' : 'Accueil',
					'item'     => home_url( '/' ),
				),
				array(
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => get_the_title(),
					'item'     => get_permalink(),
				),
			),
		);
	}

	foreach ( $graphs as $graph ) {
		echo '<script type="application/ld+json">' . wp_json_encode( $graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'maison_calista_schema_jsonld', 20 );
