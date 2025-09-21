<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - Controles y Participantes</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-10px); }
        }
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }
        .animate-fade-out {
            animation: fadeOut 0.3s ease-in forwards;
        }
    </style>

    <script>
        function showToast(message, type = "success") {
            const colors = {
                success: "bg-green-500",
                error: "bg-red-500",
                info: "bg-blue-500",
                warning: "bg-yellow-400 text-black"
            };

            const toast = document.createElement("div");
            toast.className = `${colors[type] || colors.success} text-white px-6 py-3 rounded-lg shadow-lg animate-fade-in`;
            toast.textContent = message;

            // Buscar o crear el contenedor global en body
            let container = document.getElementById("toast-container");
            if (!container) {
                container = document.createElement("div");
                container.id = "toast-container";
                container.className = "fixed top-[60px] left-0 w-full flex justify-center z-[9999]";
                document.body.appendChild(container);
            }

            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.remove("animate-fade-in");
                toast.classList.add("animate-fade-out");
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
</head>

<body class="bg-gray-100 min-h-screen p-8">
    <div id="toast-container"
        class="fixed top-[60px] left-0 w-full flex justify-center z-[9999]"></div>

    <header class="sticky top-0 z-50 bg-gray-800 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
            <!-- Nombre de la aplicación -->
            <div class="text-xl font-bold">
                🏃 Carrera Admin
            </div>

            <!-- Nombre de la vista -->
            <div class="text-lg font-semibold">
                Panel de Administración
            </div>

            <!-- Botón volver -->
            <div>
                <a href="{{ url('/') }}" 
                   class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg shadow transition">
                    ⬅ Volver
                </a>
            </div>
        </div>
    </header>
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-2xl shadow-lg mb-10">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-700">Gestión de Controles</h1>

            <!-- Botón importar controles -->
            <button onclick="document.getElementById('importControlsModal').classList.remove('hidden')"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                Importar Controles
            </button>
        </div>

        <!-- Tabla de controles -->
        <table class="w-full border-collapse border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2 text-left">ID</th>
                    <th class="border p-2 text-left">Nombre</th>
                    <th class="border p-2 text-left">Km</th>
                    <th class="border p-2 text-left">Estado</th>
                    <th class="border p-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody id="controls-table-body"></tbody>
        </table>
    </div>

    <div class="max-w-6xl mx-auto bg-white p-6 rounded-2xl shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-700">Gestión de Participantes</h1>

            <!-- Botón importar participantes -->
            <button onclick="document.getElementById('importParticipantsModal').classList.remove('hidden')"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                Importar Participantes
            </button>
        </div>

        <!-- Tabla de participantes -->
        <table class="w-full border-collapse border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Nombre</th>
                    <th class="border p-2">Apellidos</th>
                    <th class="border p-2">DNI</th>
                    <th class="border p-2">Teléfono</th>
                    <th class="border p-2">Emergencia</th>
                    <th class="border p-2">Estado</th>
                    <th class="border p-2">Dinar</th>
                    <th class="border p-2">Sopar</th>
                    <th class="border p-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody id="participants-table-body"></tbody>
        </table>
    </div>
    <!-- Modal importación controles -->
    <div id="importControlsModal" 
        class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-lg w-[32rem] max-h-[80vh] flex flex-col">

        <!-- Encabezado -->
        <h2 class="text-xl font-bold p-6 pb-2">Importar controles</h2>
        <p class="px-6 text-gray-600">⚠ Esto borrará los <b>controles y checks existentes</b>.</p>

        <!-- Contenido -->
        <div class="px-6 py-4">
        <div id="controls-import-messages" class="mb-4 hidden"></div>
        <form id="importControlsForm" class="space-y-4">
            <input type="file" name="csv" accept=".csv" required 
                class="block w-full border border-gray-300 rounded p-2">
        </form>
        </div>

        <!-- Footer fijo -->
        <div class="px-6 py-4 border-t flex justify-end space-x-2 bg-gray-50 rounded-b-2xl">
        <button type="button" 
                onclick="document.getElementById('importControlsModal').classList.add('hidden')" 
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
            Cancelar
        </button>
        <button type="submit" form="importControlsForm"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Aceptar
        </button>
        </div>
    </div>
    </div>



    <!-- Modal importación participantes -->
    <div id="importParticipantsModal" 
        class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-lg w-[32rem] max-h-[80vh] flex flex-col">
        
        <!-- Encabezado -->
        <h2 class="text-xl font-bold p-6 pb-2">Importar participantes</h2>
        <p class="px-6 text-gray-600">⚠ Esto borrará los <b>participantes existentes</b>.</p>
        
        <!-- Contenido scrollable -->
        <div class="px-6 py-4 overflow-y-auto">
            <div id="participants-import-messages" class="mb-4 hidden"></div>
            <form id="importParticipantsForm" class="space-y-4">
                <input type="file" name="csv" accept=".csv" required 
                        class="block w-full border border-gray-300 rounded p-2">
            </form>
        </div>
        
        <!-- Footer fijo -->
        <div class="px-6 py-4 border-t flex justify-end space-x-2 bg-gray-50 rounded-b-2xl">
            <button type="button" 
                    onclick="document.getElementById('importParticipantsModal').classList.add('hidden')" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                Cancelar
            </button>
            <button type="submit" form="importParticipantsForm"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Aceptar
            </button>
        </div>
    </div>
    </div>

    <script>
        // ===================== CONTROLES =====================
        async function loadControls() {
            const tbody = document.getElementById("controls-table-body");
            tbody.innerHTML = "";
            try {
                const res = await fetch("/api/controls");
                const controls = await res.json();
                controls.forEach(control => {
                    tbody.innerHTML += `
                        <tr data-id="${control.id}" class="hover:bg-gray-50">
                            <td class="border p-2">${control.id}</td>
                            <td class="border p-2 name">${control.name}</td>
                            <td class="border p-2 km_point">${control.km_point}</td>
                            <td class="border p-2 status">${control.status}</td>
                            <td class="border p-2 text-center space-x-2">
                                <button onclick="enableControlEdit(${control.id})" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600">Editar</button>
                                <button onclick="deleteControl(${control.id})" class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">Eliminar</button>
                            </td>
                        </tr>`;
                });
            } catch {
                alert("Error cargando controles");
            }
        }

        async function deleteControl(id) {
            if (!confirm("¿Eliminar control?")) return;
            await fetch(`/api/controls/${id}`, { method: "DELETE" });
            loadControls();
        }

        function enableControlEdit(id) {
            const row = document.querySelector(`#controls-table-body tr[data-id="${id}"]`);
            const name = row.querySelector(".name").textContent.trim();
            const km = row.querySelector(".km_point").textContent.trim();
            const status = row.querySelector(".status").textContent.trim();

            row.querySelector(".name").innerHTML = `<input value="${name}" class="border rounded p-1 w-full">`;
            row.querySelector(".km_point").innerHTML = `<input type="number" value="${km}" class="border rounded p-1 w-full">`;
            row.querySelector(".status").innerHTML = `
                <select class="border rounded p-1 w-full">
                    <option value="preparing">preparing</option>
                    <option value="open_requested">open_requested</option>
                    <option value="opened">opened</option>
                    <option value="close_requested">close_requested</option>
                    <option value="closed">closed</option>
                </select>`;
            row.querySelector(".status select").value = status;

            row.querySelector("td:last-child").innerHTML = `
                <button onclick="saveControl(${id})" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">Guardar</button>
                <button onclick="loadControls()" class="px-3 py-1 bg-gray-400 text-white rounded hover:bg-gray-500">Cancelar</button>`;
        }

        async function saveControl(id) {
            const row = document.querySelector(`#controls-table-body tr[data-id="${id}"]`);
            const name = row.querySelector(".name input").value.trim();
            const km = row.querySelector(".km_point input").value.trim();
            const status = row.querySelector(".status select").value;

            await fetch(`/api/controls/${id}`, {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ name, km_point: km, status })
            });
            loadControls();
        }

        document.getElementById("importControlsForm").addEventListener("submit", async (e) => {
            e.preventDefault();
            const file = e.target.csv.files[0];
            const fd = new FormData(); fd.append("csv", file);
            const res = await fetch("/api/admin/controls", { method: "POST", body: fd });
            const result = await res.json();
            showImportResult("controls-import-messages", result);
            loadControls();
            if (!result.errors || Object.keys(result.errors).length === 0) {
                document.getElementById("importControlsModal").classList.add("hidden");
            }
            
        });

        // ===================== PARTICIPANTES =====================
        async function loadParticipants() {
            const tbody = document.getElementById("participants-table-body");
            tbody.innerHTML = "";
            try {
                const res = await fetch("/api/participants");
                const participants = await res.json();
                participants.forEach(p => {
                    tbody.innerHTML += `
                        <tr data-id="${p.id}" class="hover:bg-gray-50">
                            <td class="border p-2">${p.id}</td>
                            <td class="border p-2 first_name">${p.first_name}</td>
                            <td class="border p-2 last_name">${p.last_name}</td>
                            <td class="border p-2 dni">${p.dni ?? ""}</td>
                            <td class="border p-2 phone">${p.phone ?? ""}</td>
                            <td class="border p-2 emergency_phone">${p.emergency_phone ?? ""}</td>
                            <td class="border p-2 status">${p.status ?? ""}</td>
                            <td class="border p-2 lunch_sandwich">${p.lunch_sandwich ? "Sí" : "No"}</td>
                            <td class="border p-2 dinner_sandwich">${p.dinner_sandwich ? "Sí" : "No"}</td>
                            <td class="border p-2 text-center space-x-2">
                                <button onclick="enableParticipantEdit(${p.id})" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600">Editar</button>
                                <button onclick="deleteParticipant(${p.id})" class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">Eliminar</button>
                            </td>
                        </tr>`;
                });
            } catch {
                alert("Error cargando participantes");
            }
        }

        async function deleteParticipant(id) {
            if (!confirm("¿Eliminar participante?")) return;
            await fetch(`/api/participants/${id}`, { method: "DELETE" });
            loadParticipants();
        }

        function enableParticipantEdit(id) {
            const row = document.querySelector(`#participants-table-body tr[data-id="${id}"]`);
            ["first_name", "last_name", "dni", "phone", "emergency_phone"].forEach(f => {
                const cell = row.querySelector("." + f);
                const val = cell.textContent.trim();
                cell.innerHTML = `<input value="${val}" class="border rounded p-1 w-full">`;
            });
            row.querySelector(".status").innerHTML = `
                <select class="border rounded p-1 w-full">
                    <option value="">-</option>
                    <option value="not_presented">not_presented</option>
                    <option value="presented">presented</option>
                    <option value="abandoned">abandoned</option>
                    <option value="finished">finished</option>
                </select>`;
            row.querySelector(".status select").value = row.querySelector(".status").textContent.trim();
            ["lunch_sandwich", "dinner_sandwich"].forEach(f => {
                const cell = row.querySelector("." + f);
                const val = cell.textContent.trim() === "Sí";
                cell.innerHTML = `<input type="checkbox" ${val ? "checked" : ""}>`;
            });
            row.querySelector("td:last-child").innerHTML = `
                <button onclick="saveParticipant(${id})" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">Guardar</button>
                <button onclick="loadParticipants()" class="px-3 py-1 bg-gray-400 text-white rounded hover:bg-gray-500">Cancelar</button>`;
        }

        async function saveParticipant(id) {
            const row = document.querySelector(`#participants-table-body tr[data-id="${id}"]`);
            const data = {
                first_name: row.querySelector(".first_name input").value.trim(),
                last_name: row.querySelector(".last_name input").value.trim(),
                dni: row.querySelector(".dni input").value.trim(),
                phone: row.querySelector(".phone input").value.trim(),
                emergency_phone: row.querySelector(".emergency_phone input").value.trim(),
                status: row.querySelector(".status select").value,
                lunch_sandwich: row.querySelector(".lunch_sandwich input").checked ? 1 : 0,
                dinner_sandwich: row.querySelector(".dinner_sandwich input").checked ? 1 : 0
            };
            await fetch(`/api/participants/${id}`, {
                method: "PUT", headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            });
            loadParticipants();
        }

        document.getElementById("importParticipantsForm").addEventListener("submit", async (e) => {
            e.preventDefault();
            const file = e.target.csv.files[0];
            const fd = new FormData(); fd.append("csv", file);
            const res = await fetch("/api/admin/participants", { method: "POST", body: fd });
            const result = await res.json();
            showImportResult("participants-import-messages", result);
            loadParticipants();
            if (!result.errors || Object.keys(result.errors).length === 0) {
                document.getElementById("importParticipantsModal").classList.add("hidden");
            }
        });

        // ===================== UTILIDAD =====================
        function showImportResult(containerId, result) {
            const div = document.getElementById(containerId);
            let html = "";

            if (result && result.added && (!result.errors || Object.keys(result.errors).length === 0)) {
                html = `<div class="p-3 rounded bg-green-100 text-green-800">
                        ✅ Importación realizada correctamente
                        </div>`;
            } else {
                html = `<div class="p-3 rounded bg-red-100 text-red-800">
                        ❌ Error al importar
                        </div>`;
            }

            div.innerHTML = html;
            div.classList.remove("hidden");
        }
        document.addEventListener("DOMContentLoaded", () => {
            loadControls();
            loadParticipants();
        });
    </script>
</body>
</html>
