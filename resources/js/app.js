import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {

    // ==================
    // Toast notifications
    // ==================
    document.querySelectorAll('.toast-auto').forEach(toast => {
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
    const sidebar        = document.getElementById('sidebar');
    const overlay        = document.getElementById('sidebar-overlay');

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
        document.documentElement.classList.toggle('dark', localStorage.getItem('darkMode') === 'true');
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
            if (!confirm(btn.dataset.confirm || 'Apakah Anda yakin?')) {
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
            timer = setTimeout(() => searchInput.closest('form')?.submit(), 600);
        });
    }
});
