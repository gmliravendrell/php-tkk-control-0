<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Control {{ $control->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-3xl mx-auto bg-white p-6 rounded-xl shadow space-y-4">
    <h1 class="text-2xl font-bold">📍 Control: {{ $control->name }}</h1>

    <p><strong>Distancia desde meta:</strong> {{ $control->km_point }}</p>
    <p><strong>Responsable:</strong> {{ $control->responsable }}</p>
    <p><strong>Teléfono:</strong> {{ $control->phone }}</p>
    <p><strong>Estado:</strong> {{ ucfirst($control->status) }}</p>

    <div id="controlActions" class="mt-4 space-y-2">
        <!-- Los botones se inyectarán aquí vía JS -->
    </div>

    <a href="{{ route('control.index') }}" class="inline-block mt-4 text-blue-600 hover:underline">⬅ Back to controls</a>
</div>

<script>
const controlId = {{ $control->id }};
const controlStatus = "{{ $control->status }}";
const passed = {{ $control->passed ?? 0 }};
const missing = {{ $control->missing ?? 0 }};
const abandoned = {{ $control->abandoned ?? 0 }};

const actionsDiv = document.getElementById('controlActions');

function createButton(text, color, onClick, disabled=false) {
    const btn = document.createElement('button');
    btn.textContent = text;
    btn.className = `px-4 py-2 text-white rounded ${color} hover:opacity-80`;
    btn.disabled = disabled;
    btn.addEventListener('click', onClick);
    return btn;
}

async function updateControlStatus(newStatus) {
    const res = await fetch(`/api/controls/${controlId}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ status: newStatus })
    });
    const data = await res.json();
    if(res.ok) {
        alert('Estado actualizado con éxito');
        location.reload();
    } else {
        alert(data.message || 'Error al actualizar estado');
    }
}

// Generamos los botones según el estado
if(controlStatus === 'preparing') {
    const btn = createButton('Solicitar Apertura', 'bg-blue-600', () => updateControlStatus('open_requested'));
    actionsDiv.appendChild(btn);

} else if(controlStatus === 'open_requested') {
    const btn = createButton('Esperando autorización', 'bg-gray-600', () => {}, true);
    actionsDiv.appendChild(btn);

} else if(controlStatus === 'opened') {
    actionsDiv.innerHTML = `
        <p><strong>Pasados:</strong> ${passed}</p>
        <p><strong>Faltan:</strong> ${missing}</p>
        <p><strong>Abandonos:</strong> ${abandoned}</p>
        <div class="flex space-x-2 mt-2">
            <input type="number" placeholder="Dorsal" id="dorsalInput" class="border p-2 rounded">
        </div>
    `;

    // Check
    const checkBtn = createButton('✔️ Check', 'bg-green-500', async () => {
        const dorsal = document.getElementById('dorsalInput').value;
        if(!dorsal) return alert('Introduce dorsal');
        const res = await fetch(`/api/checks`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ control_id: controlId, participant_id: dorsal, type: 'check' })
        });
        const data = await res.json();
        if(res.ok) {
            alert('Marcado correctamente');
            location.reload();
        } else {
            alert(data.message || 'Error al marcar');
        }
    });

    // Abandon
    const abandonBtn = createButton('🚨 Abandon', 'bg-red-500', async () => {
        const dorsal = document.getElementById('dorsalInput').value;
        if(!dorsal) return alert('Introduce dorsal');
        const res = await fetch(`/api/checks`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ control_id: controlId, participant_id: dorsal, type: 'abandon' })
        });
        const data = await res.json();
        if(res.ok) {
            alert('Abandono registrado');
            location.reload();
        } else {
            alert(data.message || 'Error al marcar abandono');
        }
    });

    // Close Control
    const closeBtn = createButton('Cerrar Control', 'bg-gray-700', () => updateControlStatus('close_requested'), missing > 0);

    actionsDiv.appendChild(checkBtn);
    actionsDiv.appendChild(abandonBtn);
    actionsDiv.appendChild(closeBtn);

} else if(controlStatus === 'closed') {
    actionsDiv.innerHTML = `<p>Control cerrado. ✅ Pasados: ${passed}, 🚨 Abandonos: ${abandoned}</p>`;
}
</script>

</body>
</html>
