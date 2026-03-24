import './bootstrap';

document.addEventListener('DOMContentLoaded', function () {
	const storageKey = 'crmSidebarCollapsed';
	const body = document.body;
	const toggleBtn = document.getElementById('sidebarToggle');
	const mobileToggleBtn = document.getElementById('sidebarMobileToggle');
	const backdrop = document.getElementById('crmBackdrop');
	const navLinks = document.querySelectorAll('[data-nav-link]');

	if (localStorage.getItem(storageKey) === 'true') {
		body.classList.add('sidebar-collapsed');
	}

	if (toggleBtn) {
		toggleBtn.addEventListener('click', function () {
			body.classList.toggle('sidebar-collapsed');
			localStorage.setItem(storageKey, body.classList.contains('sidebar-collapsed') ? 'true' : 'false');
		});
	}

	navLinks.forEach(function (link) {
		link.addEventListener('click', function () {
			navLinks.forEach(function (item) {
				item.classList.remove('active');
			});
			link.classList.add('active');
		});
	});

	if (mobileToggleBtn) {
		mobileToggleBtn.addEventListener('click', function () {
			body.classList.toggle('sidebar-mobile-open');
		});
	}

	if (backdrop) {
		backdrop.addEventListener('click', function () {
			body.classList.remove('sidebar-mobile-open');
		});
	}

	window.addEventListener('resize', function () {
		if (window.innerWidth >= 992) {
			body.classList.remove('sidebar-mobile-open');
		}
	});
});
