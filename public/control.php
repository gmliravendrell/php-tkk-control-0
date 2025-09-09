<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Control TKK</title>
<style>
body { font-family: sans-serif; padding: 1rem; }
input, button, select { font-size: 1rem; padding: .5rem; margin: .5rem 0; }
button:disabled { opacity: 0.5; }
#info { margin-top: 1rem; border-top: 1px solid #ccc; padding-top: 1rem; }
</style>
</head>
<body>
<h1>📍 Control TKK</h1>

<label>Selecciona control:
  <select id="controlSelect"></select>
</label>

<div id="info">
  <p id="desc"></p>
  <p>Estado: <span id="status"></span></p>
  <p>Hora apertura: <span id="openTime"></span></p>
  <p>Hora cierre: <span id="closeTime"></span></p>
  <p>Responsable: <span id="responsable"></span> (<span id="phone"></span>)</p>
  <p>Km: <span id="km"></span></p>
  <p>Pasados: <span id="passed"></span></p>
  <p>Faltan: <span id="missing"></span></p>
  <p>Abandonos: <span id="abandoned"></span></p>

  <div id="actions"></div>
</div>

<script>
let controls = [];
let selectedControlId = null;
let controlData = null;

async function loadControls() {
  try {
    const res = await fetch(`/api/controls.php`);
    controls = await res.json();
    const select = document.getElementById("controlSelect");
    select.innerHTML = "";

    controls.forEach(c => {
      const opt = document.createElement("option");
      opt.value = c.id;
      opt.textContent = `${c.name} (km ${c.km_point})`;
      select.appendChild(opt);
    });

    // Seleccionar el primer control por defecto
    if (controls.length) {
      select.value = controls[0].id;
      selectedControlId = parseInt(controls[0].id);
      await loadControlInfo();
    }

    select.addEventListener("change", async () => {
      selectedControlId = parseInt(select.value);
      await loadControlInfo();
    });

  } catch (err) {
    console.error("Error cargando controles:", err);
    alert("Error cargando controles. Revisa consola.");
  }
}

async function loadControlInfo() {
  if (!selectedControlId) return;

  console.log("Llamando a control.php con id:", selectedControlId);

  try {
    const res = await fetch(`/api/control.php?id=${selectedControlId}`);
    if (!res.ok) {
      const err = await res.json().catch(() => ({ error: res.statusText }));
      return alert("Error cargando control: " + (err.error || res.statusText));
    }

    controlData = await res.json();

    document.getElementById("desc").textContent = controlData.description;
    document.getElementById("status").textContent = controlData.status;
    document.getElementById("openTime").textContent = controlData.open_time || "-";
    document.getElementById("closeTime").textContent = controlData.close_time || "-";
    document.getElementById("responsable").textContent = controlData.responsable || "-";
    document.getElementById("phone").textContent = controlData.phone || "-";
    document.getElementById("km").textContent = controlData.km_point;
    document.getElementById("passed").textContent = controlData.passed;
    document.getElementById("missing").textContent = controlData.missing;
    document.getElementById("abandoned").textContent = controlData.abandoned;

    renderActions();

  } catch (err) {
    console.error("Error cargando control:", err);
    alert("Error cargando control. Revisa consola.");
  }
}

function renderActions() {
  const actions = document.getElementById("actions");
  actions.innerHTML = "";

  if (!controlData) return;

  if (controlData.status === "no-preparado") {
    const btn = document.createElement("button");
    btn.textContent = "Preparar control";
    btn.onclick = async () => {
      await fetch(`/api/control/prepare.php?id=${selectedControlId}`);
      await loadControlInfo();
    };
    actions.appendChild(btn);

  } else if (controlData.status === "abierto") {
    const input = document.createElement("input");
    input.type = "number";
    input.placeholder = "Dorsal";
    input.id = "dorsalInput";

    const btn = document.createElement("button");
    btn.textContent = "✔️ Marcar paso";
    btn.onclick = async () => {
      const dorsal = document.getElementById("dorsalInput").value;
      if (!dorsal) return alert("Introduce dorsal");
      const fd = new FormData();
      fd.append("dorsal", dorsal);
      fd.append("control_id", selectedControlId);
      const r = await fetch("/api/checkin.php", { method:"POST", body:fd });
      const data = await r.json();
      if (data.ok) {
        alert("✅ Marcado correctamente");
        document.getElementById("dorsalInput").value = "";
        await loadControlInfo();
      } else {
        alert("❌ " + data.msg);
      }
    };

    actions.appendChild(input);
    actions.appendChild(btn);

    const abandonBtn = document.createElement("button");
    abandonBtn.textContent = "🚨 Abandonar participante";
    abandonBtn.onclick = () => window.open(`/abandon.php?control_id=${selectedControlId}`, "_blank");
    actions.appendChild(abandonBtn);

    const closeBtn = document.createElement("button");
    closeBtn.textContent = "Cerrar control";
    closeBtn.disabled = controlData.missing > 0;
    closeBtn.onclick = async () => {
      const res = await fetch(`/api/control/close.php?id=${selectedControlId}`);
      const data = await res.json();
      if (data.ok) {
        alert("✅ Control cerrado");
        await loadControlInfo();
      } else {
        alert("❌ " + data.msg);
      }
    };
    actions.appendChild(closeBtn);

  } else if (controlData.status === "cerrado") {
    const p = document.createElement("p");
    p.textContent = "Control cerrado. ✅ Pasados: " + controlData.passed + ", 🚨 Abandonos: " + controlData.abandoned;
    actions.appendChild(p);
  }
}

loadControls();
</script>
</body>
</html>
