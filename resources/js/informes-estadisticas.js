import Chart from 'chart.js/auto';

function readJsonDataset(el, key, fallback) {
    if (!el) {
        return fallback;
    }
    const raw = el.getAttribute(key);
    if (!raw) {
        return fallback;
    }
    try {
        return JSON.parse(raw);
    } catch {
        return fallback;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const dataEl = document.getElementById('estadisticas-charts-data');
    if (!dataEl) {
        return;
    }

    const piramide = readJsonDataset(dataEl, 'data-piramide', []);
    const flujo = readJsonDataset(dataEl, 'data-flujo', []);

    const primary = '#0f172a';
    const accent = '#c6a16a';
    const muted = '#94a3b8';

    const elP = document.getElementById('chart-piramide');
    if (elP && piramide.length) {
        new Chart(elP, {
            type: 'bar',
            data: {
                labels: piramide.map((r) => r.etiqueta),
                datasets: [
                    {
                        label: 'Hermanos',
                        data: piramide.map((r) => r.total),
                        backgroundColor: piramide.map((_, i) =>
                            i % 2 === 0 ? `${primary}cc` : `${accent}aa`
                        ),
                        borderColor: primary,
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    x: {
                        ticks: { font: { size: 9 }, maxRotation: 45, minRotation: 0, color: muted },
                        grid: { display: false },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, color: muted },
                        grid: { color: '#e2e8f0' },
                    },
                },
            },
        });
    }

    const elF = document.getElementById('chart-flujo');
    if (elF && flujo.length) {
        new Chart(elF, {
            type: 'bar',
            data: {
                labels: flujo.map((r) => String(r.año)),
                datasets: [
                    {
                        label: 'Altas',
                        data: flujo.map((r) => r.altas),
                        backgroundColor: '#059669aa',
                        borderColor: '#047857',
                        borderWidth: 1,
                    },
                    {
                        label: 'Bajas',
                        data: flujo.map((r) => r.bajas),
                        backgroundColor: '#ea580caa',
                        borderColor: '#c2410c',
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                },
                scales: {
                    x: {
                        stacked: false,
                        ticks: { color: muted },
                        grid: { display: false },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, color: muted },
                        grid: { color: '#e2e8f0' },
                    },
                },
            },
        });
    }
});
