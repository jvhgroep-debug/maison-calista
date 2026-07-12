<?php
/**
 * Title: Gallery
 * Slug: maison-calista/gallery
 * Categories: maison-calista
 * Description: Galerie photo filtrable avec lightbox accessible
 */
?>
<!-- wp:group {"className":"mc-section mc-wide mc-reveal","layout":{"type":"constrained"}} -->
<div class="wp-block-group mc-section mc-wide mc-reveal">
	<!-- wp:html -->
	<span class="mc-eyebrow">Galerie</span>
	<h2 class="mc-section-title">Moments à Maison Calista</h2>
	<p class="mc-section-lead">Un aperçu de la résidence, des chambres, des jardins, de la cuisine et de la vie près de Marrakech.</p>
	<div data-mc-gallery>
		<div class="mc-gallery-filters" role="toolbar" aria-label="Filtrer la galerie">
			<button type="button" class="is-active" data-mc-filter="all" aria-pressed="true">Tout</button>
			<button type="button" data-mc-filter="residence" aria-pressed="false">Résidence</button>
			<button type="button" data-mc-filter="rooms" aria-pressed="false">Chambres</button>
			<button type="button" data-mc-filter="gardens" aria-pressed="false">Jardins</button>
			<button type="button" data-mc-filter="pool" aria-pressed="false">Piscine</button>
			<button type="button" data-mc-filter="restaurant" aria-pressed="false">Restaurant</button>
			<button type="button" data-mc-filter="activities" aria-pressed="false">Activités</button>
			<button type="button" data-mc-filter="marrakech" aria-pressed="false">Marrakech</button>
			<button type="button" data-mc-filter="family" aria-pressed="false">Moments en famille</button>
		</div>
		<div class="mc-gallery-grid">
			<button type="button" class="mc-gallery-item" data-mc-category="residence" aria-label="Vue aérienne au coucher du soleil" tabindex="0">
				<img src="%%THEME_URI%%/assets/images/photos/maison-calista-residence-aerial-sunset.jpg" data-full="%%THEME_URI%%/assets/images/photos/maison-calista-residence-aerial-sunset.jpg" alt="Vue aérienne de Maison Calista au coucher du soleil" loading="lazy" width="800" height="800" />
			</button>
			<button type="button" class="mc-gallery-item" data-mc-category="rooms" aria-label="Chambre twin" tabindex="0">
				<img src="%%THEME_URI%%/assets/images/photos/maison-calista-twin-bedroom-bohemian.jpg" data-full="%%THEME_URI%%/assets/images/photos/maison-calista-twin-bedroom-bohemian.jpg" alt="Chambre twin avec suspensions tissées" loading="lazy" width="800" height="800" />
			</button>
			<button type="button" class="mc-gallery-item" data-mc-category="gardens" aria-label="Cour-jardin" tabindex="0">
				<img src="%%THEME_URI%%/assets/images/photos/maison-calista-garden-courtyard.jpg" data-full="%%THEME_URI%%/assets/images/photos/maison-calista-garden-courtyard.jpg" alt="Cour-jardin de la résidence" loading="lazy" width="800" height="800" />
			</button>
			<button type="button" class="mc-gallery-item" data-mc-category="pool" aria-label="Piscine et Atlas" tabindex="0">
				<img src="%%THEME_URI%%/assets/images/photos/maison-calista-pool-atlas-mountains-day.jpg" data-full="%%THEME_URI%%/assets/images/photos/maison-calista-pool-atlas-mountains-day.jpg" alt="Piscine avec vue sur l'Atlas" loading="lazy" width="800" height="800" />
			</button>
			<button type="button" class="mc-gallery-item" data-mc-category="restaurant" aria-label="Terrasse restaurant" tabindex="0">
				<img src="%%THEME_URI%%/assets/images/photos/maison-calista-restaurant-terrace-day-atlas.jpg" data-full="%%THEME_URI%%/assets/images/photos/maison-calista-restaurant-terrace-day-atlas.jpg" alt="Terrasse restaurant de jour" loading="lazy" width="800" height="800" />
			</button>
			<button type="button" class="mc-gallery-item" data-mc-category="activities" aria-label="Lounge désert au coucher du soleil" tabindex="0">
				<img src="%%THEME_URI%%/assets/images/photos/maison-calista-desert-lounge-sunset.jpg" data-full="%%THEME_URI%%/assets/images/photos/maison-calista-desert-lounge-sunset.jpg" alt="Lounge désert au coucher du soleil" loading="lazy" width="800" height="800" />
			</button>
			<button type="button" class="mc-gallery-item" data-mc-category="marrakech" aria-label="Résidence près de Marrakech" tabindex="0">
				<img src="%%THEME_URI%%/assets/images/photos/maison-calista-residence-aerial-atlas.jpg" data-full="%%THEME_URI%%/assets/images/photos/maison-calista-residence-aerial-atlas.jpg" alt="Résidence avec l'Atlas près de Marrakech" loading="lazy" width="800" height="800" />
			</button>
			<button type="button" class="mc-gallery-item" data-mc-category="family" aria-label="Salon pour moments en famille" tabindex="0">
				<img src="%%THEME_URI%%/assets/images/photos/maison-calista-living-dining-garden-view.jpg" data-full="%%THEME_URI%%/assets/images/photos/maison-calista-living-dining-garden-view.jpg" alt="Salon pour moments en famille" loading="lazy" width="800" height="800" />
			</button>
		</div>
	</div>
	<div class="mc-lightbox" data-mc-lightbox aria-hidden="true" role="dialog" aria-modal="true" aria-label="Aperçu de l'image">
		<button type="button" class="mc-lightbox__close" data-mc-lightbox-close aria-label="Fermer">×</button>
		<img class="mc-lightbox__img" alt="" />
	</div>
	<!-- /wp:html -->
</div>
<!-- /wp:group -->
