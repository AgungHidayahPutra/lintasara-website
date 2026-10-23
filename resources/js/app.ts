
import { createInertiaApp } from '@inertiajs/vue3';

import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Lintasara';

void createInertiaApp({
    title: (title) =>
        title ? `${title} - ${appName}` : appName,

    layout: (name) => {
        switch (true) {
            // Semua halaman portal berita publik.
            // Masing-masing halaman menggunakan layout publik,
            // bukan AdminLayout.
            case name.startsWith('public/'):
                return null;

            // Halaman autentikasi dengan desain
            // full-screen Lintasara.
            case name === 'auth/Login':
            case name === 'auth/ForgotPassword':
            case name === 'auth/ResetPassword':
            case name === 'auth/ConfirmPassword':
                return null;

            // Halaman autentikasi lainnya.
            case name.startsWith('auth/'):
                return AuthLayout;

            // Pengaturan akun.
            case name.startsWith('settings/'):
                return [AdminLayout, SettingsLayout];

            // Dashboard Admin dan Penulis.
            default:
                return AdminLayout;
        }
    },

    withApp: (app) => {
        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },

    progress: {
        color: '#F97316',
    },
});

// Notifikasi flash.
initializeFlashToast();
