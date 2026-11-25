/* The Artvibes — small progressive enhancements. Everything works without JS except the slider arrows. */
(function () {
	var $ = function (s, r) { return (r || document).querySelector(s); };
	var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

	// Toggle panels (mobile nav, search).
	$$('[data-toggle]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var el = document.getElementById(btn.dataset.toggle);
			var open = el.hidden || !el.classList.contains('is-open');
			el.hidden = false;
			el.classList.toggle('is-open', open);
			btn.setAttribute('aria-expanded', open);
			if (open && el.querySelector('input')) el.querySelector('input').focus();
		});
	});

	// Subscribe dialog.
	$$('[data-open]').forEach(function (btn) {
		btn.addEventListener('click', function () { document.getElementById(btn.dataset.open).showModal(); });
	});
	$$('dialog [data-close]').forEach(function (btn) {
		btn.addEventListener('click', function () { btn.closest('dialog').close(); });
	});

	// Hero slider: native scroll-snap, arrows scroll one slide, autoplay every 6s.
	$$('[data-slider]').forEach(function (root) {
		var track = $('.av-hero__track', root);
		var go = function (dir) {
			var w = track.clientWidth, max = track.scrollWidth - w;
			var x = track.scrollLeft + dir * w;
			track.scrollTo({ left: x > max + 5 ? 0 : (x < -5 ? max : x), behavior: 'smooth' });
		};
		var prev = $('[data-prev]', root), next = $('[data-next]', root);
		if (prev) prev.addEventListener('click', function () { go(-1); });
		if (next) {
			next.addEventListener('click', function () { go(1); });
			var timer = setInterval(function () { go(1); }, 6000);
			root.addEventListener('pointerenter', function () { clearInterval(timer); });
		}
	});

	// Rails: progress bar follows horizontal scroll.
	$$('.av-rail').forEach(function (rail) {
		var track = $('.av-rail__track', rail), bar = $('.av-rail__bar span', rail);
		var update = function () {
			var ratio = track.clientWidth / track.scrollWidth;
			bar.style.width = Math.max(ratio, .15) * 100 + '%';
			bar.style.marginLeft = (track.scrollLeft / track.scrollWidth) * 100 + '%';
			rail.classList.toggle('is-static', ratio >= .99);
		};
		track.addEventListener('scroll', update, { passive: true });
		window.addEventListener('resize', update);
		update();
	});

	// Share buttons.
	$$('[data-copy]').forEach(function (b) {
		b.addEventListener('click', function () {
			navigator.clipboard && navigator.clipboard.writeText(b.dataset.copy).then(function () { b.textContent = '✓'; });
		});
	});
	$$('[data-share]').forEach(function (b) {
		b.addEventListener('click', function () {
			if (navigator.share) navigator.share({ url: b.dataset.share, title: document.title });
			else window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(b.dataset.share));
		});
	});

	// Toast after form submit.
	var toast = $('.av-toast');
	if (toast) setTimeout(function () { toast.remove(); }, 5000);
})();
