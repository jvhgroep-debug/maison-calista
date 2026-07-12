/**
 * Maison Calista — navigation & sticky header
 */
(function () {
	'use strict';

	var header = document.querySelector('.mc-header');
	var toggle = document.querySelector('.mc-nav-toggle');
	var nav = document.querySelector('.mc-header__nav');

	function getI18n() {
		return (window.maisonCalista && window.maisonCalista.i18n) || {};
	}

	function setNavOpen(open) {
		if (!header || !toggle) return;
		var i18n = getI18n();
		header.classList.toggle('is-nav-open', open);
		toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		toggle.setAttribute('aria-label', open ? (i18n.menuClose || 'Close menu') : (i18n.menuOpen || 'Open menu'));
		document.body.classList.toggle('mc-nav-locked', open);
	}

	if (header) {
		var onScroll = function () {
			if (window.scrollY > 12) {
				header.classList.add('is-scrolled');
			} else {
				header.classList.remove('is-scrolled');
			}
		};
		onScroll();
		window.addEventListener('scroll', onScroll, { passive: true });
	}

	if (toggle && header && nav) {
		toggle.addEventListener('click', function () {
			setNavOpen(!header.classList.contains('is-nav-open'));
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && header.classList.contains('is-nav-open')) {
				setNavOpen(false);
				toggle.focus();
			}
		});

		document.addEventListener('click', function (e) {
			if (!header.classList.contains('is-nav-open')) return;
			if (!header.contains(e.target)) {
				setNavOpen(false);
			}
		});
	}
})();
