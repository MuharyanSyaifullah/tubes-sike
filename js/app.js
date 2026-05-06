function byId(id) {
  return document.getElementById(id);
}

function drawLineChart(canvasId, labels, values, title) {
  const canvas = byId(canvasId);
  if (!canvas || !labels || !values || values.length === 0) return;
  const ctx = canvas.getContext('2d');
  const width = canvas.width = canvas.offsetWidth;
  const height = canvas.height = 280;
  const padding = 40;
  const max = Math.max(...values, 100);

  ctx.clearRect(0, 0, width, height);
  ctx.font = '12px Arial';
  ctx.fillStyle = '#5f6f66';
  ctx.fillText(title, padding, 20);

  ctx.strokeStyle = '#dfe8e2';
  for (let i = 0; i <= 4; i++) {
    const y = padding + ((height - padding * 2) / 4) * i;
    ctx.beginPath();
    ctx.moveTo(padding, y);
    ctx.lineTo(width - padding, y);
    ctx.stroke();
  }

  const stepX = labels.length > 1 ? (width - padding * 2) / (labels.length - 1) : 0;
  ctx.strokeStyle = '#7A9D8C';
  ctx.lineWidth = 3;
  ctx.beginPath();
  values.forEach((v, i) => {
    const x = padding + stepX * i;
    const y = height - padding - (v / max) * (height - padding * 2);
    if (i === 0) ctx.moveTo(x, y);
    else ctx.lineTo(x, y);
  });
  ctx.stroke();

  values.forEach((v, i) => {
    const x = padding + stepX * i;
    const y = height - padding - (v / max) * (height - padding * 2);
    ctx.fillStyle = '#5d7e6d';
    ctx.beginPath();
    ctx.arc(x, y, 4, 0, Math.PI * 2);
    ctx.fill();
    ctx.fillStyle = '#5f6f66';
    ctx.fillText(String(labels[i]).slice(5), x - 10, height - 12);
  });
}

function drawPolygon(ctx, cx, cy, radius, sides, stroke) {
  ctx.beginPath();
  for (let i = 0; i < sides; i++) {
    const angle = (Math.PI * 2 / sides) * i - Math.PI / 2;
    const x = cx + Math.cos(angle) * radius;
    const y = cy + Math.sin(angle) * radius;
    if (i === 0) ctx.moveTo(x, y);
    else ctx.lineTo(x, y);
  }
  ctx.closePath();
  ctx.strokeStyle = stroke;
  ctx.stroke();
}

function drawRadarChart(canvasId, profile) {
  const canvas = byId(canvasId);
  if (!canvas || !profile) return;
  const ctx = canvas.getContext('2d');
  const width = canvas.width = canvas.offsetWidth;
  const height = canvas.height = 280;
  const cx = width / 2;
  const cy = height / 2;
  const radius = 90;
  const labels = Object.keys(profile);
  const values = Object.values(profile);

  ctx.clearRect(0, 0, width, height);
  ctx.font = '12px Arial';

  for (let level = 1; level <= 4; level++) {
    drawPolygon(ctx, cx, cy, (radius / 4) * level, labels.length, '#dfe8e2');
  }

  labels.forEach((label, i) => {
    const angle = (Math.PI * 2 / labels.length) * i - Math.PI / 2;
    const x = cx + Math.cos(angle) * (radius + 18);
    const y = cy + Math.sin(angle) * (radius + 18);
    ctx.fillStyle = '#5f6f66';
    ctx.fillText(label, x - 20, y);
  });

  ctx.beginPath();
  values.forEach((value, i) => {
    const angle = (Math.PI * 2 / labels.length) * i - Math.PI / 2;
    const r = radius * (value / 100);
    const x = cx + Math.cos(angle) * r;
    const y = cy + Math.sin(angle) * r;
    if (i === 0) ctx.moveTo(x, y);
    else ctx.lineTo(x, y);
  });
  ctx.closePath();
  ctx.fillStyle = 'rgba(122,157,140,0.25)';
  ctx.strokeStyle = '#7A9D8C';
  ctx.lineWidth = 2;
  ctx.fill();
  ctx.stroke();
}

window.addEventListener('DOMContentLoaded', () => {
  if (window.dashboardChartData) {
    drawLineChart('dashboardChart', window.dashboardChartData.labels, window.dashboardChartData.values, window.dashboardChartData.title);
  }
  if (window.detailLineChartData) {
    drawLineChart('detailLineChart', window.detailLineChartData.labels, window.detailLineChartData.values, window.detailLineChartData.title);
  }
  if (window.detailRadarChartData) {
    drawRadarChart('detailRadarChart', window.detailRadarChartData);
  }
});
