<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Controles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

<div class="container my-5">

    <!-- Cabecera -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold">📊 Panel de Control</h1>
        <div class="d-flex gap-2">
            <a href="/" class="btn btn-secondary">🏠 Menú principal</a>
            <button class="btn btn-primary" onclick="refreshControls(true)">🔄 Actualizar</button>
        </div>
    </div>

    <!-- Tarjetas por control -->
    <div class="row g-4 mb-4" id="cards-container"></div>

    <!-- Gráfico global -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Estado global de participantes</h5>
        </div>
        <div class="card-body">
            <canvas id="statusChart" height="120"></canvas>
        </div>
    </div>

    <!-- Tabla detallada -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Detalles por control</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle" id="controlsTable">
                <thead class="table-light">
                    <tr>
                        <th>Control</th>
                        <th>Pasados</th>
                        <th>Abandonos</th>
                        <th>Pendientes</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Toast Bootstrap -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
    <div id="mainToast" class="toast text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage">✅ Acción realizada</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Sonido alerta -->
<audio id="alertSound">
    <source src="data:audio/wav;base64,UklGRhYAAABXQVZFZm10IBAAAAABAAEAQB8AAIA+AAACABAAZGF0YQYAAAAA" type="audio/wav">
</audio>

<script>
let chart; // gráfico global
let perControlCharts = {}; // gráficos individuales
let lastAbandonedTotal = 0; // Para detectar incrementos

// Renderiza los controles en cards + tabla
function renderControls(controls) {
    const cardsContainer = document.getElementById('cards-container');
    const tbody = document.querySelector('#controlsTable tbody');
    cardsContainer.innerHTML = '';
    tbody.innerHTML = '';

    let totalPassed = 0, totalAbandoned = 0, totalMissing = 0;

    controls.forEach(c => {
        totalPassed += c.passed;
        totalAbandoned += c.abandoned;
        totalMissing += c.missing;

        const chartId = `chart-control-${c.id}`;

        // Cards con gráfico
        cardsContainer.innerHTML += `
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">${c.name}</h5>
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-1">✅ Pasados: <b>${c.passed}</b></p>
                                <p class="mb-1">❌ Abandonos: <b>${c.abandoned}</b></p>
                                <p class="mb-1">⏳ Pendientes: <b>${c.missing}</b></p>
                            </div>
                            <div class="ms-auto" style="width:120px; height:120px">
                                <canvas id="${chartId}"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Tabla
        tbody.innerHTML += `
            <tr>
                <td>${c.name}</td>
                <td>${c.passed}</td>
                <td>${c.abandoned}</td>
                <td>${c.missing}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="updateControl(${c.id}, 'passed')">+ Pasado</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="updateControl(${c.id}, 'abandoned')">+ Abandono</button>
                </td>
            </tr>
        `;

        // Renderizar gráfico individual
        setTimeout(() => renderControlChart(chartId, c), 0);
    });

    // Detectar si aumentaron los abandonos
    if (totalAbandoned > lastAbandonedTotal) {
        playAlert();
    }
    lastAbandonedTotal = totalAbandoned;

    // Actualiza gráfico global
    renderChart(totalPassed, totalAbandoned, totalMissing);
}

// Renderiza gráfico global
function renderChart(passed, abandoned, missing) {
    const ctx = document.getElementById('statusChart').getContext('2d');
    if (chart) chart.destroy();

    chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pasados', 'Abandonos', 'Pendientes'],
            datasets: [{
                data: [passed, abandoned, missing],
                backgroundColor: ['#28a745', '#dc3545', '#ffc107']
            }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
    });
}

// Renderiza gráfico individual
function renderControlChart(chartId, control) {
    const ctx = document.getElementById(chartId);
    if (!ctx) return;

    if (perControlCharts[chartId]) {
        perControlCharts[chartId].destroy();
    }

    perControlCharts[chartId] = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pasados', 'Abandonos', 'Pendientes'],
            datasets: [{
                data: [control.passed, control.abandoned, control.missing],
                backgroundColor: ['#28a745', '#dc3545', '#ffc107']
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            cutout: '70%'
        }
    });
}

// Llama a la API para obtener los datos
async function refreshControls(manual = false) {
    try {
        const res = await fetch('/api/controls');
        const data = await res.json();
        renderControls(data);
        if (manual) showToast('🔄 Datos actualizados', 'info');
    } catch (err) {
        showToast('❌ Error al cargar los datos', 'danger');
    }
}

// Llama a la API para actualizar un control
async function updateControl(id, action) {
    try {
        const res = await fetch(`/api/controls/${id}/${action}`, { method: 'POST' });
        if (res.ok) {
            showToast('✅ Actualización correcta', 'success');
            refreshControls();
        } else {
            showToast('❌ Error al actualizar', 'danger');
        }
    } catch {
        showToast('❌ Error de red', 'danger');
    }
}

// Toast bonito con Bootstrap
function showToast(msg, type='success') {
    const toastEl = document.getElementById('mainToast');
    const toastBody = document.getElementById('toastMessage');
    toastBody.textContent = msg;
    toastEl.className = `toast text-bg-${type} border-0`;
    new bootstrap.Toast(toastEl).show();
}

// Reproduce sonido alerta
function playAlert() {
    const audio = document.getElementById('alertSound');
    audio.play();
}

// Cargar al inicio con polling
document.addEventListener('DOMContentLoaded', () => {
    refreshControls();
    setInterval(refreshControls, 10000); // cada 10s
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
