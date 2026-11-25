function crearGrafico(
    canvasId,
    tipo,
    labels,
    datos,
    colores
) {
    const canvas =
        document.getElementById(canvasId);
    if (!canvas) {
        return;
    }
    new Chart(canvas, {
        type: tipo,
        data: {
            labels: labels,
            datasets: [{
                data: datos,
                backgroundColor: colores,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales:
                tipo === 'bar'
                    ? {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                    : {}
        }
    });
}

document.addEventListener(
    'DOMContentLoaded',
    function () {
        if (window.dashboardData && window.dashboardData.sexo) {
            crearGrafico(
                'graficoSexo',
                'doughnut',
                window.dashboardData.sexo.labels,
                window.dashboardData.sexo.datos,
                [
                    '#198754',
                    '#20c997',
                    '#0dcaf0',
                    '#6f42c1'
                ]
            );
        }

        if (window.dashboardData && window.dashboardData.edad) {
            crearGrafico(
                'graficoEdad',
                'bar',
                window.dashboardData.edad.labels,
                window.dashboardData.edad.datos,
                [
                    '#198754',
                    '#20c997',
                    '#0dcaf0',
                    '#6f42c1',
                    '#fd7e14'
                ]
            );
        }
    }
);