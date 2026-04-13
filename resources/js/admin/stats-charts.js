function baseTooltipCallbacks(config) {
    return {
        title(items) {
            const item = items[0];

            return config.fullLabels?.[item.dataIndex] ?? config.labels?.[item.dataIndex] ?? '';
        },
        label(context) {
            return config.formattedValues?.[context.dataIndex] ?? String(context.parsed?.y ?? context.parsed?.x ?? context.raw ?? '');
        },
    };
}

function lineChartOptions(config) {
    const maxValue = Math.max(...config.values, 0);

    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                display: false,
            },
            tooltip: {
                displayColors: false,
                padding: 12,
                backgroundColor: '#1c1917',
                titleColor: '#fafaf9',
                bodyColor: '#e7e5e4',
                callbacks: baseTooltipCallbacks(config),
            },
        },
        scales: {
            x: {
                grid: {
                    display: false,
                },
                ticks: {
                    color: '#78716c',
                    maxRotation: 0,
                    autoSkipPadding: 18,
                    font: {
                        size: 11,
                        weight: '600',
                    },
                },
                border: {
                    display: false,
                },
            },
            y: {
                beginAtZero: true,
                suggestedMax: maxValue > 0 ? undefined : 1,
                ticks: {
                    color: '#78716c',
                    precision: 0,
                    font: {
                        size: 11,
                        weight: '600',
                    },
                },
                grid: {
                    color: 'rgba(120, 113, 108, 0.16)',
                    drawBorder: false,
                },
                border: {
                    display: false,
                },
            },
        },
        elements: {
            point: {
                hoverRadius: 6,
                radius: config.values.length > 30 ? 2.5 : 3.5,
            },
            line: {
                tension: 0.35,
                borderWidth: 3,
            },
        },
    };
}

function barChartOptions(config) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: {
            legend: {
                display: false,
            },
            tooltip: {
                displayColors: false,
                padding: 12,
                backgroundColor: '#1c1917',
                titleColor: '#fafaf9',
                bodyColor: '#e7e5e4',
                callbacks: baseTooltipCallbacks(config),
            },
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    color: '#78716c',
                    precision: 0,
                    font: {
                        size: 11,
                        weight: '600',
                    },
                },
                grid: {
                    color: 'rgba(120, 113, 108, 0.14)',
                    drawBorder: false,
                },
                border: {
                    display: false,
                },
            },
            y: {
                ticks: {
                    color: '#44403c',
                    font: {
                        size: 12,
                        weight: '700',
                    },
                },
                grid: {
                    display: false,
                },
                border: {
                    display: false,
                },
            },
        },
    };
}

function buildChartDefinition(config) {
    if (config.type === 'bar') {
        return {
            type: 'bar',
            data: {
                labels: config.labels,
                datasets: [
                    {
                        data: config.values,
                        backgroundColor: config.accent,
                        borderRadius: 999,
                        borderSkipped: false,
                        maxBarThickness: 34,
                    },
                ],
            },
            options: barChartOptions(config),
        };
    }

    return {
        type: 'line',
        data: {
            labels: config.labels,
            datasets: [
                {
                    data: config.values,
                    borderColor: config.accent,
                    backgroundColor: config.fill,
                    pointBackgroundColor: config.accent,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    fill: true,
                },
            ],
        },
        options: lineChartOptions(config),
    };
}

function readChartConfig(canvas) {
    const configNode = canvas.parentElement?.querySelector('[data-chart-config]');

    if (!configNode?.textContent) {
        return null;
    }

    try {
        return JSON.parse(configNode.textContent);
    } catch (error) {
        console.error('No se pudo leer la configuracion del grafico de admin.', error);
        return null;
    }
}

export async function initAdminStatsCharts() {
    const canvases = Array.from(document.querySelectorAll('[data-admin-chart]'));

    if (canvases.length === 0) {
        return;
    }

    const { default: Chart } = await import('chart.js/auto');

    canvases.forEach((canvas) => {
        const config = readChartConfig(canvas);

        if (!config) {
            return;
        }

        if (canvas._chartInstance) {
            canvas._chartInstance.destroy();
        }

        canvas._chartInstance = new Chart(
            canvas,
            buildChartDefinition(config),
        );
    });
}
