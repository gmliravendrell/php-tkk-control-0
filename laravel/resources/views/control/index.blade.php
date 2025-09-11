<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Controls</title>
    <style>
        body { font-family: sans-serif; padding: 1rem; }
        select, button { font-size: 1rem; padding: .5rem; margin: .5rem 0; }
    </style>
</head>
<body>
    <h1>📍 Select Control</h1>

    <label for="controlSelect">Choose a control:</label>
    <select id="controlSelect">
        <option value="">-- Select --</option>
        @foreach($controls as $control)
            <option value="{{ $control->id }}">{{ $control->name }} (km {{ $control->km_point }})</option>
        @endforeach
    </select>

    <button id="goButton" disabled>Go to Control</button>

    <script>
        const select = document.getElementById('controlSelect');
        const button = document.getElementById('goButton');

        select.addEventListener('change', () => {
            button.disabled = !select.value;
        });

        button.addEventListener('click', () => {
            const id = select.value;
            if(id) {
                // Redirige a la ruta show del control
                window.location.href = `/control/${id}`;
            }
        });
    </script>
</body>
</html>
