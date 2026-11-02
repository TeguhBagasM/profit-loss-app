import {
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    Legend,
    LinearScale,
    Tooltip,
} from 'chart.js';

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend);

const dataElement = document.querySelector('[data-chart]');
const canvas = document.getElementById('dashboard-chart');

if (dataElement && canvas) {
    const data = JSON.parse(dataElement.dataset.chart);
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const formatNumber = new Intl.NumberFormat('id-ID');

    // Konversi string uang dari service ke angka HANYA untuk tampilan grafik.
    const toNumber = (value) => Number(value);

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Income',
                    data: data.income.map(toNumber),
                    backgroundColor: '#0F766E',
                    borderWidth: 0,
                },
                {
                    label: 'Expense',
                    data: data.expense.map(toNumber),
                    backgroundColor: '#B3354F',
                    borderWidth: 0,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: reduceMotion ? false : undefined,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#5B6675',
                        boxWidth: 12,
                        boxHeight: 12,
                    },
                },
                tooltip: {
                    callbacks: {
                        label: (context) => `${context.dataset.label}: ${formatNumber.format(context.parsed.y)}`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { color: '#E2E6EC', drawTicks: false },
                    ticks: { color: '#5B6675' },
                },
                y: {
                    grid: { color: '#E2E6EC' },
                    border: { display: false },
                    ticks: {
                        color: '#5B6675',
                        callback: (value) => formatNumber.format(value),
                    },
                },
            },
        },
    });
}
