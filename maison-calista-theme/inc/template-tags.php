<?php
/**
 * Template tags and short helpers.
 *
 * @package Maison_Calista
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Localized "Last updated" date for the current day.
 * French is the default language.
 */
function maison_calista_last_updated_label(): string {
	$is_en = function_exists( 'maison_calista_is_english' ) ? maison_calista_is_english() : false;
	$locale = $is_en ? 'en_US' : 'fr_FR';
	$ts     = current_time( 'timestamp' );
	$tz     = wp_timezone();

	$date = '';
	if ( class_exists( 'IntlDateFormatter' ) ) {
		try {
			$formatter = new IntlDateFormatter(
				$locale,
				IntlDateFormatter::LONG,
				IntlDateFormatter::NONE,
				$tz,
				IntlDateFormatter::GREGORIAN,
				'd MMMM y'
			);
			$formatted = $formatter->format( $ts );
			if ( is_string( $formatted ) && '' !== $formatted ) {
				$date = $formatted;
			}
		} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			$date = '';
		}
	}

	if ( '' === $date ) {
		$date = date_i18n( 'j F Y', $ts );
	}

	if ( $is_en ) {
		/* translators: %s: localized date */
		return sprintf( __( 'Last updated: %s', 'maison-calista' ), $date );
	}

	/* translators: %s: localized date */
	return sprintf( __( 'Dernière mise à jour : %s', 'maison-calista' ), $date );
}

/**
 * Render last updated markup.
 */
function maison_calista_render_last_updated(): string {
	return sprintf(
		'<p class="mc-last-updated" data-mc-last-updated>%s</p>',
		esc_html( maison_calista_last_updated_label() )
	);
}

/**
 * Shortcode: [mc_last_updated]
 */
function maison_calista_last_updated_shortcode(): string {
	return maison_calista_render_last_updated();
}
add_shortcode( 'mc_last_updated', 'maison_calista_last_updated_shortcode' );

/**
 * Copyright year span (auto current year).
 */
function maison_calista_copyright_year(): string {
	return esc_html( gmdate( 'Y', current_time( 'U' ) ) );
}
add_shortcode( 'mc_year', 'maison_calista_copyright_year' );

/**
 * Language switcher markup (Polylang/WPML ready; graceful fallback).
 * French is default and listed first.
 */
function maison_calista_language_switcher(): string {
	$items = array();

	if ( function_exists( 'pll_the_languages' ) ) {
		$langs = pll_the_languages(
			array(
				'raw'           => 1,
				'hide_if_empty' => 0,
			)
		);
		if ( is_array( $langs ) ) {
			// Ensure Français appears before English.
			uasort(
				$langs,
				static function ( $a, $b ) {
					$order = array( 'fr' => 0, 'en' => 1 );
					$oa    = $order[ $a['slug'] ] ?? 9;
					$ob    = $order[ $b['slug'] ] ?? 9;
					return $oa <=> $ob;
				}
			);
			foreach ( $langs as $lang ) {
				$flag  = ( 'fr' === $lang['slug'] ) ? '🇫🇷' : '🇬🇧';
				$label = ( 'fr' === $lang['slug'] ) ? 'Français' : 'English';
				$items[] = sprintf(
					'<a class="mc-lang__link%s" href="%s" hreflang="%s" lang="%s">%s <span>%s</span></a>',
					! empty( $lang['current_lang'] ) ? ' is-active' : '',
					esc_url( $lang['url'] ),
					esc_attr( $lang['slug'] ),
					esc_attr( $lang['slug'] ),
					$flag,
					esc_html( $label )
				);
			}
		}
	} elseif ( defined( 'ICL_SITEPRESS_VERSION' ) && function_exists( 'icl_get_languages' ) ) {
		$langs = icl_get_languages( 'skip_missing=0&orderby=id' );
		if ( is_array( $langs ) ) {
			uasort(
				$langs,
				static function ( $a, $b ) {
					$order = array( 'fr' => 0, 'en' => 1 );
					$oa    = $order[ $a['language_code'] ] ?? 9;
					$ob    = $order[ $b['language_code'] ] ?? 9;
					return $oa <=> $ob;
				}
			);
			foreach ( $langs as $lang ) {
				$flag  = ( 'fr' === $lang['language_code'] ) ? '🇫🇷' : '🇬🇧';
				$label = ( 'fr' === $lang['language_code'] ) ? 'Français' : 'English';
				$items[] = sprintf(
					'<a class="mc-lang__link%s" href="%s" hreflang="%s" lang="%s">%s <span>%s</span></a>',
					! empty( $lang['active'] ) ? ' is-active' : '',
					esc_url( $lang['url'] ),
					esc_attr( $lang['language_code'] ),
					esc_attr( $lang['language_code'] ),
					$flag,
					esc_html( $label )
				);
			}
		}
	}

	if ( empty( $items ) ) {
		$is_en  = function_exists( 'maison_calista_is_english' ) && maison_calista_is_english();
		$fr_url = apply_filters( 'maison_calista_lang_url_fr', home_url( '/' ) );
		$en_url = apply_filters( 'maison_calista_lang_url_en', home_url( '/en/' ) );

		$items[] = sprintf(
			'<a class="mc-lang__link%s" href="%s" hreflang="fr" lang="fr">🇫🇷 <span>Français</span></a>',
			$is_en ? '' : ' is-active',
			esc_url( $fr_url )
		);
		$items[] = sprintf(
			'<a class="mc-lang__link%s" href="%s" hreflang="en" lang="en">🇬🇧 <span>English</span></a>',
			$is_en ? ' is-active' : '',
			esc_url( $en_url )
		);
	}

	return sprintf(
		'<nav class="mc-lang" aria-label="%s">%s</nav>',
		esc_attr__( 'Langue', 'maison-calista' ),
		implode( '', $items )
	);
}
add_shortcode( 'mc_language_switcher', 'maison_calista_language_switcher' );

/**
 * WhatsApp CTA button — empty Customizer value shows “To be confirmed”.
 */
function maison_calista_whatsapp_button( $atts = array() ): string {
	if ( ! is_array( $atts ) ) {
		$atts = array();
	}
	$atts = shortcode_atts(
		array(
			'label' => __( 'WhatsApp', 'maison-calista' ),
			'class' => 'mc-btn mc-btn--whatsapp',
		),
		$atts,
		'mc_whatsapp'
	);

	$number = preg_replace( '/\D+/', '', (string) get_theme_mod( 'maison_calista_whatsapp', '' ) );
	if ( '' === $number ) {
		$is_en = function_exists( 'maison_calista_is_english' ) && maison_calista_is_english();
		$hint  = $is_en
			? 'Set the number in Appearance → Customize → Maison Calista Settings.'
			: "Définir le numéro dans Apparence → Personnaliser → Maison Calista Settings.";
		return sprintf(
			'<span class="mc-tbc" title="%1$s"><span class="mc-tbc__value">To be confirmed</span></span>',
			esc_attr( $hint )
		);
	}

	$url = 'https://wa.me/' . $number;

	return sprintf(
		'<a class="%1$s" href="%2$s" target="_blank" rel="noopener noreferrer">%3$s</a>',
		esc_attr( $atts['class'] ),
		esc_url( $url ),
		esc_html( $atts['label'] )
	);
}
add_shortcode( 'mc_whatsapp', 'maison_calista_whatsapp_button' );

/**
 * Location label from Customizer.
 */
function maison_calista_location_label_shortcode(): string {
	$is_en = function_exists( 'maison_calista_is_english' ) && maison_calista_is_english();
	$label = $is_en
		? (string) get_theme_mod( 'maison_calista_location_label_en', 'Near Marrakech, Morocco' )
		: (string) get_theme_mod( 'maison_calista_location_label', "Près de Marrakech, Maroc" );
	return esc_html( $label );
}
add_shortcode( 'mc_location', 'maison_calista_location_label_shortcode' );

/**
 * Google Maps embed (Customizer) or disabled placeholder card.
 */
function maison_calista_maps_shortcode(): string {
	$embed = esc_url( (string) get_theme_mod( 'maison_calista_maps_embed', '' ) );
	$link  = esc_url( (string) get_theme_mod( 'maison_calista_maps_url', '' ) );
	$is_en = function_exists( 'maison_calista_is_english' ) && maison_calista_is_english();
	$loc   = $is_en
		? (string) get_theme_mod( 'maison_calista_location_label_en', 'Near Marrakech, Morocco' )
		: (string) get_theme_mod( 'maison_calista_location_label', "Près de Marrakech, Maroc" );

	if ( $embed ) {
		$title = $is_en
			? 'Map of Maison Calista near Marrakech'
			: "Carte de Maison Calista près de Marrakech";
		$html  = sprintf(
			'<div class="mc-maps"><iframe src="%1$s" title="%2$s" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>',
			$embed,
			esc_attr( $title )
		);
		if ( $link ) {
			$open = $is_en ? 'Open in Google Maps' : 'Ouvrir dans Google Maps';
			$html .= sprintf(
				'<p class="mc-maps__link"><a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a></p>',
				$link,
				esc_html( $open )
			);
		}
		$html .= '</div>';
		return $html;
	}

	$title = $is_en ? 'Map placeholder — exact pin to be confirmed' : "Carte désactivée — emplacement exact à confirmer";
	$body  = $is_en
		? 'The interactive map will appear here once the exact Google Maps embed URL is added in Appearance → Customize → Maison Calista Settings.'
		: "La carte interactive apparaîtra ici dès que l’URL d’intégration Google Maps sera ajoutée dans Apparence → Personnaliser → Maison Calista Settings.";

	$html = sprintf(
		'<div class="mc-maps mc-maps--disabled" role="img" aria-label="%1$s"><div class="mc-maps__placeholder"><p class="mc-maps__pin" aria-hidden="true"></p><p class="mc-maps__title">%2$s</p><p class="mc-maps__loc">%3$s</p><p class="mc-maps__hint">%4$s</p><p class="mc-tbc"><span class="mc-tbc__value">To be confirmed</span></p></div>',
		esc_attr( $title ),
		esc_html( $title ),
		esc_html( $loc ),
		esc_html( $body )
	);

	if ( $link ) {
		$open  = $is_en ? 'Open approximate area in Google Maps' : 'Ouvrir la zone approximative dans Google Maps';
		$html .= sprintf(
			'<p class="mc-maps__link"><a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a></p>',
			$link,
			esc_html( $open )
		);
	}

	$html .= '</div>';
	return $html;
}
add_shortcode( 'mc_maps', 'maison_calista_maps_shortcode' );

/**
 * Social links from Customizer — empty URLs are hidden (no placeholder clutter).
 */
function maison_calista_social_links_shortcode(): string {
	$links = array(
		'Facebook'  => (string) get_theme_mod( 'maison_calista_social_facebook', '' ),
		'Instagram' => (string) get_theme_mod( 'maison_calista_social_instagram', '' ),
		'LinkedIn'  => (string) get_theme_mod( 'maison_calista_social_linkedin', '' ),
	);

	$parts = array();
	foreach ( $links as $label => $url ) {
		$url = esc_url( $url );
		if ( '' === $url ) {
			continue;
		}
		$parts[] = sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			$url,
			esc_html( $label )
		);
	}

	if ( empty( $parts ) ) {
		return '';
	}

	return '<p class="mc-footer__social">' . implode( ' · ', $parts ) . '</p>';
}
add_shortcode( 'mc_social_links', 'maison_calista_social_links_shortcode' );

/**
 * Legal review banner — toggle + edit text in Customizer.
 */
function maison_calista_legal_review_notice_shortcode(): string {
	if ( ! get_theme_mod( 'maison_calista_legal_notice_enabled', true ) ) {
		return '';
	}

	$is_en = function_exists( 'maison_calista_is_english' ) && maison_calista_is_english();
	$msg   = $is_en
		? (string) get_theme_mod(
			'maison_calista_legal_notice_en',
			'LEGAL REVIEW REQUIRED BEFORE GO-LIVE — This page is a professional template for general information only. It must be reviewed and approved by qualified legal counsel before publication.'
		)
		: (string) get_theme_mod(
			'maison_calista_legal_notice_fr',
			"À RÉVISER AVANT LA MISE EN LIGNE — Cette page est un modèle professionnel à titre informatif uniquement. Elle doit être contrôlée et validée par un juriste qualifié avant publication."
		);

	if ( '' === trim( $msg ) ) {
		return '';
	}

	return '<aside class="mc-legal-banner" role="status"><strong class="mc-legal-banner__mark">' .
		esc_html( $is_en ? 'Legal notice' : 'Avis juridique' ) .
		'</strong><p class="mc-legal-banner__text">' . esc_html( $msg ) . '</p></aside>';
}
add_shortcode( 'mc_legal_review_notice', 'maison_calista_legal_review_notice_shortcode' );

/**
 * Site logo — Site Identity custom logo when set, else official theme SVG.
 */
function maison_calista_site_logo_shortcode( $atts = array() ): string {
	if ( ! is_array( $atts ) ) {
		$atts = array();
	}
	$atts = shortcode_atts(
		array(
			'class'  => 'mc-site-logo',
			'width'  => '160',
			'height' => '48',
		),
		$atts,
		'mc_site_logo'
	);

	$logo_id = (int) get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$img = wp_get_attachment_image(
			$logo_id,
			'full',
			false,
			array(
				'class'   => $atts['class'],
				'alt'     => get_bloginfo( 'name' ) ?: 'Maison Calista',
				'loading' => 'eager',
				'decoding'=> 'async',
			)
		);
		if ( $img ) {
			return $img;
		}
	}

	$src = trailingslashit( MAISON_CALISTA_URI ) . 'assets/images/logo/maison-calista-logo.svg';
	return sprintf(
		'<img class="%1$s" src="%2$s" alt="Maison Calista" width="%3$s" height="%4$s" loading="eager" decoding="async" />',
		esc_attr( $atts['class'] ),
		esc_url( $src ),
		esc_attr( $atts['width'] ),
		esc_attr( $atts['height'] )
	);
}
add_shortcode( 'mc_site_logo', 'maison_calista_site_logo_shortcode' );

/**
 * Fluent Forms placeholder shortcode when plugin is inactive.
 */
function maison_calista_contact_form_shortcode(): string {
	if ( shortcode_exists( 'fluentform' ) ) {
		$form_id = absint( get_theme_mod( 'maison_calista_fluent_form_id', 0 ) );
		if ( $form_id > 0 ) {
			return do_shortcode( '[fluentform id="' . $form_id . '"]' );
		}
	}

	return maison_calista_fallback_contact_form();
}
add_shortcode( 'mc_contact_form', 'maison_calista_contact_form_shortcode' );

/**
 * Accessible fallback contact form (Fluent Forms-ready styling).
 * French is the default language.
 */
function maison_calista_fallback_contact_form(): string {
	$email = sanitize_email( (string) get_theme_mod( 'maison_calista_contact_email', 'contact@maisoncalista.com' ) );
	$en    = function_exists( 'maison_calista_is_english' ) && maison_calista_is_english();

	$labels = $en ? array(
		'name'     => 'Full name',
		'email'    => 'Email',
		'phone'    => 'Phone',
		'subject'  => 'Subject',
		'package'  => 'Stay of interest',
		'select'   => 'Please select',
		'discovery'=> 'Discovery Stay',
		'wellbeing'=> 'Well-Being Package',
		'comfort'  => 'Comfort Package',
		'signature'=> 'Signature Package',
		'waitlist' => 'Waiting list',
		'other'    => 'Other',
		'message'  => 'Message',
		'privacy'  => 'I agree to the processing of my data as described in the %s.',
		'policy'   => 'Privacy Policy',
		'note'     => 'Messages are sent to %s. Replace this form with Fluent Forms when ready.',
		'send'     => 'Send message',
	) : array(
		'name'     => 'Nom complet',
		'email'    => 'E-mail',
		'phone'    => 'Téléphone',
		'subject'  => 'Objet',
		'package'  => 'Formule souhaitée',
		'select'   => 'Veuillez choisir',
		'discovery'=> 'Séjour Découverte',
		'wellbeing'=> 'Formule Bien-Être',
		'comfort'  => 'Formule Confort',
		'signature'=> 'Formule Signature',
		'waitlist' => 'Liste d’attente',
		'other'    => 'Autre',
		'message'  => 'Message',
		'privacy'  => 'J’accepte le traitement de mes données conformément à la %s.',
		'policy'   => 'Politique de confidentialité',
		'note'     => 'Les messages sont envoyés à %s. Remplacez ce formulaire par Fluent Forms lorsque prêt.',
		'send'     => 'Envoyer',
	);

	ob_start();
	?>
	<form class="mc-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
		<input type="hidden" name="action" value="maison_calista_contact" />
		<?php wp_nonce_field( 'maison_calista_contact', 'maison_calista_contact_nonce' ); ?>

		<div class="mc-form__row">
			<label for="mc-name"><?php echo esc_html( $labels['name'] ); ?></label>
			<input type="text" id="mc-name" name="mc_name" required autocomplete="name" />
		</div>

		<div class="mc-form__row">
			<label for="mc-email"><?php echo esc_html( $labels['email'] ); ?></label>
			<input type="email" id="mc-email" name="mc_email" required autocomplete="email" />
		</div>

		<div class="mc-form__row">
			<label for="mc-phone"><?php echo esc_html( $labels['phone'] ); ?></label>
			<input type="tel" id="mc-phone" name="mc_phone" autocomplete="tel" />
		</div>

		<div class="mc-form__row">
			<label for="mc-subject"><?php echo esc_html( $labels['subject'] ); ?></label>
			<input type="text" id="mc-subject" name="mc_subject" required />
		</div>

		<div class="mc-form__row">
			<label for="mc-package"><?php echo esc_html( $labels['package'] ); ?></label>
			<select id="mc-package" name="mc_package">
				<option value=""><?php echo esc_html( $labels['select'] ); ?></option>
				<option value="discovery"><?php echo esc_html( $labels['discovery'] ); ?></option>
				<option value="well-being"><?php echo esc_html( $labels['wellbeing'] ); ?></option>
				<option value="comfort"><?php echo esc_html( $labels['comfort'] ); ?></option>
				<option value="signature"><?php echo esc_html( $labels['signature'] ); ?></option>
				<option value="waitlist"><?php echo esc_html( $labels['waitlist'] ); ?></option>
				<option value="other"><?php echo esc_html( $labels['other'] ); ?></option>
			</select>
		</div>

		<div class="mc-form__row">
			<label for="mc-message"><?php echo esc_html( $labels['message'] ); ?></label>
			<textarea id="mc-message" name="mc_message" rows="6" required></textarea>
		</div>

		<div class="mc-form__row mc-form__row--check">
			<label>
				<input type="checkbox" name="mc_privacy" value="1" required />
				<?php
				printf(
					esc_html( $labels['privacy'] ),
					'<a href="' . esc_url( get_privacy_policy_url() ?: home_url( '/privacy-policy/' ) ) . '">' . esc_html( $labels['policy'] ) . '</a>'
				);
				?>
			</label>
		</div>

		<p class="mc-form__note">
			<?php printf( esc_html( $labels['note'] ), esc_html( $email ) ); ?>
		</p>

		<button type="submit" class="wp-block-button__link mc-btn"><?php echo esc_html( $labels['send'] ); ?></button>
	</form>
	<?php
	return (string) ob_get_clean();
}

/**
 * Handle fallback contact form submission.
 */
function maison_calista_handle_contact_form(): void {
	if ( ! isset( $_POST['maison_calista_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['maison_calista_contact_nonce'] ) ), 'maison_calista_contact' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'maison-calista' ), 403 );
	}

	$name     = isset( $_POST['mc_name'] ) ? sanitize_text_field( wp_unslash( $_POST['mc_name'] ) ) : '';
	$email    = isset( $_POST['mc_email'] ) ? sanitize_email( wp_unslash( $_POST['mc_email'] ) ) : '';
	$phone    = isset( $_POST['mc_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['mc_phone'] ) ) : '';
	$subject  = isset( $_POST['mc_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['mc_subject'] ) ) : '';
	$package  = isset( $_POST['mc_package'] ) ? sanitize_text_field( wp_unslash( $_POST['mc_package'] ) ) : '';
	$message  = isset( $_POST['mc_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['mc_message'] ) ) : '';
	$privacy  = ! empty( $_POST['mc_privacy'] );

	if ( ! $privacy || '' === $name || ! is_email( $email ) || '' === $subject || '' === $message ) {
		wp_safe_redirect( add_query_arg( 'contact', 'invalid', wp_get_referer() ?: home_url( '/contact/' ) ) );
		exit;
	}

	$to      = sanitize_email( (string) get_theme_mod( 'maison_calista_contact_email', 'contact@maisoncalista.com' ) );
	$mail_subject = sprintf( '[Maison Calista] %s', $subject );
	$body    = "Name: {$name}\nEmail: {$email}\nPhone: {$phone}\nPackage: {$package}\n\n{$message}";
	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . $name . ' <' . $email . '>',
	);

	wp_mail( $to, $mail_subject, $body, $headers );

	wp_safe_redirect( add_query_arg( 'contact', 'sent', home_url( '/contact/' ) ) );
	exit;
}
add_action( 'admin_post_nopriv_maison_calista_contact', 'maison_calista_handle_contact_form' );
add_action( 'admin_post_maison_calista_contact', 'maison_calista_handle_contact_form' );

/**
 * Inject skip link early in body.
 */
function maison_calista_skip_link(): void {
	$label = ( function_exists( 'maison_calista_is_english' ) && maison_calista_is_english() )
		? 'Skip to content'
		: 'Aller au contenu';
	echo '<a class="mc-skip-link" href="#main-content">' . esc_html( $label ) . '</a>';
}
add_action( 'wp_body_open', 'maison_calista_skip_link', 5 );
