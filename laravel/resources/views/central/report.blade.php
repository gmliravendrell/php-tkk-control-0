<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Auditoría - Control Central</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { padding-top: 1rem; background:#f8f9fa; }

    /* Row highlights */
    .row-passed td { background-color: #e6ffef; }      /* suave verde */
    .row-abandoned td { background-color: #ffecec; }   /* suave rojo */
    .row-not-presented td { background-color: #f2f2f2; color: #6c757d; } /* gris */
    .row-pending td { background-color: #ffffff; }     /* normal */

    .icon-cell { font-size: 1.25rem; text-align: center; width: 64px; }
    .control-info { min-width: 220px; }
    .table-responsive { max-height: 55vh; overflow: auto; }
    .muted-small { font-size: .9rem; color: #6c757d; }
    th.sortable { cursor: pointer; user-select:none; }
    th.sortable .sort-ind { font-size: .75rem; opacity: .6; margin-left:6px; }

    /* Nav pills centradas y con aspecto más visual */
    .nav-pills .nav-link {
      font-size: 1.02rem;
      font-weight: 600;
      border-radius: 30px;
      margin: 0 .25rem;
      padding: .5rem 1.05rem;
    }
    .nav-pills .nav-link.active {
      background-image: linear-gradient(90deg,#0d6efd,#0062cc);
      color: #fff;
      box-shadow: 0 4px 14px rgba(13,110,253,.15);
    }

    .badge-status { text-transform: capitalize; }
    .table thead.sticky-top th { position: sticky; top:0; z-index: 1; background: #fff; }

    /* pagination small spacing */
    .pagination-sm .page-link { padding: .25rem .5rem; }
  </style>
</head>
<body>

<div class="container my-4">

  <!-- Cabecera -->
  <div class="d-flex align-items-center mb-4">
    <h1 class="h4 me-auto">🔍 Auditoría - Control Central</h1>
    <div class="me-2">
      <button class="btn btn-outline-primary" id="btnRefresh" title="Recargar todo">🔄 Actualizar</button>
    </div>
    <a href="{{ route('home') ?? '/' }}" class="btn btn-outline-secondary">🏠 Volver a Home</a>
  </div>

  <!-- Tabs -->
  <ul class="nav nav-pills justify-content-center mb-4" id="auditTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="control-tab" data-bs-toggle="tab" data-bs-target="#control-view" type="button" role="tab" aria-controls="control-view" aria-selected="true">📍 Visión: Control</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="participant-tab" data-bs-toggle="tab" data-bs-target="#participant-view" type="button" role="tab" aria-controls="participant-view" aria-selected="false">👤 Visión: Participante</button>
    </li>
  </ul>

  <div class="tab-content">

    <!-- TAB 1: VISIÓN CONTROL -->
    <div class="tab-pane fade show active" id="control-view" role="tabpanel" aria-labelledby="control-tab">

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Seleccionar control</label>
          <select id="selectControl" class="form-select">
            <option value="">Cargando controles...</option>
          </select>
        </div>

        <div class="col-md-6 control-info">
          <label class="form-label">Información del control</label>
          <div id="controlDetail" class="p-3 border rounded bg-white">
            <div><strong id="ctrlName">-</strong></div>
            <div class="muted-small">Estado: <span id="ctrlStatus">-</span></div>
            <div class="muted-small">Km: <span id="ctrlKm">-</span></div>
            <div class="muted-small">Responsable: <span id="ctrlResp">-</span></div>
            <div class="muted-small">Tel: <span id="ctrlPhone">-</span></div>
            <div class="mt-2">
              <span class="badge bg-success" id="badgePassed">Pasados: 0</span>
              <span class="badge bg-danger" id="badgeAbandoned">Abandonos: 0</span>
              <span class="badge bg-secondary" id="badgeMissing">Pendientes: 0</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabla participantes control -->
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="card-title mb-0">Participantes (visión del control)</h5>
            <div class="d-flex align-items-center">
              <label class="me-2 mb-0">Mostrar</label>
              <select id="pageSize" class="form-select form-select-sm d-inline-block me-3" style="width:80px">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </select>
              <button class="btn btn-outline-secondary btn-sm" id="btnReloadChecks" title="Recargar checks del control">🔁 Recargar checks</button>
            </div>
          </div>
            <div class="mb-3">
            <label for="statusFilter" class="form-label">Filtrar por estado:</label>
            <select id="statusFilter" class="form-select w-auto d-inline-block">
                <option value="all">Todos</option>
                <option value="presentado">Presentado</option>
                <option value="abandonado">Abandonado</option>
                <option value="pendiente">Pendiente</option>
            </select>
            </div>

          <div class="table-responsive mt-2">
            <table class="table table-hover table-sm align-middle" id="tableControlParticipants">
              <thead class="table-light sticky-top">
                <tr>
                  <th class="sortable" data-sort="id">Dorsal <span class="sort-ind">▲▼</span></th>
                  <th class="sortable" data-sort="name">Nombre <span class="sort-ind">▲▼</span></th>
                  <th>Teléfono</th>
                  <th>Tel. Emerg.</th>
                  <th class="text-center">Estado</th>
                </tr>
              </thead>
              <tbody id="tbodyControlParticipants"></tbody>
            </table>
          </div>

          <!-- Paginación -->
          <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
              <small class="text-muted">Registros <span id="displayRange">0-0</span> de <span id="totalRecords">0</span></small>
            </div>
            <nav>
              <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 2: VISIÓN PARTICIPANTE -->
    <div class="tab-pane fade" id="participant-view" role="tabpanel" aria-labelledby="participant-tab">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Participantes (visión personal)</h5>
          <div class="mb-3">
            <label for="statusFilterAcc" class="form-label">Filtrar por estado:</label>
            <select id="statusFilterAcc" class="form-select w-auto d-inline-block">
                <option value="all">Todos</option>
                <option value="presentado">Presentado</option>
                <option value="abandonado">Abandonado</option>
                <option value="pendiente">Pendiente</option>
            </select>
            </div>
          <div id="participantsAccordion" class="accordion mt-3">
            <div class="text-center text-muted py-3" id="participantsLoading">Cargando participantes...</div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
/*
  Auditoría - vista
  Endpoints usados:
   - GET /api/controls
   - GET /api/controls/{id}                -> detalle control
   - GET /api/controls/{id}/checks        -> checks 'check' para control
   - GET /api/checks/abandons             -> abandonos globales
   - GET /api/participants
   - GET /api/participants/{id}
*/

document.addEventListener('DOMContentLoaded', () => {
  const selectControl = document.getElementById('selectControl');
  const tbody = document.getElementById('tbodyControlParticipants');
  const pagination = document.getElementById('pagination');
  const pageSizeSel = document.getElementById('pageSize');
  const btnRefresh = document.getElementById('btnRefresh');
  const statusFilter = document.getElementById('statusFilter');
  let currentFilter = 'all';
  const btnReloadChecks = document.getElementById('btnReloadChecks');

  // control detail elems
  const ctrlName = document.getElementById('ctrlName');
  const ctrlStatus = document.getElementById('ctrlStatus');
  const ctrlKm = document.getElementById('ctrlKm');
  const ctrlResp = document.getElementById('ctrlResp');
  const ctrlPhone = document.getElementById('ctrlPhone');
  const badgePassed = document.getElementById('badgePassed');
  const badgeAbandoned = document.getElementById('badgeAbandoned');
  const badgeMissing = document.getElementById('badgeMissing');
  const displayRange = document.getElementById('displayRange');
  const totalRecords = document.getElementById('totalRecords');
    const statusFilterAcc = document.getElementById('statusFilterAcc');
    let currentFilterAcc = 'all';

  let controls = [];
  let participants = [];
  let currentPassedSet = new Set();      // dorsales que han pasado en control seleccionado
  let globalAbandonsMap = new Map();     // dorsal -> abandon info
  let currentControl = null;

  // pagination + sort
  let currentPage = 1;
  let pageSize = parseInt(pageSizeSel.value);
  let sortState = { key: 'id', dir: 'asc' };
    function getFilteredParticipantsAcc() {
    if (currentFilterAcc === 'all') return participants;
    return participants.filter(p => (p.status || '').toLowerCase() === currentFilterAcc);
    }

  // ------------------ LOADERS ------------------
  async function loadControls() {
    try {
      const res = await fetch('/api/controls');
      if (!res.ok) throw new Error('No se pudieron cargar controles');
      controls = await res.json();
      // ordenar por km_point asc
      controls.sort((a,b) => (parseFloat(a.km_point||0) - parseFloat(b.km_point||0)));
      populateControlsSelect();
    } catch (err) {
      console.error(err);
      selectControl.innerHTML = '<option value="">Error cargando controles</option>';
    }
  }

  async function loadParticipants() {
    try {
      const res = await fetch('/api/participants');
      if (!res.ok) throw new Error('No se pudieron cargar participantes');
      participants = await res.json();
      buildParticipantsAccordion(); // actualiza también tab 2
    } catch (err) {
      console.error(err);
      participants = [];
      document.getElementById('participantsAccordion').innerHTML = '<div class="text-danger p-3">Error cargando participantes</div>';
    }
  }
    function getFilteredParticipants() {
    if (currentFilter === 'all') return participants;
    return participants.filter(p => (p.status || '').toLowerCase() === currentFilter);
    }

  async function loadAbandons() {
    try {
      const res = await fetch('/api/checks/abandons');
      if (!res.ok) {
        globalAbandonsMap = new Map();
        return;
      }
      const abandons = await res.json();
      globalAbandonsMap = new Map();
      abandons.forEach(a => {
        const dorsalNum = Number(a.dorsal);
        globalAbandonsMap.set(dorsalNum, a);
      });
    } catch (err) {
      console.error('Error cargando abandonos', err);
      globalAbandonsMap = new Map();
    }
  }

  async function loadChecksForControl(controlId) {
    currentPassedSet = new Set();
    if (!controlId) return;
    try {
      const res = await fetch(`/api/controls/${controlId}/checks`);
      if (!res.ok) {
        console.warn('No checks endpoint OK for control', controlId);
        return;
      }
      const checks = await res.json();
      checks.forEach(c => currentPassedSet.add(Number(c.dorsal)));
    } catch (err) {
      console.error('Error cargando checks del control', err);
      currentPassedSet = new Set();
    }
  }

  // ------------------ RENDER ------------------
  function populateControlsSelect() {
    selectControl.innerHTML = '';
    if (!controls.length) {
      selectControl.innerHTML = '<option value="">No hay controles</option>';
      return;
    }
    selectControl.innerHTML = '<option value="">-- Selecciona un control --</option>';
    controls.forEach(c => {
      const opt = document.createElement('option');
      opt.value = c.id;
      opt.textContent = `${c.name} — km ${c.km_point ?? '-' } (${c.type ?? 'control'})`;
      selectControl.appendChild(opt);
    });
    // seleccionar primero si hay alguno
    if (controls.length) {
      selectControl.value = controls[0].id;
      onControlSelected(controls[0].id);
    }
  }

  async function onControlSelected(controlId) {
    if (!controlId) return;
    currentControl = controls.find(c => String(c.id) === String(controlId)) || null;
    // cargar detalle desde API (por si difiere)
    try {
      const detailRes = await fetch(`/api/controls/${controlId}`);
      if (detailRes.ok) {
        currentControl = await detailRes.json();
      }
    } catch (err) { /* silent */ }

    renderControlDetail(currentControl);
    // cargar checks and abandonos to compute states
    await loadAbandons();
    await loadChecksForControl(controlId);
    // render participants table (use participants array + sets)
    currentPage = 1;
    renderControlParticipantsTable();
  }

  function renderControlDetail(ctrl) {
    ctrlName.textContent = ctrl?.name ?? '-';
    ctrlStatus.textContent = ctrl?.status ?? '-';
    ctrlKm.textContent = ctrl?.km_point ?? '-';
    ctrlResp.textContent = ctrl?.responsible ?? '-';
    ctrlPhone.textContent = ctrl?.phone ?? '-';
    badgePassed.textContent = `Pasados: ${ctrl?.passed ?? 0}`;
    badgeAbandoned.textContent = `Abandonos: ${ctrl?.abandoned ?? 0}`;
    badgeMissing.textContent = `Pendientes: ${ctrl?.missing ?? 0}`;
  }

  function determineStateForParticipant(p) {
    const dorsal = Number(p.id);
    // Si ha pasado por este control -> passed
    if (currentPassedSet.has(dorsal)) return 'passed';
    // Si figura en abandonos globales o tiene estado abandoned -> abandoned
    if (p.status === 'abandoned' || globalAbandonsMap.has(dorsal)) return 'abandoned';
    // If not presented
    if (p.status === 'not_presented') return 'not_presented';
    // Presented or finished -> pending (still must pass this control)
    if (p.status === 'presented' || p.status === 'finished') return 'pending';
    return 'pending';
  }

  function iconForState(p, state) {
    if (state === 'passed') return `<i class="bi bi-check-circle-fill text-success" title="Pasado"></i>`;
    if (state === 'abandoned') return `<i class="bi bi-skull-fill text-danger" title="Abandonado"></i>`;
    if (state === 'not_presented') return `<i class="bi bi-x-circle-fill text-secondary" title="No presentado"></i>`;
    // pending: show runner emoji with gender
    const g = (p.gender || '').toString().toLowerCase();
    if (g === 'female' || g === 'f') return `🏃‍♀️`;
    return `🏃‍♂️`;
  }

  function stateBadge(state) {
    if (state === 'passed') return `<span class="badge bg-success badge-status">pasado</span>`;
    if (state === 'abandoned') return `<span class="badge bg-danger badge-status">abandonado</span>`;
    if (state === 'not_presented') return `<span class="badge bg-secondary badge-status">no presentado</span>`;
    return `<span class="badge bg-light text-dark badge-status">pendiente</span>`;
  }

  function renderControlParticipantsTable() {
    tbody.innerHTML = '';
    if (!participants || !participants.length) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay participantes.</td></tr>';
      totalRecords.textContent = '0';
      displayRange.textContent = '0-0';
      renderPagination();
      return;
    }

    // Sort participants copy
    const arr = participants.slice();
    arr.sort((a,b) => {
      if (sortState.key === 'id') {
        const av = Number(a.id), bv = Number(b.id);
        return sortState.dir === 'asc' ? av - bv : bv - av;
      } else {
        const av = (a.first_name+' '+(a.last_name||'')).toLowerCase();
        const bv = (b.first_name+' '+(b.last_name||'')).toLowerCase();
        if (av < bv) return sortState.dir === 'asc' ? -1 : 1;
        if (av > bv) return sortState.dir === 'asc' ? 1 : -1;
        return 0;
      }
    });

    // Pagination slice
    const start = (currentPage - 1) * pageSize;
    const end = start + pageSize;
    const slice = arr.slice(start, end);

    slice.forEach(p => {
      const state = determineStateForParticipant(p);
      const icon = iconForState(p, state);
      const badge = stateBadge(state);

      const tr = document.createElement('tr');
      tr.classList.add(
        state === 'passed' ? 'row-passed' :
        state === 'abandoned' ? 'row-abandoned' :
        state === 'not_presented' ? 'row-not-presented' : 'row-pending'
      );

      tr.innerHTML = `
        <td>${escapeHtml(String(p.id))}</td>
        <td>${escapeHtml((p.first_name ?? '') + ' ' + (p.last_name ?? ''))}</td>
        <td>${escapeHtml(p.phone ?? '')}</td>
        <td>${escapeHtml(p.emergency_phone ?? '')}</td>
        <td class="text-center">${icon} <div class="d-block mt-1">${badge}</div></td>
      `;
      tbody.appendChild(tr);
    });

    // update counters & display range
    totalRecords.textContent = String(participants.length);
    const from = participants.length ? start + 1 : 0;
    const to = Math.min(participants.length, end);
    displayRange.textContent = `${from}-${to}`;

    renderPagination();
    attachSortingHandlers();
  }

  // ------------------ PAGINATION & SORT ------------------
  function renderPagination() {
    pagination.innerHTML = '';
    const totalPages = Math.max(1, Math.ceil(participants.length / pageSize));
    const createPageItem = (i, label = null) => {
      const li = document.createElement('li');
      li.className = `page-item ${i === currentPage ? 'active' : ''}`;
      li.innerHTML = `<a class="page-link" href="#" data-page="${i}">${label ?? i}</a>`;
      li.addEventListener('click', e => {
        e.preventDefault();
        const page = Number(e.currentTarget.querySelector('a').dataset.page);
        if (!isNaN(page)) {
          currentPage = page;
          renderControlParticipantsTable();
        }
      });
      return li;
    };

    // Prev
    const prev = document.createElement('li');
    prev.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prev.innerHTML = `<a class="page-link" href="#" data-page="${Math.max(1,currentPage-1)}">‹</a>`;
    prev.querySelector('a').addEventListener('click', e => { e.preventDefault(); if (currentPage>1){ currentPage--; renderControlParticipantsTable(); }});
    pagination.appendChild(prev);

    // show up to 7 pages centered around current
    const total = totalPages;
    let startPage = Math.max(1, currentPage - 3);
    let endPage = Math.min(total, startPage + 6);
    if (endPage - startPage < 6) startPage = Math.max(1, endPage - 6);

    for (let i = startPage; i <= endPage; i++) {
      pagination.appendChild(createPageItem(i));
    }

    // Next
    const next = document.createElement('li');
    next.className = `page-item ${currentPage === total ? 'disabled' : ''}`;
    next.innerHTML = `<a class="page-link" href="#" data-page="${Math.min(total,currentPage+1)}">›</a>`;
    next.querySelector('a').addEventListener('click', e => { e.preventDefault(); if (currentPage<total){ currentPage++; renderControlParticipantsTable(); }});
    pagination.appendChild(next);
  }

  function attachSortingHandlers() {
    document.querySelectorAll('#tableControlParticipants th.sortable').forEach(th => {
      if (th.dataset.bound) return; // already bound
      th.dataset.bound = '1';
      th.addEventListener('click', () => {
        const key = th.dataset.sort;
        if (sortState.key === key) sortState.dir = (sortState.dir === 'asc' ? 'desc' : 'asc');
        else { sortState.key = key; sortState.dir = 'asc'; }
        currentPage = 1;
        renderControlParticipantsTable();
      });
    });
  }

  // ------------------ PARTICIPANT VIEW (ACCORDION) ------------------
  function buildParticipantsAccordion() {
    const container = document.getElementById('participantsAccordion');
    container.innerHTML = '';
    if (!participants.length) {
      container.innerHTML = '<div class="text-center text-muted p-3">No hay participantes.</div>';
      return;
    }

    participants.forEach(p => {
      const dorsal = p.id;
      const name = (p.first_name ?? '') + ' ' + (p.last_name ?? '');
      const status = p.status ?? 'not_presented';
      const icon = status === 'not_presented' ? '<i class="bi bi-x-circle-fill text-secondary"></i>' :
                   status === 'abandoned' ? '<i class="bi bi-skull-fill text-danger"></i>' :
                   '<i class="bi bi-person-fill text-primary"></i>';

      const idHeading = `heading-${dorsal}`;
      const idCollapse = `collapse-${dorsal}`;

      const card = document.createElement('div');
      card.className = 'accordion-item';
      card.innerHTML = `
        <h2 class="accordion-header" id="${idHeading}">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
            data-bs-target="#${idCollapse}" aria-expanded="false" aria-controls="${idCollapse}">
            <div class="me-3" style="width:38px;text-align:center">${icon}</div>
            <div class="flex-grow-1 text-start">
              <div><strong>#${escapeHtml(String(dorsal))} — ${escapeHtml(name)}</strong></div>
              <div class="muted-small">${escapeHtml(p.phone ?? '')} · Emerg: ${escapeHtml(p.emergency_phone ?? '')}</div>
            </div>
            <div class="ms-2"><span class="badge ${statusBadgeClass(status)} badge-status">${escapeHtml(status)}</span></div>
          </button>
        </h2>
        <div id="${idCollapse}" class="accordion-collapse collapse" aria-labelledby="${idHeading}">
          <div class="accordion-body p-2">
            <div id="participantDetail-${dorsal}" class="p-0">Pulsa para cargar detalles...</div>
          </div>
        </div>
      `;
      container.appendChild(card);

      // add event: on expand always reload details (as requested)
      const collapseEl = card.querySelector(`#${idCollapse}`);
      collapseEl.addEventListener('show.bs.collapse', () => fetchAndRenderParticipantChecks(dorsal));
    });

    const loading = document.getElementById('participantsLoading');
    if (loading) loading.remove();
  }

  function statusBadgeClass(status) {
    if (status === 'not_presented') return 'bg-secondary';
    if (status === 'presented') return 'bg-success';
    if (status === 'abandoned') return 'bg-danger';
    if (status === 'finished') return 'bg-dark';
    return 'bg-secondary';
  }

  // Always fetch fresh details when expanding
  async function fetchAndRenderParticipantChecks(participantId) {
    const container = document.getElementById(`participantDetail-${participantId}`);
    if (!container) return;
    container.innerHTML = 'Cargando registros...';
    try {
      const res = await fetch(`/api/participants/${participantId}`);
      if (!res.ok) {
        container.innerHTML = '<div class="text-danger">No se pudo cargar la información.</div>';
        return;
      }
      const p = await res.json();
      const checks = p.checks ?? [];
      if (!checks.length) {
        container.innerHTML = '<div class="text-muted">No hay registros de paso para este participante.</div>';
        return;
      }
      const rows = checks.sort((a,b) => new Date(b.checked_at||b.timestamp||0)-new Date(a.checked_at||a.timestamp||0))
        .map(ch => {
          const ctrlName = (ch.control && ch.control.name) ? ch.control.name : ('#'+(ch.control_id ?? ''));
          const when = ch.checked_at ?? ch.timestamp ?? '-';
          const what = ch.type === 'abandon' ? 'Abandono' : 'Pasó';
          return `<tr>
            <td>${escapeHtml(ctrlName)}</td>
            <td>${escapeHtml(what)}</td>
            <td>${escapeHtml(when)}</td>
          </tr>`;
        }).join('');
      container.innerHTML = `
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Control</th><th>Acción</th><th>Hora</th></tr></thead>
            <tbody>${rows}</tbody>
          </table>
        </div>
      `;
    } catch (err) {
      console.error(err);
      container.innerHTML = '<div class="text-danger">Error cargando checks.</div>';
    }
  }

  // ------------------ UTILS ------------------
  function escapeHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s)
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'",'&#039;');
  }

  function statusBadgeClass(status) {
    if (status === 'not_presented') return 'bg-secondary';
    if (status === 'presented') return 'bg-success';
    if (status === 'abandoned') return 'bg-danger';
    if (status === 'finished') return 'bg-dark';
    return 'bg-secondary';
  }

  // ------------------ EVENTS ------------------
  pageSizeSel.addEventListener('change', () => {
    pageSize = parseInt(pageSizeSel.value);
    currentPage = 1;
    renderControlParticipantsTable();
    statusFilter.addEventListener('change', () => {
        currentFilter = statusFilter.value;
        currentPage = 1;
        renderTable();
        });
  });

  selectControl.addEventListener('change', (e) => {
    const id = e.target.value;
    if (id) onControlSelected(id);
  });

  btnRefresh.addEventListener('click', async () => {
    await refreshAll();
  });

  btnReloadChecks.addEventListener('click', async () => {
    if (selectControl.value) {
      await loadChecksForControl(selectControl.value);
      renderControlParticipantsTable();
    }
  });

  // ------------------ BOOTSTRAP INITIAL LOAD ------------------
  async function init() {
    await Promise.all([loadControls(), loadParticipants(), loadAbandons()]);
    // controls select is populated by loadControls -> onControlSelected is called inside
  }

  async function refreshAll() {
    await Promise.all([loadControls(), loadParticipants(), loadAbandons()]);
    if (selectControl.value) onControlSelected(selectControl.value);
  }

  // kick off
  init().catch(err => console.error('Init error', err));
});
</script>

</body>
</html>
