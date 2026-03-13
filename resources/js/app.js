import Swal from 'sweetalert2';
window.Swal = Swal;

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
