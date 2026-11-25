function crearGrafico(
    canvasId,
    tipo,
    labels,
    datos,
    colores,
    titulo
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
                label:titulo,
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
                ],
                "Cantidad de jugadores"
            );
        }

        if (window.dashboardData && window.dashboardData.pais) {
            crearGrafico(
                'graficoPais',
                'bar',
                window.dashboardData.pais.labels,
                window.dashboardData.pais.datos,
                [
                    '#198754',
                    '#20c997',
                    '#0dcaf0',
                    '#6f42c1',
                    '#fd7e14',
                    '#dc3545',
                    '#ffc107'
                ],
                "Cantidad de jugadores"
            );
        }

        if (window.dashboardData && window.dashboardData.aciertos) {
            crearGrafico(
                'graficoAciertos',
                'bar',
                window.dashboardData.aciertos.labels,
                window.dashboardData.aciertos.datos,
                [
                    '#dc3545',
                    '#fd7e14',
                    '#ffc107',
                    '#198754'
                ],
                'Cantidad de jugadores'
            );
        }
    }
);