// Vaishnavi Tours — Client Application Entry Point

document.addEventListener('DOMContentLoaded', () => {
    // Mobile Navigation Toggle
    const mobileNavBtn = document.getElementById('mobile-nav-btn');
    const mobileNavMenu = document.getElementById('mobile-nav-menu');
    if (mobileNavBtn && mobileNavMenu) {
        mobileNavBtn.addEventListener('click', () => {
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
