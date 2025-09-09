<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>TKK Control</title>
  <style>
    body { font-family: sans-serif; padding: 1rem; }
    input, button, select { font-size: 1.2rem; padding: .5rem; margin: .5rem 0; }
  </style>
</head>
<body>
  <h1>📍 Control TKK</h1>

  <!-- selector de control -->
  <label for="control">Selecciona control:</label>
  <select id="control"></select>

  <br>

  <label>Dorsal: <input type="number" id="dorsal"></label>
  <button onclick="checkin()">✔️ Marcar paso</button>

  <script>
    let controls = [];
    let selectedControl = null;

    // cargar controles desde la API
    async function loadControls() {
      const res = await fetch("api/controls.php");
      controls = await res.json();

      const select = document.getElementById("control");
      select.innerHTML = "";

      controls.forEach(c => {
        const opt = document.createElement("option");
        opt.value = c.id;
        opt.textContent = `${c.name} (km_point ${c.km_point})`;
        select.appendChild(opt);
      });

      selectedControl = select.value;

      select.addEventListener("change", () => {
        selectedControl = select.value;
      });
    }

    async function checkin() {
      const dorsal = document.getElementById("dorsal").value;
      if (!dorsal) return alert("Introduce un dorsal");
      if (!selectedControl) return alert("Selecciona un control");

      // buscar participante
      const res = await fetch("../api/participants.php");
      const participants = await res.json();
      const p = participants.find(x => x.dorsal == dorsal);
      if (!p) return alert("Participante no encontrado");

      const fd = new FormData();
      fd.append("participant_id", p.id);
      fd.append("control_id", selectedControl);

      const r = await fetch("/api/checkin.php", { method:"POST", body:fd });
      const data = await r.json();
      if (data.ok) {
        alert(`✅ Marcado en control ${selectedControl}!`);
        document.getElementById("dorsal").value = "";
      } else {
        alert("❌ " + data.msg);
      }
    }

    loadControls();
  </script>
</body>
</html>

