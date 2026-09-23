import { createIcons, icons } from 'lucide';

window.lucide = { createIcons, icons };

document.addEventListener('DOMContentLoaded', () => {
    // 1. Initialize Lucide Icons
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

    // 2. Mobile Navigation Drawer Controller (Opens from RIGHT)
    const mobileNavBtn = document.getElementById('mobile-nav-btn');
    const mobileNavDrawer = document.getElementById('mobile-nav-drawer');
    const mobileNavBackdrop = document.getElementById('mobile-nav-backdrop');
    const mobileDrawerCloseBtn = document.getElementById('mobile-drawer-close-btn');

    function openMobileMenu() {
        if (!mobileNavDrawer) return;
        mobileNavDrawer.classList.add('is-open');
        if (mobileNavBackdrop) mobileNavBackdrop.classList.add('is-open');
        if (mobileNavBtn) mobileNavBtn.setAttribute('aria-expanded', 'true');
        mobileNavDrawer.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileMenu() {
        if (!mobileNavDrawer) return;
        mobileNavDrawer.classList.remove('is-open');
        if (mobileNavBackdrop) mobileNavBackdrop.classList.remove('is-open');
        if (mobileNavBtn) mobileNavBtn.setAttribute('aria-expanded', 'false');
        mobileNavDrawer.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    if (mobileNavBtn && mobileNavDrawer) {
        mobileNavBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (mobileNavDrawer.classList.contains('is-open')) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });

        if (mobileDrawerCloseBtn) {
            mobileDrawerCloseBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                closeMobileMenu();
            });
        }

        if (mobileNavBackdrop) {
            mobileNavBackdrop.addEventListener('click', () => {
                closeMobileMenu();
            });
        }

        // Close on navigation link click
        mobileNavDrawer.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                closeMobileMenu();
            });
        });

        // Close on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && mobileNavDrawer.classList.contains('is-open')) {
                closeMobileMenu();
                mobileNavBtn.focus();
            }
        });
    }

    // 3. Custom Accessible Vehicle Dropdown Controller
    const vehicleSelectGroup = document.getElementById('vehicle-select-group');
    const vehicleSelectBtn = document.getElementById('vehicle-select-btn');
    const vehicleSelectMenu = document.getElementById('vehicle-select-menu');
    const vehicleSelectDisplay = document.getElementById('vehicle-select-display');
    const selectedVehicleInput = document.getElementById('selected-vehicle-id');

    if (vehicleSelectBtn && vehicleSelectMenu && selectedVehicleInput) {
        const options = Array.from(vehicleSelectMenu.querySelectorAll('.custom-select-option'));

        function openDropdown() {
            vehicleSelectMenu.classList.add('is-open');
            vehicleSelectBtn.classList.add('is-open');
            vehicleSelectBtn.setAttribute('aria-expanded', 'true');

            // Viewport boundary guard: flip upwards if overflowing screen bottom
            const rect = vehicleSelectBtn.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            if (spaceBelow < 280 && rect.top > 280) {
                vehicleSelectMenu.classList.add('open-up');
            } else {
                vehicleSelectMenu.classList.remove('open-up');
            }

            // Focus currently selected option or first option
            const selectedOpt = vehicleSelectMenu.querySelector('.custom-select-option.selected') || options[0];
            if (selectedOpt) {
                selectedOpt.focus();
            }
        }

        function closeDropdown() {
            vehicleSelectMenu.classList.remove('is-open');
            vehicleSelectMenu.classList.remove('open-up');
            vehicleSelectBtn.classList.remove('is-open');
            vehicleSelectBtn.setAttribute('aria-expanded', 'false');
        }

        function selectOption(opt) {
            if (!opt) return;
            const val = opt.getAttribute('data-value') || '';
            const name = opt.getAttribute('data-name') || 'Select a vehicle';

            selectedVehicleInput.value = val;
            if (vehicleSelectDisplay) {
                vehicleSelectDisplay.textContent = name;
            }

            // Update selected class and aria-selected state
            options.forEach((item) => {
                item.classList.remove('selected');
                item.setAttribute('aria-selected', 'false');
            });
            opt.classList.add('selected');
            opt.setAttribute('aria-selected', 'true');

            // Trigger change event for form synchronization
            selectedVehicleInput.dispatchEvent(new Event('change', { bubbles: true }));

            closeDropdown();
            vehicleSelectBtn.focus();
        }

        // Toggle open/close on button click
        vehicleSelectBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (vehicleSelectMenu.classList.contains('is-open')) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });

        // Option click handlers
        options.forEach((opt, idx) => {
            opt.addEventListener('click', (e) => {
                e.stopPropagation();
                selectOption(opt);
            });

            opt.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    selectOption(opt);
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    const next = options[idx + 1] || options[0];
                    next.focus();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    const prev = options[idx - 1] || options[options.length - 1];
                    prev.focus();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    closeDropdown();
                    vehicleSelectBtn.focus();
                }
            });
        });

        // Trigger button keyboard navigation
        vehicleSelectBtn.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openDropdown();
            } else if (e.key === 'Escape') {
                closeDropdown();
            }
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (vehicleSelectGroup && !vehicleSelectGroup.contains(e.target)) {
                closeDropdown();
            }
        });
    }

    // 4. Admin Sidebar Toggle (Mobile)
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
