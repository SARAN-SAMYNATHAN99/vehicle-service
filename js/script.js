/**
 * GearShift - Global UI Behaviors & Landing Page Scripts
 * Pure Vanilla JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Menu Toggle
    const navToggle = document.querySelector('.mobile-nav-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            navToggle.setAttribute('aria-expanded', navMenu.classList.contains('active'));
        });
    }

    // 2. Dynamic Copyright Year
    const yearEls = document.querySelectorAll('.dynamic-year');
    const currentYear = new Date().getFullYear();
    yearEls.forEach(el => el.textContent = currentYear);

    // 3. Auto-load home services if container exists
    const homeServicesContainer = document.getElementById('home-services-container');
    if (homeServicesContainer && typeof loadServicesCatalog === 'function') {
        loadServicesCatalog('home-services-container', 4);
    }

    // 4. Check auth state on global pages to set navbar links
    if (typeof checkAuthState === 'function') {
        checkAuthState();
    }
});
