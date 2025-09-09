<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>TKK Dashboard</title>
  <style>
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ccc; padding: .5rem; }
  </style>
</head>
<body>
  <h1>📊 Dashboard TKK</h1>
  <table>
    <thead>
      <tr><th>Dorsal</th><th>Nombre</th><th>Status</th><th>Controles pasados</th></tr>
    </thead>
    <tbody id="tbl"></tbody>
  </table>

  <script>
    async function refresh() {
      const res = await fetch("../api/dashboard.php");
      const data = await res.json();
      const tbl = document.getElementById("tbl");
      tbl.innerHTML = "";
      data.forEach(p => {
        tbl.innerHTML += `
          <tr>
            <td>${p.dorsal}</td>
            <td>${p.name}</td>
            <td>${p.status}</td>
            <td>${p.passed_controls || "-"}</td>
          </tr>`;
      });
    }
    setInterval(refresh, 5000); // refresca cada 5s
    refresh();
  </script>
</body>
</html>
