<?php
/**
 * Title: Contact Block
 * Slug: maison-calista/contact-block
 * Categories: maison-calista
 * Description: Formulaire de contact, coordonnées, carte et WhatsApp
 */
?>
<!-- wp:group {"className":"mc-section mc-wide mc-reveal","layout":{"type":"constrained"}} -->
<div class="wp-block-group mc-section mc-wide mc-reveal">
	<!-- wp:html -->
	<span class="mc-eyebrow">Contact</span>
	<h2 class="mc-section-title">Nous serions ravis d'échanger avec vous</h2>
	<p class="mc-section-lead">Que vous souhaitiez rejoindre la liste d'attente, vous renseigner sur un séjour ou simplement en savoir plus — notre équipe répond avec soin et discrétion.</p>
	<div class="mc-contact-grid">
		<div>
	<!-- /wp:html -->

	<!-- wp:shortcode -->
	[mc_contact_form]
	<!-- /wp:shortcode -->

	<!-- wp:html -->
		</div>
		<div>
			<h3>Maison Calista</h3>
			<p>
				<a href="mailto:contact@maisoncalista.com">contact@maisoncalista.com</a><br />
				Marrakech, Maroc
			</p>
			<p><a class="mc-btn-cta" href="%%HOME_URL%%contact/">Liste d'attente</a></p>
			<p>
	<!-- /wp:html -->

	<!-- wp:shortcode -->
	[mc_whatsapp]
	<!-- /wp:shortcode -->

	<!-- wp:html -->
			</p>
			<div class="mc-maps" role="region" aria-label="Carte de localisation">
				[mc_maps]
			</div>
		</div>
	</div>
	<!-- /wp:html -->
</div>
<!-- /wp:group -->
