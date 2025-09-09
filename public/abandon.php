<?php
$control_id = (int)($_GET['control_id'] ?? 0);
if (!$control_id) {
    die("Falta id del control");
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Abandonar participante</title>
<style>
  body { font-family: sans-serif; padding: 1rem; }
  input, button { font-size: 1rem; padding: .5rem; margin: .5rem 0; }
</style>
</head>
<body>
  <h1>🚨 Abandonar participante</h1>
  <p>Control ID: <?= $control_id ?></p>

  <label>Dorsal del participante: <input type="number" id="dorsal"></label>
  <button id="abandonBtn">Abandonar</button>

  <p id="msg"></p>

<script>
document.getElementById("abandonBtn").addEventListener("click", async () => {
  const dorsal = document.getElementById("dorsal").value;
  if (!dorsal) return alert("Introduce dorsal");

  const fd = new FormData();
  fd.append("dorsal", dorsal);
  fd.append("control_id", <?= $control_id ?>);

  const res = await fetch("api/abandon.php", { method:"POST", body: fd });
  const data = await res.json();
  const msg = document.getElementById("msg");

  if (data.ok) {
    msg.textContent = `✅ Participante ${dorsal} marcado como abandonado`;
    document.getElementById("dorsal").value = "";
  } else {
    msg.textContent = `❌ ${data.msg}`;
  }
});
</script>
</body>
</html>
