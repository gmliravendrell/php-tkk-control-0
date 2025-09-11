<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Trenkakames</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="bg-white p-8 rounded-2xl shadow-lg text-center space-y-6">
        <h1 class="text-2xl font-bold text-gray-700">Bienvenido a Trenkakames</h1>
        <p class="text-gray-500">Selecciona una opción:</p>

        <div class="flex justify-center space-x-4">
            <!-- Botón para ir al selector de controles -->
            <a href="{{ route('control.index') }}"
                class="px-6 py-3 bg-blue-600 text-white rounded-xl shadow hover:bg-blue-700 transition">
                Ir a Control
            </a>

            <!-- Botón para ir al dashboard de controles -->
            <a href="{{ route('dashboard') }}"
               class="px-6 py-3 bg-green-600 text-white rounded-xl shadow hover:bg-green-700 transition">
               Ir a Dashboard
            </a>
        </div>
    </div>

</body>
</html>
