import '../css/app.css';
import { setupAdvancedFilters } from './report-filters.js';

const palette = ['#f97316', '#3b82f6', '#16a34a', '#64748b', '#dc2626', '#0f766e'];

function parseData(element, key, fallback) {
    try {
        return JSON.parse(element.dataset[key] || JSON.stringify(fallback));
    } catch {
        return fallback;
    }
}

function chartContext(canvas) {
    const bounds = canvas.getBoundingClientRect();
    const ratio = window.devicePixelRatio || 1;
    canvas.width = Math.max(1, Math.floor(bounds.width * ratio));
    canvas.height = Math.max(1, Math.floor(bounds.height * ratio));
    const context = canvas.getContext('2d');
    context.scale(ratio, ratio);
    return { context, width: bounds.width, height: bounds.height };
}

function drawAxis(context, width, height, maximum) {
    const left = 64;
    const right = 12;
    const top = 16;
    const bottom = 40;
    const plotHeight = height - top - bottom;
    context.font = '11px Inter, sans-serif';
    context.textAlign = 'right';
    context.textBaseline = 'middle';
    for (let tick = 0; tick <= 4; tick += 1) {
        const y = top + (plotHeight * tick) / 4;
        context.strokeStyle = '#e2e8f0';
        context.beginPath();
        context.moveTo(left, y);
        context.lineTo(width - right, y);
        context.stroke();
        context.fillStyle = '#64748b';
        context.fillText(new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 1 }).format(maximum * (4 - tick) / 4), left - 8, y);
    }
    return { left, right, top, bottom, plotHeight, plotWidth: width - left - right };
}

function drawBar(canvas, root, labels, values, refunds) {
    const { context, width, height } = chartContext(canvas);
    const maxValue = Math.max(1, ...values.map(Number), ...refunds.map(Number));
    const axis = drawAxis(context, width, height, maxValue);
    const step = axis.plotWidth / Math.max(1, values.length);
    const hasRefunds = refunds.some((value) => Number(value) > 0);
    const format = new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 0 });

    values.forEach((value, index) => {
        const groupWidth = Math.min(48, step * 0.68);
        const barWidth = Math.max(2, hasRefunds ? groupWidth / 2 - 2 : groupWidth);
        const center = axis.left + step * (index + 0.5);
        const barHeight = (axis.plotHeight * Number(value)) / maxValue;
        context.fillStyle = '#f97316';
        context.fillRect(center - (hasRefunds ? groupWidth / 2 : barWidth / 2), axis.top + axis.plotHeight - barHeight, barWidth, barHeight);
        if (hasRefunds && Number(refunds[index]) > 0) {
            const refundHeight = (axis.plotHeight * Number(refunds[index])) / maxValue;
            context.fillStyle = '#dc2626';
            context.fillRect(center + 2, axis.top + axis.plotHeight - refundHeight, barWidth, refundHeight);
        }
        if (index % Math.max(1, Math.ceil(labels.length / 10)) === 0 || index === labels.length - 1) {
            context.fillStyle = '#64748b';
            context.font = '10px Inter, sans-serif';
            context.textAlign = 'center';
            context.textBaseline = 'top';
            context.fillText(String(labels[index] || '').slice(5), center, height - axis.bottom + 12);
        }
    });
    canvas.setAttribute('aria-label', `${root.dataset.chartLabel}: ${format.format(values.reduce((sum, value) => sum + Number(value || 0), 0))}`);
}

function drawLine(canvas, labels, values) {
    const { context, width, height } = chartContext(canvas);
    const maxValue = Math.max(1, ...values.map(Number));
    const axis = drawAxis(context, width, height, maxValue);
    const step = axis.plotWidth / Math.max(1, values.length);
    const points = values.map((value, index) => ({
        x: axis.left + step * (index + 0.5),
        y: axis.top + axis.plotHeight * (1 - Number(value) / maxValue),
    }));
    context.strokeStyle = '#f4511e';
    context.lineWidth = 3;
    context.beginPath();
    points.forEach((point, index) => index === 0 ? context.moveTo(point.x, point.y) : context.lineTo(point.x, point.y));
    context.stroke();
    points.forEach((point, index) => {
        context.fillStyle = '#f4511e';
        context.beginPath();
        context.arc(point.x, point.y, 4, 0, Math.PI * 2);
        context.fill();
        if (index % Math.max(1, Math.ceil(labels.length / 6)) === 0 || index === labels.length - 1) {
            context.fillStyle = '#64748b';
            context.font = '10px Inter, sans-serif';
            context.textAlign = 'center';
            context.textBaseline = 'top';
            context.fillText(String(labels[index] || '').slice(5), point.x, height - axis.bottom + 12);
        }
    });
}

function drawDonut(canvas, root, status) {
    const { context, width, height } = chartContext(canvas);
    const entries = Object.entries(status).filter(([, value]) => Number(value) > 0);
    const total = entries.reduce((sum, [, value]) => sum + Number(value), 0);
    if (!total) return;
    const centerX = width / 2;
    const centerY = height / 2;
    const radius = Math.min(width, height) * 0.38;
    let angle = -Math.PI / 2;
    const statusColors = { completed: '#f97316', pending: '#0f766e', failed: '#2563eb', refunded: '#dc2626', active: '#0f766e', used: '#2563eb', cancelled: '#64748b', scheduled: '#2563eb', departed: '#f97316', arrived: '#16a34a' };
    entries.forEach(([key, value], index) => {
        const sweep = (Number(value) / total) * Math.PI * 2;
        context.beginPath();
        context.arc(centerX, centerY, radius, angle, angle + sweep);
        context.arc(centerX, centerY, radius * 0.58, angle + sweep, angle, true);
        context.closePath();
        context.fillStyle = ['revenue', 'overview'].includes(root.dataset.reportType)
            ? palette[index % palette.length]
            : (statusColors[key] || palette[index % palette.length]);
        context.fill();
        angle += sweep;
    });
    context.textAlign = 'center';
    context.textBaseline = 'middle';
    context.fillStyle = '#64748b';
    context.font = '12px Inter, sans-serif';
    const isRevenue = ['revenue', 'overview'].includes(root.dataset.reportType);
    context.fillText(isRevenue ? root.dataset.totalRevenueLabel : root.dataset.totalCountLabel, centerX, centerY - 10);
    context.fillStyle = '#172033';
    context.font = 'bold 16px Inter, sans-serif';
    const displayTotal = isRevenue
        ? `${new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 2 }).format(total / 1_000_000)} ${root.dataset.millionUnit}`
        : new Intl.NumberFormat('vi-VN').format(total);
    context.fillText(displayTotal, centerX, centerY + 13);
}

function renderCharts() {
    document.querySelectorAll('[data-reporting-chart]').forEach((root) => {
        const labels = parseData(root, 'labels', []);
        const values = parseData(root, 'values', []);
        const counts = parseData(root, 'counts', []);
        const refunds = parseData(root, 'refunds', []);
        const status = parseData(root, 'status', {});
        root.querySelectorAll('[data-report-bar]').forEach((canvas) => drawBar(canvas, root, labels, values, refunds));
        root.querySelectorAll('[data-report-line]').forEach((canvas) => drawLine(canvas, labels, counts));
        root.querySelectorAll('[data-report-donut]').forEach((canvas) => drawDonut(canvas, root, status));
    });
}

function setupFilters() {
    const period = document.querySelector('[data-report-period]');
    const dates = [...document.querySelectorAll('[data-report-date]')];
    if (!period || dates.length !== 2) return;
    const syncDateFields = () => {
        document.querySelectorAll('[data-report-date-field]').forEach((field) => {
            field.hidden = period.value !== 'custom';
        });
    };
    dates.forEach((input) => input.addEventListener('change', () => {
        period.value = 'custom';
        period.dispatchEvent(new Event('change', { bubbles: true }));
    }));
    period.addEventListener('change', () => {
        syncDateFields();
        if (period.value === 'custom') return;
        const end = new Date();
        const start = new Date(end);
        if (period.value === '7days') start.setDate(start.getDate() - 6);
        if (period.value === '30days') start.setDate(start.getDate() - 29);
        const toDate = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
        dates[0].value = toDate(period.value === 'today' ? end : start);
        dates[1].value = toDate(end);
    });
}

function setupExports() {
    document.querySelectorAll('[data-report-export]').forEach((button) => button.addEventListener('click', async () => {
        button.disabled = true;
        try {
            const response = await fetch(button.dataset.reportExport, { credentials: 'same-origin', headers: { Accept: 'application/json, application/pdf' } });
            if (!response.ok) {
                let message = button.dataset.exportError;
                try { message = (await response.json()).message || message; } catch { /* Keep the localized fallback. */ }
                throw new Error(message);
            }
            const blob = await response.blob();
            const disposition = response.headers.get('Content-Disposition') || '';
            const filename = disposition.match(/filename="?([^";]+)"?/i)?.[1] || 'futabus-report';
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            link.click();
            URL.revokeObjectURL(link.href);
            window.FutaNotify?.success(button.dataset.exportSuccess, { key: 'report-export' });
        } catch (error) {
            window.FutaNotify?.error(error.message || button.dataset.exportError, { key: 'report-export', duration: 8000 });
        } finally {
            button.disabled = false;
        }
    }));
}

document.addEventListener('DOMContentLoaded', () => {
    renderCharts();
    setupFilters();
    setupAdvancedFilters();
    setupExports();
});
window.addEventListener('resize', renderCharts);
