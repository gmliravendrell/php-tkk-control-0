<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Escanear QR - Control {{ $control->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</head>
<div id="toast" class="fixed top-5 right-5 bg-green-600 text-white px-4 py-2 rounded-lg shadow hidden z-50">
    <span id="toast-message"></span>
</div>
<audio id="sound-success" preload="auto">
    <source src="/sounds/success.mp3" type="audio/mpeg">
    <source src="/sounds/success.wav" type="audio/wav">
</audio>
<audio id="sound-error" preload="auto">
    <source src="/sounds/alert.mp3" type="audio/mpeg">
    <source src="/sounds/alert.wav" type="audio/wav">
</audio>
<script>
document.addEventListener("DOMContentLoaded", () => {
    // Una sola vez: al primer clic o toque desbloqueamos el audio
    const unlockAudio = () => {
        const success = document.getElementById("sound-success");
        const error = document.getElementById("sound-error");

        // intentamos reproducir en silencio para desbloquear
        [success, error].forEach(audio => {
            if (audio) {
                audio.play().then(() => {
                    audio.pause();   // lo paramos enseguida
                    audio.currentTime = 0;
                }).catch(() => {
                    // Ignoramos errores (normal si no hay interacción previa)
                });
            }
        });

        // Quitamos el listener, ya no hace falta repetir
        document.removeEventListener("click", unlockAudio);
        document.removeEventListener("touchstart", unlockAudio);
    };

    document.addEventListener("click", unlockAudio, { once: true });
    document.addEventListener("touchstart", unlockAudio, { once: true });
});
</script>
<body class="bg-gray-100 p-6">

<div class="max-w-3xl mx-auto bg-white p-6 rounded-xl shadow space-y-4">
    <h1 class="text-2xl font-bold">📷 Escanear QR</h1>
    <p>Control: {{ $control->name }}</p>
    <!-- Modal -->
    <div id="modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-xl p-6 max-w-md w-full text-center">
            <h2 id="modal-title" class="text-xl font-bold mb-4">Aviso</h2>
            <p id="modal-message" class="text-gray-700 mb-6"></p>
            <button id="modal-ok"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                Aceptar
            </button>
        </div>
    </div>
    <div id="reader" style="width: 100%"></div>
    <a href="{{ route('control.show', $control->id) }}" 
       class="inline-block mt-4 text-blue-600 hover:underline">⬅ Volver al control</a>
</div>

<script>
const controlId = {{ $control->id }};

// Cuando se detecta un QR
function onScanSuccess(decodedText, decodedResult) {
    // decodedText contendrá el dorsal o id del participante
    fetch(`/api/checks`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ 
            control_id: controlId, 
            participant_id: decodedText, 
            type: 'check' 
        })
    })
    .then(res => res.json().then(data => ({ok: res.ok, data})))
    .then(({ok, data}) => {
        if(ok){
            alert(`✅ Participante ${decodedText} marcado correctamente`);
            location.reload(); // opcional: recargar para seguir escaneando
        } else {
            alert(data.message || 'Error al marcar');
        }
    })
    .catch(err => console.error(err));
}
function showToast(message) {
    const toast = document.getElementById("toast");
    const msg = document.getElementById("toast-message");
    msg.textContent = message;
    toast.classList.remove("hidden");

    setTimeout(() => {
        toast.classList.add("hidden");
    }, 3000); // se oculta tras 3s
}
function showModal(title, message) {
    document.getElementById("modal-title").textContent = title;
    document.getElementById("modal-message").textContent = message;
    document.getElementById("modal").classList.remove("hidden");

    return new Promise(resolve => {
        const okBtn = document.getElementById("modal-ok");
        okBtn.onclick = () => {
            document.getElementById("modal").classList.add("hidden");
            resolve(true);
        };
    });
}
function onScanSuccess(decodedText) {
    html5QrCode.pause();
    fetch(`/api/checks`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            control_id: controlId,
            participant_id: decodedText,
            type: 'check'
        })
    })
    .then(async res => {
        const data = await res.json();
        if (res.ok) {
            showToast(`Participante ${decodedText} marcado correctamente`);
            playSuccessSound()
        } else {
              playErrorSound()
            await showModal("⚠️ Atención", data.message || "Este participante ya estaba marcado");
        }
    })
    .catch(err => {
        showModal("❌ Error", `No se pudo marcar: ${err}`);
    })
    .finally(() => {setTimeout(() => html5QrCode.resume(), 1000);})
}
function playSuccessSound() {
    const audio = document.getElementById("sound-success");
    audio.currentTime = 0; 
    audio.play();
}

function playErrorSound() {
    const audio = document.getElementById("sound-error");
    audio.currentTime = 0;
    audio.play();
}
// Inicializar escáner
const html5QrCode = new Html5Qrcode("reader");
html5QrCode.start(
    { facingMode: "environment" }, // cámara trasera
    { fps: 10, qrbox: 250 },
    onScanSuccess
).catch(err => {
    console.error("Error al iniciar cámara:", err);
});
</script>

</body>
</html>
