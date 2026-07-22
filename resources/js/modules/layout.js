export function initSidebar() {
    const toggleBtn = document.getElementById('toggleSidebarBtn');
    const sidebar = document.getElementById('sidebarMenu');
    const mainContent = document.getElementById('mainContent');

    if (!toggleBtn || !sidebar || !mainContent) return;

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
    });
}

export function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const icons = {
        success: '<i class="fa-solid fa-circle-check text-success fs-5"></i>',
        error: '<i class="fa-solid fa-triangle-exclamation text-danger fs-5"></i>',
        warning: '<i class="fa-solid fa-clock text-warning fs-5"></i>',
        info: '<i class="fa-solid fa-circle-info text-info fs-5"></i>',
    };

    const id = 'toast-' + Date.now();
    const html = `
        <div id="${id}" class="toast toast-bsmart" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
            <div class="toast-body">
                ${icons[type] || icons.info}
                <span class="flex-grow-1 text-dark fw-semibold">${message}</span>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);

    const el = document.getElementById(id);
    const toast = new bootstrap.Toast(el);
    toast.show();

    el.addEventListener('hidden.bs.toast', () => el.remove());
}

export function convertAlertsToToasts() {
    document.querySelectorAll('.alert[role="alert"]').forEach(el => {
        if (el.classList.contains('d-none') || el.style.display === 'none' || el.closest('.modal')) return;
        const text = el.querySelector('.fw-semibold')?.innerText || el.innerText.trim();
        if (!text) return;
        if (el.classList.contains('alert-success')) showToast(text, 'success');
        else if (el.classList.contains('alert-danger')) showToast(text, 'error');
        else if (el.classList.contains('alert-warning')) showToast(text, 'warning');
        else showToast(text, 'info');
        el.style.display = 'none';
    });
}

export function initNotifications() {
    const badge = document.getElementById('notifBadge');
    const container = document.getElementById('notifContainer');
    if (!badge || !container) return;

    function fetchUnread() {
        fetch('/notifications/fetch-unread')
            .then(r => r.json())
            .then(data => {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline-block';

                    let html = '';
                    data.notifications.forEach(n => {
                        html += `
                            <div class="dropdown-item px-2 py-2 border-bottom" style="cursor: pointer; font-size: 12px;" data-notif-id="${n.id}">
                                <div class="fw-semibold text-dark">${n.title}</div>
                                <div class="text-muted small">${n.message}</div>
                                <div class="text-muted mt-1" style="font-size: 10px;">${n.created_at}</div>
                            </div>
                        `;
                    });
                    container.innerHTML = html;

                    container.querySelectorAll('[data-notif-id]').forEach(el => {
                        el.addEventListener('click', function() {
                            markAsRead(this.dataset.notifId);
                        });
                    });
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(err => console.error('Failed to fetch notifications:', err));
    }

    function markAsRead(id) {
        fetch('/notifications/' + id + '/read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Content-Type': 'application/json'
            }
        }).then(() => {
            fetchUnread();
        });
    }

    fetchUnread();
    setInterval(fetchUnread, 30000);
}
