import '../css/app.css';

document.querySelectorAll('[data-reporting-chart]').forEach((root) => {
    const labels = JSON.parse(root.dataset.labels || '[]');
    const values = JSON.parse(root.dataset.values || '[]').map(Number);
    const counts = JSON.parse(root.dataset.counts || '[]').map(Number);
    const refunds = JSON.parse(root.dataset.refunds || '[]').map((value) => Number(value || 0));
    const segments = Object.entries(JSON.parse(root.dataset.status || '{}'));
    const segmentColors = {
        completed: '#f97316',
        pending: '#0f766e',
        failed: '#2563eb',
        refunded: '#dc2626',
        active: '#0f766e',
        used: '#2563eb',
    };
    const palette = ['#f97316', '#0f766e', '#2563eb', '#dc2626', '#7c3aed', '#0891b2'];
    const revenueColors = ['#f97316', '#3b82f6', '#16a34a', '#64748b'];

    const render = (canvas, painter) => {
        if (!canvas) return;

        const paint = () => {
            const bounds = canvas.getBoundingClientRect();
            if (!bounds.width || !bounds.height) return;

            const ratio = window.devicePixelRatio || 1;
            const context = canvas.getContext('2d');
            canvas.width = Math.round(bounds.width * ratio);
            canvas.height = Math.round(bounds.height * ratio);
            context.setTransform(ratio, 0, 0, ratio, 0, 0);
            context.clearRect(0, 0, bounds.width, bounds.height);
            painter(context, bounds.width, bounds.height);
        };

        if ('ResizeObserver' in window) {
            new ResizeObserver(paint).observe(canvas);
        } else {
            window.addEventListener('resize', paint);
        }
        paint();
    };

    const chartFrame = (context, width, height, maxValue) => {
        const padding = { top: 14, right: 12, bottom: 34, left: 56 };
        const plotWidth = Math.max(1, width - padding.left - padding.right);
        const plotHeight = Math.max(1, height - padding.top - padding.bottom);
        context.font = '11px Inter, sans-serif';
        context.textAlign = 'right';
        context.textBaseline = 'middle';

        for (let tick = 0; tick <= 4; tick += 1) {
            const y = padding.top + (plotHeight * tick) / 4;
            context.strokeStyle = '#e2e8f0';
            context.lineWidth = 1;
            context.beginPath();
            context.moveTo(padding.left, y);
            context.lineTo(width - padding.right, y);
            context.stroke();
            context.fillStyle = '#64748b';
            context.fillText(new Intl.NumberFormat().format(maxValue * (4 - tick) / 4), padding.left - 8, y);
        }

        const step = plotWidth / Math.max(labels.length, 1);
        const labelStride = Math.max(1, Math.ceil(labels.length / 6));
        context.textAlign = 'center';
        context.textBaseline = 'alphabetic';
        labels.forEach((label, index) => {
            if (index % labelStride !== 0 && index !== labels.length - 1) return;
            const shortLabel = /^\d{4}-\d{2}-\d{2}$/.test(label) ? label.slice(5) : label;
            context.fillStyle = '#64748b';
            context.fillText(shortLabel, padding.left + step * (index + 0.5), height - 8);
        });

        return { padding, plotWidth, plotHeight, step };
    };

    render(root.querySelector('[data-report-bar]'), (context, width, height) => {
        const hasRefunds = refunds.some((amount) => amount > 0);
        const maxValue = Math.max(...values, ...(hasRefunds ? refunds : []), 1);
        const { padding, plotHeight, step } = chartFrame(context, width, height, maxValue);
        const groupWidth = Math.min(42, step * 0.64);
        const barWidth = hasRefunds ? groupWidth / 2 - 1 : groupWidth;

        values.forEach((value, index) => {
            const center = padding.left + step * (index + 0.5);
            const barHeight = plotHeight * (value / maxValue);
            context.fillStyle = '#f97316';
            context.fillRect(center - (hasRefunds ? groupWidth / 2 : barWidth / 2), padding.top + plotHeight - barHeight, barWidth, barHeight);

            if (hasRefunds) {
                const refundHeight = plotHeight * (refunds[index] / maxValue);
                context.fillStyle = '#dc2626';
                context.fillRect(center + 1, padding.top + plotHeight - refundHeight, barWidth, refundHeight);
            }
        });
    });

    render(root.querySelector('[data-report-line]'), (context, width, height) => {
        const maxValue = Math.max(...counts, 1);
        const { padding, plotHeight, step } = chartFrame(context, width, height, maxValue);
        context.strokeStyle = '#ea580c';
        context.lineWidth = 3;
        context.lineJoin = 'round';
        context.beginPath();
        counts.forEach((value, index) => {
            const x = padding.left + step * (index + 0.5);
            const y = padding.top + plotHeight * (1 - value / maxValue);
            index === 0 ? context.moveTo(x, y) : context.lineTo(x, y);
        });
        context.stroke();
        counts.forEach((value, index) => {
            const x = padding.left + step * (index + 0.5);
            const y = padding.top + plotHeight * (1 - value / maxValue);
            context.beginPath();
            context.arc(x, y, 3.5, 0, Math.PI * 2);
            context.fillStyle = '#fff';
            context.fill();
            context.strokeStyle = '#ea580c';
            context.lineWidth = 2;
            context.stroke();
        });
    });

    render(root.querySelector('[data-report-donut]'), (context, width, height) => {
        const total = segments.reduce((sum, [, value]) => sum + Number(value), 0);
        if (!total) return;

        const centerX = width / 2;
        const centerY = height / 2;
        const radius = Math.min(width, height) * 0.39;
        const isRevenue = root.querySelector('[data-report-donut]')?.dataset.reportType === 'revenue';
        let angle = -Math.PI / 2;
        segments.forEach(([name, value], index) => {
            const next = angle + (Number(value) / total) * Math.PI * 2;
            context.beginPath();
            context.arc(centerX, centerY, radius, angle, next);
            context.arc(centerX, centerY, radius * 0.58, next, angle, true);
            context.closePath();
            context.fillStyle = isRevenue ? revenueColors[index % revenueColors.length] : (segmentColors[name] || palette[index % palette.length]);
            context.fill();

            angle = next;
        });

        context.textAlign = 'center';
        context.textBaseline = 'middle';
        if (isRevenue) {
            context.fillStyle = '#64748b';
            context.font = '10px Inter, sans-serif';
            context.fillText(canvasLabel(root), centerX, centerY - 9);
            context.fillStyle = '#0f172a';
            context.font = '700 15px Inter, sans-serif';
            const totalInMillions = new Intl.NumberFormat(undefined, { maximumFractionDigits: 2 }).format(total / 1_000_000);
            context.fillText(`${totalInMillions} ${canvasUnit(root)}`, centerX, centerY + 11);
        } else {
            context.fillStyle = '#0f172a';
            context.font = '700 16px Inter, sans-serif';
            context.fillText(new Intl.NumberFormat().format(total), centerX, centerY - 6);
            context.fillStyle = '#64748b';
            context.font = '10px Inter, sans-serif';
            context.fillText(canvasLabel(root), centerX, centerY + 11);
        }
    });
});

function canvasLabel(root) {
    return root.querySelector('[data-report-donut]')?.dataset.totalLabel || '';
}

function canvasUnit(root) {
    return root.querySelector('[data-report-donut]')?.dataset.totalUnit || '';
}

document.querySelectorAll('[data-report-pdf-export]').forEach((link) => {
    let exporting = false;

    link.addEventListener('click', async (event) => {
        event.preventDefault();
        if (exporting) return;

        exporting = true;
        link.setAttribute('aria-disabled', 'true');

        try {
            const response = await fetch(link.href, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });
            const contentType = response.headers.get('content-type') || '';

            if (!response.ok || !contentType.includes('application/pdf')) {
                const payload = await response.json().catch(() => null);
                throw new Error(payload?.message || link.dataset.exportError);
            }

            const blob = await response.blob();
            if (!blob.size) throw new Error(link.dataset.exportError);

            const objectUrl = URL.createObjectURL(blob);
            const download = document.createElement('a');
            const filename = response.headers.get('content-disposition')?.match(/filename="?([^";]+)"?/i)?.[1];
            download.href = objectUrl;
            download.download = filename || 'futabus-report.pdf';
            document.body.append(download);
            download.click();
            download.remove();
            window.setTimeout(() => URL.revokeObjectURL(objectUrl), 1000);
            window.FutaNotify?.success(link.dataset.exportSuccess, { key: 'report-pdf-export' });
        } catch (error) {
            window.FutaNotify?.error(error.message || link.dataset.exportError, { key: 'report-pdf-export', duration: 8000 });
        } finally {
            exporting = false;
            link.removeAttribute('aria-disabled');
        }
    });
});
