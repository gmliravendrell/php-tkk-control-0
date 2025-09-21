<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Controles</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .podio {
      margin-top: 1rem;
    }
    .podio h6 {
      font-weight: bold;
      margin-bottom: .5rem;
    }
    .podio ol {
      padding-left: 1.2rem;
      margin: 0;
    }
    .podio ol li {
      font-size: 0.9rem;
    }
    .progress-icon {
      font-size: 1.5rem;
      margin-right: 0.5rem;
    }
  </style>
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

  <!-- Tarjetas -->
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

<!-- Sonidos -->
<audio id="soundAbandoned">
  <source src="https://actions.google.com/sounds/v1/cartoon/wood_plank_flicks.ogg" type="audio/ogg">
</audio>
<audio id="soundFinished">
  <source src="https://actions.google.com/sounds/v1/alarms/trumpet_fanfare.ogg" type="audio/ogg">
</audio>

<script>
let chart; // gráfico global
let perControlCharts = {}; // gráficos individuales
let lastAbandonedTotal = 0;
let lastFinishedTotal = 0;

// Render principal
function renderControls(controls) {
  const cardsContainer = document.getElementById('cards-container');
  const tbody = document.querySelector('#controlsTable tbody');
  cardsContainer.innerHTML = '';
  tbody.innerHTML = '';

  let totalPassed = 0, totalAbandoned = 0, totalMissing = 0, totalFinished = 0;

  controls.forEach(c => {
    totalPassed += c.passed;
    totalAbandoned += c.abandoned;
    totalMissing += c.missing;
    if (c.type === 'finish') totalFinished = c.passed;

    const cardHtml = renderCard(c);
    cardsContainer.innerHTML += cardHtml;

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

    // Renderizar gráfico si aplica
    if (c.type === 'control') {
      setTimeout(() => renderControlChart(`chart-control-${c.id}`, c), 0);
    }
  });

  // Detectar cambios
  if (totalAbandoned > lastAbandonedTotal) playSound('abandoned');
  if (totalFinished > lastFinishedTotal) playSound('finished');
  lastAbandonedTotal = totalAbandoned;
  lastFinishedTotal = totalFinished;

  // Gráfico global
  renderChart(totalPassed, totalAbandoned, totalMissing);
}

// Render tarjeta según tipo
function renderCard(c) {
  if (c.type === 'start' || c.type === 'finish') {
    return `
      <div class="col-12">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title">${c.type === 'start' ? '🚩 Salida' : '🏁 Meta'}</h5>
            <div class="d-flex align-items-center mb-2">
              <span class="progress-icon">${c.type === 'start' ? '🚶' : '🎉'}</span>
              <div class="progress flex-grow-1" style="height: 25px;">
                <div class="progress-bar bg-${c.type === 'start' ? 'info' : 'success'}"
                  role="progressbar" style="width: ${(c.passed / (c.passed + c.missing)) * 100}%">
                  ${c.passed} / ${c.passed + c.missing}
                </div>
              </div>
            </div>
            ${renderPodio(c)}
          </div>
        </div>
      </div>
    `;
  }

  if (c.type === 'control') {
    return `
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
                <canvas id="chart-control-${c.id}"></canvas>
              </div>
            </div>
            ${renderPodio(c)}
          </div>
        </div>
      </div>
    `;
  }
  return '';
}

// Podio top 3 hombres y mujeres
function renderPodio(c) {
  if (!c.topMen || !c.topWomen) return '';
  return `
    <div class="row podio">
      <div class="col-6">
        <h6>👨 Hombres</h6>
        <ol>
          ${c.topMen.slice(0,3).map(p => `<li>#${p.dorsal} ${p.name}</li>`).join('')}
        </ol>
      </div>
      <div class="col-6">
        <h6>👩 Mujeres</h6>
        <ol>
          ${c.topWomen.slice(0,3).map(p => `<li>#${p.dorsal} ${p.name}</li>`).join('')}
        </ol>
      </div>
    </div>
  `;
}

// Gráfico global
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

// Gráfico individual de control
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
    options: { plugins: { legend: { display: false } }, cutout: '70%' }
  });
}

// API
async function refreshControls(manual = false) {
  try {
    const res = await fetch('/api/controls');
    const data = await res.json();
    renderControls(data);
    if (manual) showToast('🔄 Datos actualizados', 'info');
  } catch {
    showToast('❌ Error al cargar los datos', 'danger');
  }
}

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

// Toast
function showToast(msg, type='success') {
  const toastEl = document.getElementById('mainToast');
  const toastBody = document.getElementById('toastMessage');
  toastBody.textContent = msg;
  toastEl.className = `toast text-bg-${type} border-0`;
  new bootstrap.Toast(toastEl).show();
}

// Sonidos
function playSound(type) {
  if (type === 'abandoned') document.getElementById('soundAbandoned').play();
  if (type === 'finished') document.getElementById('soundFinished').play();
}

// Inicio
document.addEventListener('DOMContentLoaded', () => {
  refreshControls();
  setInterval(refreshControls, 10000);
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
