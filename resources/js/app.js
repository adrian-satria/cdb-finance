import './bootstrap';
import { initSidebar, initNotifications } from './modules/layout';
import { initLoginForm } from './modules/login';

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initNotifications();
    initLoginForm();
});
