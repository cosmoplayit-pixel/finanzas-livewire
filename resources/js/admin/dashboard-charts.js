/**
 * Dashboard Charts logic.
 */
export function initDashboardCharts() {
    // Usar window para que las instancias persistan y podamos destruirlas antes de recrearlas
    if (!window.dashboardChartInstances) {
        window.dashboardChartInstances = {
            balance: null,
            activos: null,
            deudas: null,
            patrimonio: null
        };
    }
    const charts = window.dashboardChartInstances;

    const initCharts = () => {
        const balanceEl = document.querySelector("#chart-balance");
        const activosEl = document.querySelector("#chart-activos");
        const deudasEl = document.querySelector("#chart-deudas");
        const patrimonioEl = document.querySelector("#chart-patrimonio");

        const balanceData = document.querySelector("#data-balance");
        const activosData = document.querySelector("#data-activos");
        const deudasData = document.querySelector("#data-deudas");
        const patrimonioData = document.querySelector("#data-patrimonio");

        if (!balanceEl || !activosEl || !deudasEl || !patrimonioEl || !balanceData || !activosData || !
            deudasData || !patrimonioData) return;

        // Destruir si ya existen
        if (charts.balance) charts.balance.destroy();
        if (charts.activos) charts.activos.destroy();
        if (charts.deudas) charts.deudas.destroy();
        if (charts.patrimonio) charts.patrimonio.destroy();

        const totalActivos = parseFloat(balanceData.dataset.activos);
        const totalDeudas = parseFloat(balanceData.dataset.deudas);
        const totalPatrimonio = parseFloat(balanceData.dataset.patrimonio);
        const activosVals = JSON.parse(activosData.dataset.vals);
        const deudasVals = JSON.parse(deudasData.dataset.vals);
        const patrimonioVals = JSON.parse(patrimonioData.dataset.vals);
        const totalActivosTxt = activosData.dataset.total;
        const totalDeudasTxt = deudasData.dataset.total;
        const totalPatrimonioTxt = patrimonioData.dataset.total;

        // 1. Balance General
        charts.balance = new ApexCharts(balanceEl, {
            chart: {
                height: 250,
                type: 'bar',
                toolbar: {
                    show: false
                },
                fontFamily: 'Inter, ui-sans-serif, system-ui'
            },
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '55%',
                    distributed: true
                }
            },
            dataLabels: {
                enabled: false
            },
            colors: ['#3b82f6', '#ef4444', '#10b981'],
            series: [{
                name: 'Monto Consolidado (Bs)',
                data: [{
                        x: 'Activos',
                        y: totalActivos
                    },
                    {
                        x: 'Deudas',
                        y: totalDeudas
                    },
                    {
                        x: 'Patrimonio',
                        y: totalPatrimonio
                    }
                ]
            }],
            xaxis: {
                categories: ['C.C.', 'Deudas', 'Patrimonio'],
                labels: {
                    style: {
                        colors: '#94a3b8',
                        fontSize: '11px',
                        fontWeight: 600
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#94a3b8'
                    },
                    formatter: (v) => v.toLocaleString()
                }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4
            },
            tooltip: {
                y: {
                    formatter: (val) => val.toLocaleString() + ' Bs'
                }
            }
        });

        // 2. Activos Donut
        charts.activos = new ApexCharts(activosEl, {
            chart: {
                height: 260,
                type: 'donut',
                fontFamily: 'Inter, ui-sans-serif, system-ui'
            },
            series: activosVals,
            labels: ['Efectivo', 'Bancos', 'Proyectos', 'Boletas', 'Agentes'],
            colors: ['#0d9488', '#4f46e5', '#f59e0b', '#be185d', '#a299b1ff'],
            legend: {
                position: 'bottom',
                fontSize: '11px',
                fontWeight: 600
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Activos (Bs)',
                                fontSize: '13px',
                                formatter: () => totalActivosTxt
                            }
                        }
                    }
                }
            },
            dataLabels: {
                enabled: false
            }
        });

        // 3. Deudas Donut
        charts.deudas = new ApexCharts(deudasEl, {
            chart: {
                height: 260,
                type: 'donut',
                fontFamily: 'Inter, ui-sans-serif, system-ui'
            },
            series: deudasVals,
            labels: ['Privados', 'Bancos', 'Impuestos'],
            colors: ['#ef4444', '#06b6d4', '#8b5cf6'],
            legend: {
                position: 'bottom',
                fontSize: '11px',
                fontWeight: 600
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Deudas (Bs)',
                                fontSize: '13px',
                                formatter: () => totalDeudasTxt
                            }
                        }
                    }
                }
            },
            dataLabels: {
                enabled: false
            }
        });

        // 4. Patrimonio Donut
        charts.patrimonio = new ApexCharts(patrimonioEl, {
            chart: {
                height: 260,
                type: 'donut',
                fontFamily: 'Inter, ui-sans-serif, system-ui'
            },
            series: patrimonioVals,
            labels: ['Herramientas', 'Materiales', 'Mobiliario', 'Vehículos', 'Inmuebles'],
            colors: ['#10b981', '#e05e58ff', '#2764b9ff', '#e2e44eff', '#9e1cd5ff'],
            legend: {
                position: 'bottom',
                fontSize: '11px',
                fontWeight: 600
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Patrimonio (Bs)',
                                fontSize: '13px',
                                formatter: () => totalPatrimonioTxt
                            }
                        }
                    }
                }
            },
            dataLabels: {
                enabled: false
            }
        });

        charts.balance.render();
        charts.activos.render();
        charts.deudas.render();
        charts.patrimonio.render();
    };

    // Al cargar
    initCharts();

    // Hook de Livewire para detectar cuando se actualiza el componente
    Livewire.on('charts-updated', () => {
        // Darle un tiempo pequeño para que el DOM se actualice con los nuevos data-attributes
        setTimeout(initCharts, 20);
    });
}
