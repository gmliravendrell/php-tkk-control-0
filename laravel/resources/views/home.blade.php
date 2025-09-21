<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Trenkakames - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .sidebar {
            width: 260px;
            background: #0d6efd;
            color: #fff;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sidebar .logo {
            text-align: center;
            padding: 1.5rem 1rem;
        }
        .sidebar .logo img {
            max-width: 100%;
            height: 100px;
            object-fit: contain;
        }
        .sidebar .info {
            padding: 1rem;
        }
        .sidebar .info p {
            margin: 0.25rem 0;
        }
        .content {
            flex-grow: 1;
            padding: 2rem;
            background: #f8f9fa;
        }
        footer {
            background: #fff;
            border-top: 1px solid #dee2e6;
            padding: 1rem;
            text-align: center;
        }
        .sponsors img {
            height: 40px;
            margin: 0 10px;
            object-fit: contain;
        }
    </style>
</head>
<body>

    <!-- Layout principal -->
    <div class="d-flex flex-grow-1">

        <!-- Sidebar -->
        <div class="sidebar">
            <div>
                <div class="logo">
                    <img src="/assets/logo.png" alt="Logo Trenkakames">
                </div>
                <div class="info text-center">
                    <h5>Trenkakames</h5>
                    <p id="date"></p>
                    <p id="clock" class="fw-bold"></p>
                    <p id="weather">⏳ Cargando meteo...</p>
                </div>
            </div>
            <div class="text-center p-3 small">
                © {{ date('Y') }} Trenkakames
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="content w-100">
            <div class="container">
                <h1 class="mb-4">Bienvenido a Trenkakames</h1>
                <p class="text-muted mb-4">Selecciona una opción para continuar:</p>

                <div class="row g-4">
                    <div class="col-md-4">
                        <a href="{{ route('control.index') }}" class="btn btn-primary w-100 p-4 shadow-lg">
                            📍 Ir a Control
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-success w-100 p-4 shadow-lg">
                            📊 Ir al Dashboard de Control Central
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('admin.index') }}" class="btn btn-purple w-100 p-4 shadow-lg" 
                           style="background:#6f42c1; color:#fff;">
                            ⚙️ Administración
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('central.index') }}" class="btn btn-purple w-100 p-4 shadow-lg" 
                           style="background:#6f42c1; color:#fff;">
                            ⚙️ Control de Salida
                        </a>
                    </div>
                                       <div class="col-md-4">
                        <a href="{{ route('central.report') }}" class="btn btn-purple w-100 p-4 shadow-lg" 
                           style="background:#6f42c1; color:#fff;">
                            📊 Control Central
                        </a>
                    </div> 
                </div>
            </div>
        </div>
    </div>

    <!-- Footer patrocinadores -->
    <footer>
        <div class="sponsors d-flex justify-content-center align-items-center flex-wrap">
            <img src="/assets/sponsor1.png" alt="Patrocinador 1">
            <img src="/assets/sponsor2.png" alt="Patrocinador 2">
            <img src="/assets/sponsor3.png" alt="Patrocinador 3">
        </div>
    </footer>

<script>
    // Fecha
    document.getElementById("date").textContent =
        new Date().toLocaleDateString("es-ES", { weekday:"long", year:"numeric", month:"long", day:"numeric" });

    // Reloj
    function updateClock() {
        const now = new Date();
        document.getElementById("clock").textContent =
            now.toLocaleTimeString("es-ES", {hour: '2-digit', minute:'2-digit', second:'2-digit'});
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Mapear códigos de tiempo de Open-Meteo a iconos
    function weatherIcon(code) {
        if ([0].includes(code)) return "☀️";
        if ([1,2].includes(code)) return "🌤️";
        if ([3].includes(code)) return "☁️";
        if ([45,48].includes(code)) return "🌫️";
        if ([51,53,55,56,57].includes(code)) return "🌦️";
        if ([61,63,65,80,81,82].includes(code)) return "🌧️";
        if ([66,67].includes(code)) return "🌨️";
        if ([71,73,75,77,85,86].includes(code)) return "❄️";
        if ([95,96,99].includes(code)) return "⛈️";
        return "🌍";
    }

    // Meteo con geolocalización
    function loadWeather(lat, lon) {
        fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true`)
            .then(res => res.json())
            .then(data => {
                const w = data.current_weather;
                const text = `${weatherIcon(w.weathercode)} ${w.temperature}°C · ${w.windspeed} km/h`;
                document.getElementById("weather").textContent = text;
            })
            .catch(() => {
                document.getElementById("weather").textContent = "Meteo no disponible";
            });
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            pos => loadWeather(pos.coords.latitude, pos.coords.longitude),
            err => {
                console.warn("Error ubicación", err);
                document.getElementById("weather").textContent = "Ubicación denegada";
            }
        );
    } else {
        document.getElementById("weather").textContent = "Geolocalización no soportada";
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
