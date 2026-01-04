var registeredCharts = [];

export function getThemeColors() {
    var styles = getComputedStyle(document.documentElement);
    return {
        grid: 'rgba(255,255,255,0.04)',
        text: styles.getPropertyValue('--color-text-light').trim() || '#949ba2',
        success: styles.getPropertyValue('--color-success').trim() || '#1bbf89',
        primary: styles.getPropertyValue('--color-primary').trim() || '#0F83C9',
        accent: styles.getPropertyValue('--color-accent').trim() || '#f7af3e',
        danger: styles.getPropertyValue('--color-danger').trim() || '#db524b',
        info: styles.getPropertyValue('--color-info').trim() || '#56c0e0',
        warning: styles.getPropertyValue('--color-warning').trim() || '#f7af3e',
        muted: styles.getPropertyValue('--color-muted').trim() || '#949ba2'
    };
}

export function registerChartForThemeUpdates(chart, updateCallback) {
    registeredCharts.push({ chart: chart, update: updateCallback });
}

function updateAllCharts() {
    var colors = getThemeColors();
    registeredCharts.forEach(function(item) {
        if (item.chart && item.update) {
            item.update(item.chart, colors);
        }
    });
}

if (typeof MutationObserver !== 'undefined') {
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'data-theme') {
                setTimeout(updateAllCharts, 50);
            }
        });
    });
    observer.observe(document.documentElement, { attributes: true });
}

export function getBaseChartOptions(colors) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false
        },
        plugins: {
            tooltip: {
                usePointStyle: true,
                boxPadding: 6
            },
            legend: {
                display: true,
                position: 'bottom',
                align: 'center',
                labels: {
                    color: colors.text,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    padding: 20,
                    boxHeight: 8
                }
            }
        },
        layout: {
            padding: 0
        }
    };
}

export function getGridScaleConfig(colors) {
    return {
        grid: {
            color: colors.grid
        },
        ticks: {
            color: colors.text
        }
    };
}
