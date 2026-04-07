import Swal from 'sweetalert2';
window.Swal = Swal;

// Cuando la sesión expira, Livewire recibe un 401 en lugar de redirigir a /livewire/update.
// Hacemos reload completo para que el usuario vea el login normalmente.
document.addEventListener('livewire:init', () => {
    Livewire.hook('request', ({ fail }) => {
        fail(({ status, preventDefault }) => {
            if (status === 401) {
                preventDefault();
                window.location.reload();
            }
        });
    });
});

import './utils/input-formatter';
import { initDashboardCharts } from './admin/dashboard-charts';

// Usar una variable global para evitar duplicados al navegar con Livewire
window.dashboardChartsInitialized = false;

document.addEventListener('livewire:navigated', () => {
    // Forzamos el reinicio en cada navegación para evitar que queden rastros antiguos
    if (document.querySelector('#chart-balance')) {
        initDashboardCharts();
    }
});

// Carga inicial
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('#chart-balance')) {
        initDashboardCharts();
    }
});
