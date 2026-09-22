import { createIcons, icons } from 'lucide';

window.lucide = { createIcons, icons };

document.addEventListener('DOMContentLoaded', () => {
    // Initialize Lucide Icons
    try {
        createIcons({
            icons,
            attrs: {
                'stroke-width': 2,
                'stroke-linecap': 'round',
                'stroke-linejoin': 'round'
            }
        });
    } catch (e) {
        console.error('Lucide init error:', e);
    }

    // Mobile Navigation Toggle
    const mobileNavBtn = document.getElementById('mobile-nav-btn');
    const mobileNavMenu = document.getElementById('nav-links-menu') || document.getElementById('mobile-nav-menu');
    if (mobileNavBtn && mobileNavMenu) {
        mobileNavBtn.addEventListener('click', () => {
            mobileNavMenu.classList.toggle('show');
            mobileNavMenu.classList.toggle('active');
        });
    }

    // Admin Sidebar Toggle (Mobile)
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const adminSidebar = document.getElementById('adminSidebar');
    const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
    if (sidebarToggleBtn && adminSidebar) {
        sidebarToggleBtn.addEventListener('click', () => {
            adminSidebar.classList.toggle('active');
        });
    }
    if (sidebarCloseBtn && adminSidebar) {
        sidebarCloseBtn.addEventListener('click', () => {
            adminSidebar.classList.remove('active');
        });
    }
});
