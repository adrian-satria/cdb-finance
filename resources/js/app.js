import './bootstrap';
import { initSidebar, initNotifications, convertAlertsToToasts } from './modules/layout';
import { initLoginForm } from './modules/login';
import { initFormLoading } from './modules/spp';

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initNotifications();
    initLoginForm();
    initFormLoading();
    convertAlertsToToasts();
});
