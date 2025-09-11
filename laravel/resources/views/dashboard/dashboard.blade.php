<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard de Controles</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-5xl mx-auto space-y-6">
    <h1 class="text-3xl font-bold text-gray-700">📊 Dashboard de Controles</h1>

    @foreach($controls as $control)
        @php
            // Definir texto, color y estado del botón
            switch($control->status){
                case 'preparing':
                    $btnText = 'En preparación';
                    $btnColor = 'bg-gray-400 cursor-not-allowed';
                    $btnDisabled = 'disabled';
                    $nextStatus = null;
                    break;
                case 'open_requested':
                    $btnText = 'Autorizar Apertura';
                    $btnColor = 'bg-gray-500 hover:bg-gray-600 cursor-pointer';
                    $btnDisabled = '';
                    $nextStatus = 'opened';
                    break;
                case 'opened':
                    $btnText = 'Abierto';
                    $btnColor = 'bg-green-500 cursor-not-allowed';
                    $btnDisabled = 'disabled';
                    $nextStatus = null;
                    break;
                case 'close_requested':
                    $btnText = 'Autorizar Cierre';
                    $btnColor = 'bg-amber-800 hover:bg-amber-900 cursor-pointer';
                    $btnDisabled = '';
                    $nextStatus = 'closed';
                    break;
                case 'closed':
                    $btnText = 'Cerrado';
                    $btnColor = 'bg-black cursor-not-allowed';
                    $btnDisabled = 'disabled';
                    $nextStatus = null;
                    break;
                default:
                    $btnText = ucfirst($control->status);
                    $btnColor = 'bg-gray-400';
                    $btnDisabled = 'disabled';
                    $nextStatus = null;
            }
        @endphp

        <div class="bg-white p-4 rounded-xl shadow flex justify-between items-center space-x-4">
            <div>
                <h2 class="text-xl font-semibold">{{ $control->name }} (km {{ $control->km_point }})</h2>
                <p>Responsable: {{ $control->responsable }} - Tel: {{ $control->phone }}</p>
                <p>Estado: {{ ucfirst($control->status) }}</p>
                <p>
                    Apertura: {{ $control->opened_at ?? '—' }} |
                    Cierre: {{ $control->closed_at ?? '—' }}
                </p>
                <p>
                    Pasados: {{ $control->passed }} |
                    Faltan: {{ $control->missing }} |
                    Abandonos: {{ $control->abandoned }}
                </p>
            </div>

            <div class="flex flex-col space-y-2">
                <button 
                    class="px-4 py-2 text-white rounded {{ $btnColor }}" 
                    data-id="{{ $control->id }}" 
                    data-next="{{ $nextStatus }}"
                    {{ $btnDisabled }}>
                    {{ $btnText }}
                </button>

                <!-- Aquí irá más adelante el gráfico de tarta -->
                <canvas id="chart-{{ $control->id }}" width="200" height="200"></canvas>
            </div>
        </div>
    @endforeach
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.querySelectorAll('button[data-id]').forEach(btn => {
    btn.addEventListener('click', async () => {
        const id = btn.dataset.id;
        const next = btn.dataset.next;

        if(!next) return; // Si no hay siguiente estado, no hace nada

        try {
            const res = await fetch(`/api/controls/${id}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status: next })
            });

            if(res.ok){
                const data = await res.json();
                alert(`Control ${data.control.name} actualizado a: ${next}!`);
                location.reload();
            } else {
                const err = await res.json();
                alert(err.error);
            }
        } catch(e){
            console.error(e);
            alert('Error al actualizar el control');
        }
    });
});

// Graficar con Chart.js
@foreach($controls as $control)
    const ctx{{ $control->id }} = document.getElementById('chart-{{ $control->id }}').getContext('2d');
    new Chart(ctx{{ $control->id }}, {
        type: 'doughnut',
        data: {
            labels: ['Faltan', 'Pasados', 'Abandonos'],
            datasets: [{
                data: [{{ $control->missing }}, {{ $control->passed }}, {{ $control->abandoned }}],
                backgroundColor: ['#f59e0b', '#10b981', '#ef4444']
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
@endforeach
</script>

</body>
</html>
