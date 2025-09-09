<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>TKK Dashboard</title>
  <style>
    body { font-family: sans-serif; padding: 1rem; background: #f5f5f5; }
    h1 { text-align: center; margin-bottom: 2rem; }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 2rem;
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
    }
    th, td {
      padding: 0.8rem;
      border-bottom: 1px solid #ddd;
      text-align: center;
    }
    th { background: #007bff; color: #fff; }
    tr:nth-child(even) { background: #f9f9f9; }
    .status { font-weight: bold; }
    .abierto { color: green; }
    .cerrado { color: red; }
    .no-preparado { color: gray; }
  </style>
</head>
<body>
  <h1>📊 Dashboard de Controles</h1>

  <table id="controls">
    <thead>
      <tr>
        <th>Control</th>
        <th>Estado</th>
        <th>Hora cierre</th>
        <th>Pasados</th>
        <th>Faltan</th>
        <th>Abandonos</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>

  <script>
    async function loadDashboard() {
      const res = await fetch("api/dashboard.php");
      const data = await res.json();

      const tbody = document.querySelector("#controls tbody");
      tbody.innerHTML = "";

      data.forEach(c => {
        const tr = document.createElement("tr");

        tr.innerHTML = `
          <td>${c.name}</td>
          <td class="status ${c.status}">${c.status}</td>
          <td>${c.close_time ?? "-"}</td>
          <td>${c.passed}</td>
          <td>${c.missing}</td>
          <td>${c.abandoned}</td>
        `;

        tbody.appendChild(tr);
      });
    }

    // refrescar cada 10s
    loadDashboard();
    setInterval(loadDashboard, 10000);
  </script>
</body>
</html>
