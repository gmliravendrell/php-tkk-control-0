<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control {{ $control->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Barra superior -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="{{ route('control.index') }}">⬅ Menú Principal</a>
        <span class="navbar-text ms-auto">
            📍 {{ $control->name }} — 👤 {{ $control->responsible }} (📞 {{ $control->phone }})
        </span>
    </div>
</nav>

<div class="container">

    <!-- Barra de progreso -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">Progreso del control</h5>
            <div class="progress mb-2" style="height: 25px;">
                <div id="progressPassed" class="progress-bar bg-success" role="progressbar"></div>
                <div id="progressMissing" class="progress-bar bg-warning" role="progressbar"></div>
                <div id="progressAbandoned" class="progress-bar bg-danger" role="progressbar"></div>
            </div>
            <p class="mb-0 text-muted" id="progressText"></p>
        </div>
    </div>

    <!-- Formulario de dorsal -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">Registrar dorsal</h5>
            <div class="input-group mb-3">
                <input type="number" id="dorsalInput" class="form-control" placeholder="Número de dorsal">
                <button class="btn btn-success" id="btnCheck">✔️ Marcar</button>
                <button class="btn btn-danger" id="btnAbandon">🚨 Abandonar</button>
            </div>
        </div>
    </div>

    <!-- Botón de escaneo -->
    <div class="d-grid mb-4">
        <a href="{{ route('scanner.index', $control->id) }}" class="btn btn-secondary btn-lg">
            📷 Escanear QR
        </a>
    </div>

    <!-- Acordeón -->
    <div class="accordion mb-4" id="accordionParticipants">

        <!-- Pasados -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingPassed">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePassed">
                    ✅ Participantes que han pasado
                </button>
            </h2>
            <div id="collapsePassed" class="accordion-collapse collapse show" data-bs-parent="#accordionParticipants">
                <div class="accordion-body" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Dorsal</th>
                                <th>Nombre</th>
                                <th>Hora de paso</th>
                            </tr>
                        </thead>
                        <tbody id="tablePassed"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Abandonos -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingAbandoned">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAbandoned">
                    🚨 Abandonos
                </button>
            </h2>
            <div id="collapseAbandoned" class="accordion-collapse collapse" data-bs-parent="#accordionParticipants">
                <div class="accordion-body" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Dorsal</th>
                                <th>Nombre</th>
                                <th>Hora</th>
                                <th>Control</th>
                            </tr>
                        </thead>
                        <tbody id="tableAbandoned"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
const controlId = {{ $control->id }};
const csrfToken = '{{ csrf_token() }}';

// Render barra progreso
function renderProgress(passed, missing, abandoned) {
    const total = passed + missing + abandoned;
    const totalSinAbandonos = total - abandoned;

    const pctPassed = totalSinAbandonos > 0 ? (passed / totalSinAbandonos) * 100 : 0;
    const pctMissing = totalSinAbandonos > 0 ? (missing / totalSinAbandonos) * 100 : 0;
    const pctAbandoned = total > 0 ? (abandoned / total) * 100 : 0;

    document.getElementById("progressPassed").style.width = pctPassed + "%";
    document.getElementById("progressMissing").style.width = pctMissing + "%";
    document.getElementById("progressAbandoned").style.width = pctAbandoned + "%";

    document.getElementById("progressPassed").textContent = Math.round(pctPassed) + "%";
    document.getElementById("progressMissing").textContent = Math.round(pctMissing) + "%";
    document.getElementById("progressAbandoned").textContent = Math.round(pctAbandoned) + "%";

    document.getElementById("progressText").textContent =
        `✅ Pasados: ${passed} · ⏳ Pendientes: ${missing} · 🚨 Abandonos: ${abandoned}`;
}

// Render tablas
function renderTables(passedList, abandonedList) {
    const tablePassed = document.getElementById("tablePassed");
    const tableAbandoned = document.getElementById("tableAbandoned");

    tablePassed.innerHTML = passedList
        .sort((a, b) => new Date(b.checked_at) - new Date(a.checked_at))
        .map(p => `<tr><td>${p.dorsal}</td><td>${p.name}</td><td>${p.checked_at}</td></tr>`)
        .join("");

    tableAbandoned.innerHTML = abandonedList
        .map(p => `<tr><td>${p.dorsal}</td><td>${p.name}</td><td>${p.checked_at}</td><td>${p.control}</td></tr>`)
        .join("");
}

// Refrescar datos
async function refreshControl() {
    try {
        // Estado del control
        const resStatus = await fetch(`/api/controls/${controlId}`);
        const statusData = await resStatus.json();
        renderProgress(statusData.passed, statusData.missing, statusData.abandoned);

        // Lista de pasados
        const resChecks = await fetch(`/api/controls/${controlId}/checks`);
        const passedList = await resChecks.json();

        // Lista de abandonos global
        const resAbandons = await fetch(`/api/checks/abandons`);
        const abandonedList = await resAbandons.json();

        renderTables(passedList, abandonedList);

    } catch (e) {
        console.error("Error al refrescar", e);
    }
}

// Marcar acción
async function sendCheck(type) {
    const dorsal = document.getElementById("dorsalInput").value;
    if (!dorsal) return alert("Introduce dorsal");

    const res = await fetch(`/api/checks`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": csrfToken
        },
        body: JSON.stringify({ control_id: controlId, participant_id: dorsal, type })
    });
    const data = await res.json();
    if (res.ok) {
        alert(type === "check" ? "Marcado correctamente" : "Abandono registrado");
        refreshControl();
    } else {
        alert(data.error || "Error");
    }
}

// Botones
document.getElementById("btnCheck").addEventListener("click", () => sendCheck("check"));
document.getElementById("btnAbandon").addEventListener("click", () => sendCheck("abandon"));

// Inicial + polling
document.addEventListener("DOMContentLoaded", () => {
    refreshControl();
    setInterval(refreshControl, 180000); // 3 minutos
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
