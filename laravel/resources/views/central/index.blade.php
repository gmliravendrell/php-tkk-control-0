<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control Central - Salida</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .badge-meal { font-size: 0.9em; margin-right: 5px; }
        .table-success-row td { background-color: #d4edda !important; }
        .table-danger-row td { background-color: #f8d7da !important; }
        body { padding-top: 70px; } /* espacio para la navbar fija */
    </style>
</head>
<body class="bg-light">

<!-- Headbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <span class="navbar-brand">Control de Meta</span>
    <div class="d-flex">
      <a href="/" class="btn btn-outline-light">🏠 Volver a Home</a>
    </div>
  </div>
</nav>

<div class="container py-5">
    <h1 class="mb-4">Control Central - Salida</h1>

    <!-- Búsqueda de dorsal -->
    <div class="mb-4">
        <label for="searchDorsal" class="form-label">Buscar dorsal</label>
        <input type="number" id="searchDorsal" class="form-control" placeholder="Introduce dorsal">
        <button id="btnSearch" class="btn btn-primary mt-2">Buscar</button>
    </div>

    <!-- Información del participante -->
    <div id="participantInfo" class="card p-3 d-none">
        <h4>Información del participante</h4>
        <p><strong>Dorsal:</strong> <span id="pDorsal"></span></p>
        <p><strong>Nombre:</strong> <span id="pName"></span></p>
        <p><strong>Edad:</strong> <span id="pAge"></span></p>
        <p><strong>Género:</strong> <span id="pGender"></span></p>
        <p><strong>Talla Camiseta:</strong> <span id="pShirt"></span></p>
        <p><strong>Bocadillos:</strong> <span id="pSandwiches"></span></p>
        <p><strong>Estado:</strong> <span id="pStatus"></span></p>

        <div id="minorAlert" class="alert alert-danger d-none">
            ⚠️ Participante menor de edad: se requiere autorización firmada
            <div class="form-check mt-2">
                <input type="checkbox" class="form-check-input" id="minorAuth">
                <label class="form-check-label" for="minorAuth">He recibido la autorización</label>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="mt-3">
            <button id="btnDeliver" class="btn btn-success me-2" disabled>Entregar dorsal</button>
            <button id="btnAbandon" class="btn btn-danger" disabled>Abandonar</button>
        </div>
    </div>

    <!-- Barra de progreso -->
    <div class="mt-5">
        <h4>Progreso de entrega</h4>
        <div class="progress">
            <div id="barPending" class="progress-bar bg-secondary" role="progressbar"></div>
            <div id="barDelivered" class="progress-bar bg-success" role="progressbar"></div>
            <div id="barAbandoned" class="progress-bar bg-danger" role="progressbar"></div>
        </div>
        <div class="mt-2">
            <span class="badge bg-secondary">Pendientes</span>
            <span class="badge bg-success">Entregados</span>
            <span class="badge bg-danger">Abandonados</span>
        </div>
    </div>

    <!-- Accordion con entregados y abandonos -->
    <div class="mt-5">
        <h4>Últimos registros</h4>
        <div class="accordion" id="recentAccordion">

            <!-- Entregados -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingChecks">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseChecks" aria-expanded="true" aria-controls="collapseChecks">
                        Últimos dorsales entregados
                    </button>
                </h2>
                <div id="collapseChecks" class="accordion-collapse collapse show" aria-labelledby="headingChecks" data-bs-parent="#recentAccordion">
                    <div class="accordion-body">
                        <div class="table-responsive" style="max-height:200px; overflow-y:auto;">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Dorsal</th>
                                        <th>Nombre</th>
                                        <th>Hora</th>
                                    </tr>
                                </thead>
                                <tbody id="tableChecks">
                                    <!-- Rellenado por JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Abandonos -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingAbandons">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAbandons" aria-expanded="false" aria-controls="collapseAbandons">
                        Abandonos
                    </button>
                </h2>
                <div id="collapseAbandons" class="accordion-collapse collapse" aria-labelledby="headingAbandons" data-bs-parent="#recentAccordion">
                    <div class="accordion-body">
                        <div class="table-responsive" style="max-height:200px; overflow-y:auto;">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Dorsal</th>
                                        <th>Nombre</th>
                                        <th>Hora</th>
                                        <th>Control</th>
                                    </tr>
                                </thead>
                                <tbody id="tableAbandons">
                                    <!-- Rellenado por JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const btnSearch = document.getElementById("btnSearch");
    const dorsalInput = document.getElementById("searchDorsal");
    const participantInfo = document.getElementById("participantInfo");
    const minorAlert = document.getElementById("minorAlert");
    const minorAuth = document.getElementById("minorAuth");
    const btnDeliver = document.getElementById("btnDeliver");
    const btnAbandon = document.getElementById("btnAbandon");

    let currentParticipant = null;

    // Buscar participante
    btnSearch.addEventListener("click", async () => {
        const dorsal = dorsalInput.value;
        if (!dorsal) return alert("Introduce un dorsal");

        const res = await fetch(`/api/participants/${dorsal}`);
        if (!res.ok) return alert("Participante no encontrado");
        const participant = await res.json();
        currentParticipant = participant;

        renderParticipant(participant);
    });

    function renderParticipant(p) {
        document.getElementById("pDorsal").innerText = p.id;
        document.getElementById("pName").innerText = p.first_name + " " + p.last_name;
        document.getElementById("pAge").innerText = calcAge(p.birth_date) + " años";
        document.getElementById("pGender").innerText = p.gender || "-";
        document.getElementById("pShirt").innerText = p.shirt_size || "-";
        document.getElementById("pSandwiches").innerHTML =
            `<span class="badge bg-info text-dark badge-meal">🍔 Comida: ${p.lunch_sandwich || "-"}</span>
             <span class="badge bg-warning text-dark badge-meal">🥪 Cena: ${p.dinner_sandwich || "-"}</span>`;
        document.getElementById("pStatus").innerText = p.status;

        const age = calcAge(p.birth_date);

        // Reset botones
        btnDeliver.disabled = true;
        btnAbandon.disabled = true;

        if (age < 18) {
            minorAlert.classList.remove("d-none");
            btnDeliver.disabled = !minorAuth.checked;
            btnAbandon.disabled = false;
        } else {
            minorAlert.classList.add("d-none");
            if (p.status === "not_presented") {
                btnDeliver.disabled = false;
                btnAbandon.disabled = false;
            } else if (p.status === "presented") {
                btnDeliver.disabled = true;
                btnAbandon.disabled = false;
            } else if (p.status === "abandoned") {
                btnDeliver.disabled = true;
                btnAbandon.disabled = true;
            }
        }

        participantInfo.classList.remove("d-none");
    }

    function calcAge(dateString) {
        if (!dateString) return "-";
        const birth = new Date(dateString);
        const diff = Date.now() - birth.getTime();
        return Math.abs(new Date(diff).getUTCFullYear() - 1970);
    }

    minorAuth.addEventListener("change", () => {
        if (currentParticipant && calcAge(currentParticipant.birth_date) < 18) {
            btnDeliver.disabled = !minorAuth.checked;
        }
    });

    // Entregar dorsal
    btnDeliver.addEventListener("click", async () => {
        if (!currentParticipant) return;

        if (currentParticipant.status === "not_presented") {
            await fetch(`/api/participants/${currentParticipant.id}`, {
                method: "PATCH",
                headers: {"Content-Type":"application/json"},
                body: JSON.stringify({ status: "presented" })
            });
            currentParticipant.status = "presented";
        }

        await fetch(`/api/checks`, {
            method: "POST",
            headers: {"Content-Type":"application/json"},
            body: JSON.stringify({
                participant_id: currentParticipant.id,
                control_id: 1,
                type: "check"
            })
        });
        refreshProgress();
        loadRecent();
        alert("Dorsal entregado ✅");
        renderParticipant(currentParticipant);
        dorsalInput.value = "";
        dorsalInput.focus();
    });

    // Marcar abandono
    btnAbandon.addEventListener("click", async () => {
        if (!currentParticipant) return;

        await fetch(`/api/participants/${currentParticipant.id}`, {
            method: "PATCH",
            headers: {"Content-Type":"application/json"},
            body: JSON.stringify({ status: "abandoned" })
        });
        currentParticipant.status = "abandoned";
 
        await fetch(`/api/checks`, {
            method: "POST",
            headers: {"Content-Type":"application/json"},
            body: JSON.stringify({
                participant_id: currentParticipant.id,
                control_id: 1,
                type: "abandon"
            })
        });
        refreshProgress();
        loadRecent();
        alert("Marcado como abandonado ❌");
        renderParticipant(currentParticipant);
        dorsalInput.value = "";
        dorsalInput.focus();
    });

    async function refreshProgress() {
        const res = await fetch(`/api/controls/1`);
        if (!res.ok) return;
        const control = await res.json();

        const total = control.total || 1;
        const pending = control.missing || 0;
        const delivered = control.passed || 0;
        const abandoned = control.abandoned || 0;

        document.getElementById("barPending").style.width = `${(pending/total)*100}%`;
        document.getElementById("barDelivered").style.width = `${(delivered/total)*100}%`;
        document.getElementById("barAbandoned").style.width = `${(abandoned/total)*100}%`;
    }

    async function loadRecent() {
        const resChecks = await fetch(`/api/controls/1/checks`);
        if (resChecks.ok) {
            const checks = await resChecks.json();
            const tbody = document.getElementById("tableChecks");
            tbody.innerHTML = "";
            checks.forEach(c => {
                const tr = document.createElement("tr");
                tr.classList.add("table-success-row");
                tr.innerHTML = `<td>${c.dorsal}</td><td>${c.name}</td><td>${c.checked_at}</td>`;
                tbody.appendChild(tr);
            });
        }

        const resAbandons = await fetch(`/api/checks/abandons`);
        if (resAbandons.ok) {
            const abandons = await resAbandons.json();
            const tbody = document.getElementById("tableAbandons");
            tbody.innerHTML = "";
            abandons.forEach(a => {
                const tr = document.createElement("tr");
                tr.classList.add("table-danger-row");
                tr.innerHTML = `<td>${a.dorsal}</td><td>${a.name}</td><td>${a.checked_at}</td><td>${a.control}</td>`;
                tbody.appendChild(tr);
            });
        }
    }

    setInterval(() => {
        refreshProgress();
        loadRecent();
    }, 180000);

    refreshProgress();
    loadRecent();
});
</script>

</body>
</html>
