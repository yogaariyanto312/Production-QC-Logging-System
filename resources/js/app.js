import './bootstrap';

// =====================
// Auto-calculate total
// =====================
function calculateTotal() {
    const shift1 = parseInt(document.getElementById('shift1_qty')?.value) || 0;
    const shift2 = parseInt(document.getElementById('shift2_qty')?.value) || 0;
    const shift3 = parseInt(document.getElementById('shift3_qty')?.value) || 0;
    const total  = shift1 + shift2 + shift3;

    const display = document.getElementById('total_display');
    if (display) {
        display.textContent = total.toLocaleString('id-ID') + ' unit';
        display.classList.toggle('text-green-600', total > 0);
        display.classList.toggle('text-gray-400', total === 0);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Attach shift quantity listeners
    ['shift1_qty', 'shift2_qty', 'shift3_qty'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', calculateTotal);
    });
    calculateTotal(); // Run once on load

    // ==================
    // Toast notifications
    // ==================
    const toasts = document.querySelectorAll('.toast-auto');
    toasts.forEach(toast => {
        toast.classList.add('toast-enter');
        setTimeout(() => {
            toast.classList.add('toast-leave');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    });

    // ==================
    // Sidebar toggle
    // ==================
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            overlay?.classList.toggle('hidden');
        });
        overlay?.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    }

    // ==================
    // Dark mode toggle
    // ==================
    const darkToggle = document.getElementById('dark-toggle');
    if (darkToggle) {
        const isDark = localStorage.getItem('darkMode') === 'true';
        document.documentElement.classList.toggle('dark', isDark);

        darkToggle.addEventListener('click', () => {
            const dark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', dark);
        });
    }

    // ==================
    // Confirm delete
    // ==================
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const msg = btn.dataset.confirm || 'Apakah Anda yakin?';
            if (!confirm(msg)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });

    // ==================
    // Real-time search (debounce)
    // ==================
    const searchInput = document.getElementById('search-realtime');
    if (searchInput) {
        let timer;
        searchInput.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                searchInput.closest('form')?.submit();
            }, 600);
        });
    }
});

// Export to window for inline use
window.calculateTotal = calculateTotal;
