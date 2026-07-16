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
