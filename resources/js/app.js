import './bootstrap';
import { initSidebar, initNotifications } from './modules/layout';

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initNotifications();
});
