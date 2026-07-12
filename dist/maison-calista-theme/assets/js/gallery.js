/**
 * Accessible lightbox + category filters for gallery
 */
(function () {
	'use strict';

	var root = document.querySelector('[data-mc-gallery]');
	if (!root) return;

	var filters = root.querySelectorAll('[data-mc-filter]');
	var items = root.querySelectorAll('[data-mc-category]');
	var lightbox = document.querySelector('[data-mc-lightbox]');
	var lightboxImg = lightbox ? lightbox.querySelector('img') : null;
	var closeBtn = lightbox ? lightbox.querySelector('[data-mc-lightbox-close]') : null;
	var lastFocus = null;

	function getI18n() {
		return (window.maisonCalista && window.maisonCalista.i18n) || {};
	}

	if (closeBtn) {
		var i18n = getI18n();
		if (i18n.lightbox) closeBtn.setAttribute('aria-label', i18n.lightbox);
	}

	filters.forEach(function (btn) {
		btn.addEventListener('click', function () {
			var cat = btn.getAttribute('data-mc-filter');
			filters.forEach(function (b) {
				b.classList.toggle('is-active', b === btn);
				b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
			});
			items.forEach(function (item) {
				var match = cat === 'all' || item.getAttribute('data-mc-category') === cat;
				item.hidden = !match;
			});
		});
	});

	function getFocusable() {
		if (!lightbox) return [];
		return Array.prototype.slice.call(
			lightbox.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])')
		).filter(function (el) {
			return !el.hasAttribute('disabled') && el.offsetParent !== null;
		});
	}

	function trapFocus(e) {
		if (!lightbox || !lightbox.classList.contains('is-open') || e.key !== 'Tab') return;
		var focusable = getFocusable();
		if (!focusable.length) return;
		var first = focusable[0];
		var last = focusable[focusable.length - 1];
		if (e.shiftKey && document.activeElement === first) {
			e.preventDefault();
			last.focus();
		} else if (!e.shiftKey && document.activeElement === last) {
			e.preventDefault();
			first.focus();
		}
	}

	function openLightbox(src, alt) {
		if (!lightbox || !lightboxImg) return;
		lastFocus = document.activeElement;
		lightboxImg.src = src;
		lightboxImg.alt = alt || '';
		lightbox.classList.add('is-open');
		lightbox.setAttribute('aria-hidden', 'false');
		document.body.classList.add('mc-lightbox-open');
		if (closeBtn) {
			var i18n = getI18n();
			if (i18n.lightbox) closeBtn.setAttribute('aria-label', i18n.lightbox);
			closeBtn.focus();
		}
	}

	function closeLightbox() {
		if (!lightbox || !lightboxImg) return;
		lightbox.classList.remove('is-open');
		lightbox.setAttribute('aria-hidden', 'true');
		lightboxImg.removeAttribute('src');
		lightboxImg.alt = '';
		document.body.classList.remove('mc-lightbox-open');
		if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
	}

	items.forEach(function (item) {
		item.addEventListener('click', function () {
			var img = item.querySelector('img, .mc-placeholder');
			if (!img) return;
			var src = img.getAttribute('data-full') || img.getAttribute('src') || '';
			var alt = img.getAttribute('alt') || item.getAttribute('aria-label') || '';
			if (src) openLightbox(src, alt);
		});
		item.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				item.click();
			}
		});
	});

	if (closeBtn) closeBtn.addEventListener('click', closeLightbox);
	if (lightbox) {
		lightbox.addEventListener('click', function (e) {
			if (e.target === lightbox) closeLightbox();
		});
	}
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && lightbox && lightbox.classList.contains('is-open')) {
			closeLightbox();
			return;
		}
		trapFocus(e);
	});
})();
