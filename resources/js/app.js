import './bootstrap';
import { initLeadIndexPage } from './pages/leads/index';
import { initLeadKanbanPage } from './pages/leads/kanban';

function initSubmitButtonGuard() {
    let lastSubmitter = null;

    document.addEventListener('click', function (event) {
        const submitter = event.target.closest('button[type="submit"], input[type="submit"]');

        if (submitter && submitter.form) {
            lastSubmitter = submitter;
        }
    });

    document.addEventListener('submit', function (event) {
        if (event.defaultPrevented) {
            return;
        }

        const form = event.target;
        const submitter = event.submitter || lastSubmitter;

        if (!(form instanceof HTMLFormElement) || form.dataset.submitting === 'true') {
            event.preventDefault();
            return;
        }

        form.dataset.submitting = 'true';

        if (submitter && submitter.form === form && submitter.name && !submitter.disabled) {
            const hiddenSubmitter = document.createElement('input');
            hiddenSubmitter.type = 'hidden';
            hiddenSubmitter.name = submitter.name;
            hiddenSubmitter.value = submitter.value;
            form.appendChild(hiddenSubmitter);
        }

        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (button) {
            button.disabled = true;
            button.setAttribute('aria-disabled', 'true');
        });
    });
}

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

    initSubmitButtonGuard();
    initLeadIndexPage();
    initLeadKanbanPage();
});
