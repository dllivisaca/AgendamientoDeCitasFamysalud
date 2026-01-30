/**
 * Admin - Crear cita (Wizard secuencial)
 * IDs reales según tu modal (ca_*)
 * Requiere: jQuery + Bootstrap 5 modal
 */

(function () {
  console.log('[CreateAppt] JS cargado ✅');
  const $ = window.jQuery;
  if (!$) return;

  // =========================
  // 0) SELECTORES (YA CON TUS IDS)
  // =========================
  const UI = {
    modal: '#modalCreateAppointment',
    form: '#formCreateAppointment',
    errorBox: '#createApptError',

    // Step 1-4
    categorySelect: '#ca_category_id',      // Área
    serviceSelect: '#ca_service_select',    // Servicio
    employeeSelect: '#ca_employee_select',  // Profesional
    modeSelect: '#ca_mode_select',          // Modalidad

    // Hidden required by backend store
    hidEmployeeId: '#ca_employee_id',
    hidServiceId: '#ca_service_id',
    hidDate: '#ca_appointment_date',
    hidTime: '#ca_appointment_time',
    hidEnd: '#ca_appointment_end_time',
    hidHoldId: '#ca_hold_id',

    // Calendar + slots
    calendarContainer: '#ca_calendar_container',
    slotsContainer: '#ca_slots_container',
    selectedSlotLabel: '#ca_selected_slot_label',

    // Patient
    patientName: '#ca_patient_full_name',
    patientDob: '#ca_patient_dob',
    patientPhone: '#ca_patient_phone',
    patientEmail: '#ca_patient_email',
    patientDocType: '#ca_patient_doc_type',
    patientDocNumber: '#ca_patient_doc_number',
    patientAddress: '#ca_patient_address',
    patientNotes: '#ca_patient_notes',

    // Billing
    billingSameChk: '#ca_billing_same_as_patient',
    minorHint: '#ca_minor_hint',
    billingName: '#ca_billing_name',
    billingDocType: '#ca_billing_doc_type',
    billingDocNumber: '#ca_billing_doc_number',
    billingEmail: '#ca_billing_email',
    billingPhone: '#ca_billing_phone',
    billingPhoneUI: '#ca_billing_phone_ui',
    billingAddress: '#ca_billing_address',

    // Payment
    paymentMethod: '#ca_payment_method',         // cash | transfer | card
    amount: '#ca_amount',
    amountPaid: '#ca_amount_paid',
    paymentStatus: '#ca_payment_status',
    paidAt: '#ca_payment_paid_at',
    clientTxWrap: '#ca_client_tx_wrap',
    clientTx: '#ca_client_transaction_id',
    paymentNotes: '#ca_payment_notes',
    transferBlock: '#ca_transfer_block',
    transferBankOrigin: '#ca_transfer_bank_origin',
    transferPayerName: '#ca_transfer_payer_name',
    transferDate: '#ca_transfer_date',
    transferReference: '#ca_transfer_reference',
    trFile: '#ca_tr_file',

    // Channel/status/consent
    channel: '#ca_appointment_channel',
    status: '#ca_status',

    // Submit
    submitBtn: '#btnSubmitCreateAppointment',

    csrfMeta: 'meta[name="csrf-token"]'
  };

  // =========================
  // 1) ENDPOINTS (AJUSTA SI TU RUTA ES OTRA)
  // =========================
  const API = {
    categories: '/admin/appointments/create/options/categories',

    // estos 3 reciben querystring (ver abajo)
    services: '/admin/appointments/create/options/services',
    employees: '/admin/appointments/create/options/employees',
    slots: '/appointments/reschedule/slots',

    createHold: '/holds',
    store: '/admin/appointments/create/store'
    };

  // =========================
  // 2) STATE
  // =========================
  const State = {
    categoryId: null,
    serviceId: null,
    employeeId: null,
    mode: null,

    holdId: null,
    date: null,
    start: null,
    end: null,

    // calendar
    calYear: null,
    calMonth: null, // 0-11

    lastPaymentMethod: null
  };

  // =========================
  // 3) HELPERS
  // =========================
  function csrf() {
    const el = document.querySelector(UI.csrfMeta);
    return el ? el.getAttribute('content') : '';
  }

  function showError(msg) {
    $(UI.errorBox).removeClass('d-none').text(msg || 'Ocurrió un error.');
  }

  function hideError() {
    $(UI.errorBox).addClass('d-none').text('');
  }

  function disable(sel, yes = true) {
    $(sel).prop('disabled', yes);
  }

  function ensureSelectPlaceholder(sel, text = 'Seleccione...') {
    const $sel = $(sel);
    if (!$sel.length) return;

    // Si no existe opción value="" la creamos arriba
    if ($sel.find('option[value=""]').length === 0) {
        $sel.prepend(`<option value="">${text}</option>`);
    }
    }

    function resetToPlaceholder(sel) {
        const $sel = $(sel);
        if (!$sel.length) return;

        // 1) Asegurar placeholder value=""
        ensureSelectPlaceholder(sel, 'Seleccione...');

        // 2) Quitar selección REAL de todas las opciones y seleccionar placeholder
        $sel.find('option').prop('selected', false);
        $sel.find('option[value=""]').prop('selected', true);

        // 3) Setear value vacío (doble seguro)
        $sel.val('');

        // 4) Refrescar UI (Select2 + normal)
        if ($sel.hasClass('select2-hidden-accessible') || $sel.data('select2')) {
            // Este es el que realmente hace que el texto visible vuelva a "Seleccione..."
            $sel.trigger('change.select2');
        }
        $sel.trigger('change');
    }

  function resetSelect(sel) {
    $(sel).val('');
    $(sel).find('option').not(':first').remove();
  }

  function setHidden(sel, val) {
    $(sel).val(val == null ? '' : String(val));
  }

  function toYMD(dateObj) {
    const y = dateObj.getFullYear();
    const m = String(dateObj.getMonth() + 1).padStart(2, '0');
    const d = String(dateObj.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
  }

  function normalizeMoney2(val) {
    let s = String(val ?? '').trim();

    // permitir coma como decimal
    s = s.replace(',', '.');

    // dejar solo dígitos y puntos
    s = s.replace(/[^\d.]/g, '');

    // si hay más de un punto, dejar solo el primero
    const firstDot = s.indexOf('.');
    if (firstDot !== -1) {
        s = s.slice(0, firstDot + 1) + s.slice(firstDot + 1).replace(/\./g, '');
    }

    if (s === '' || s === '.') return '';

    const n = Number(s);
    if (Number.isNaN(n)) return '';

    return n.toFixed(2);
    }

  function isAdult(dobYMD) {
    if (!dobYMD) return false;
    const dob = new Date(dobYMD + 'T00:00:00');
    if (isNaN(dob.getTime())) return false;

    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
    return age >= 18;
  }

  async function fetchJSON(url, { method = 'GET', body = null } = {}) {
        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrf()
        };

        // Solo poner Content-Type cuando ENVIAS JSON
        if (body) headers['Content-Type'] = 'application/json';

        const res = await fetch(url, {
            method,
            headers,
            credentials: 'same-origin', // ✅ asegura cookies/sesión en Laravel
            body: body ? JSON.stringify(body) : null
        });

        // Leemos como texto primero (por si el backend devolvió HTML)
        const raw = await res.text();

        let data = {};
        try {
            data = raw ? JSON.parse(raw) : {};
        } catch (e) {
            // Si devuelve HTML (login, error 500 con página, etc.)
            data = { message: raw?.slice(0, 200) || 'Respuesta no JSON.' };
        }

        if (!res.ok) {
            const msg = data?.message || `Error HTTP ${res.status}`;
            throw new Error(msg);
        }

        return data;
     }

  async function fetchForm(url, formData) {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrf() },
      body: formData
    });

    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      throw new Error(data?.message || 'Error al guardar.');
    }
    return data;
  }

  // =========================
  // 4) RESET POR PASOS
  // =========================
  function clearSlot() {
    State.holdId = null;
    State.date = null;
    State.start = null;
    State.end = null;

    setHidden(UI.hidHoldId, '');
    setHidden(UI.hidDate, '');
    setHidden(UI.hidTime, '');
    setHidden(UI.hidEnd, '');

    $(UI.slotsContainer).empty();
    $(UI.selectedSlotLabel).text('');

    $('#ca_slots_hint').show(); // ✅ vuelve a mostrar el texto cuando no hay selección
  }

  function resetAfterCategory() {
    State.serviceId = null;
    State.employeeId = null;
    State.mode = null;

    resetSelect(UI.serviceSelect);
    resetSelect(UI.employeeSelect);
    $(UI.modeSelect).val('');

    disable(UI.serviceSelect, true);
    disable(UI.employeeSelect, true);
    disable(UI.modeSelect, true);

    clearSlot();
    renderCalendarPlaceholder();
  }

  function resetAfterService() {
    State.employeeId = null;
    State.mode = null;

    resetSelect(UI.employeeSelect);
    $(UI.modeSelect).val('');

    disable(UI.employeeSelect, true);
    disable(UI.modeSelect, true);

    clearSlot();
    renderCalendarPlaceholder();
  }

  function resetAfterEmployee() {
    State.mode = null;
    $(UI.modeSelect).val('');
    disable(UI.modeSelect, false); // ya puede elegir modalidad
    clearSlot();
    renderCalendarPlaceholder();
  }

  // =========================
  // 5) CARGAS AJAX (category -> services -> employees)
  // =========================
  async function loadCategories() {
    resetSelect(UI.categorySelect);

    try {
      const data = await fetchJSON(API.categories);
      const categories = data?.data || [];

      categories.forEach((c) => {
        // esperado {id, name} o {id, catname}
        const text = c.name || c.catname || c.category_name || ('Área #' + c.id);
        $(UI.categorySelect).append(`<option value="${c.id}">${text}</option>`);
      });
    } catch (e) {
      showError(e.message);
    }
  }

  async function onCategoryChange() {
    hideError();
    const id = $(UI.categorySelect).val();
    State.categoryId = id || null;

    resetAfterCategory();
    if (!State.categoryId) return;

    disable(UI.serviceSelect, true);

    try {
      const data = await fetchJSON(`${API.services}?category_id=${encodeURIComponent(State.categoryId)}`);

      const services = data?.data || [];

      services.forEach((s) => {
        const text = s.name || s.servname || s.sername || s.service_name || ('Servicio #' + s.id);
        $(UI.serviceSelect).append(`<option value="${s.id}">${text}</option>`);
      });

      disable(UI.serviceSelect, false);
    } catch (e) {
      showError(e.message);
    }
  }

  async function onServiceChange() {
    hideError();
    const id = $(UI.serviceSelect).val();
    State.serviceId = id || null;

    resetAfterService();
    if (!State.serviceId) return;

    // set hidden service_id
    setHidden(UI.hidServiceId, State.serviceId);

    disable(UI.employeeSelect, true);

    try {
      const data = await fetchJSON(`${API.employees}?service_id=${encodeURIComponent(State.serviceId)}`);

      const employees = data?.data || [];

      employees.forEach((emp) => {
        const text =
          emp.name ||
          emp.full_name ||
          emp.user_name ||
          emp.employee_name ||
          ('Profesional #' + emp.id);

        $(UI.employeeSelect).append(`<option value="${emp.id}">${text}</option>`);
      });

      disable(UI.employeeSelect, false);
    } catch (e) {
      showError(e.message);
    }
  }

  function onEmployeeChange() {
    hideError();
    const id = $(UI.employeeSelect).val();
    State.employeeId = id || null;

    resetAfterEmployee();
    if (!State.employeeId) return;

    // set hidden employee_id
    setHidden(UI.hidEmployeeId, State.employeeId);

    disable(UI.modeSelect, false);
  }

  function onModeChange() {
    hideError();
    const m = $(UI.modeSelect).val();
    State.mode = m || null;

    clearSlot();

    if (!State.employeeId || !State.mode) {
      renderCalendarPlaceholder();
      return;
    }

    // Render calendar ahora que ya hay employee + mode
    initCalendarToCurrentMonth();
    renderCalendar();
  }

  // =========================
  // 6) CALENDARIO (render simple, click fecha -> loadSlotsForDate)
  // =========================
  function renderCalendarPlaceholder() {
    $(UI.calendarContainer).html(
      `<div class="text-muted small">Primero selecciona Área → Servicio → Profesional → Modalidad.</div>`
    );
  }

  function initCalendarToCurrentMonth() {
    const now = new Date();
    State.calYear = now.getFullYear();
    State.calMonth = now.getMonth();
  }

  function renderCalendar() {
    const year = State.calYear;
    const month = State.calMonth;

    const firstDay = new Date(year, month, 1).getDay(); // 0=Dom
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    const monthNames = [
        'Enero','Febrero','Marzo','Abril','Mayo','Junio',
        'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'
    ];

    const today = new Date();
    today.setHours(0,0,0,0);
    const todayYMD = toYMD(today);

    // Header tipo "Reagendar"
    const header = `
        <div class="card mb-0" id="ca-calendar-card">
        <div class="card-header">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="caCalPrev" aria-label="Mes anterior">
            <i class="fas fa-chevron-left"></i>
            </button>

            <h5 class="mb-0" id="ca-current-month">${monthNames[month]} ${year}</h5>

            <button type="button" class="btn btn-sm btn-outline-secondary" id="caCalNext" aria-label="Mes siguiente">
            <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <div class="card-body p-0">
            <table class="table table-calendar mb-0">
            <thead>
                <tr>
                <th>Dom</th><th>Lun</th><th>Mar</th><th>Mié</th><th>Jue</th><th>Vie</th><th>Sáb</th>
                </tr>
            </thead>
            <tbody id="ca-calendar-body"></tbody>
            </table>
        </div>
        </div>
    `;

    $(UI.calendarContainer).html(header);

    // Body (tabla) similar a Reagendar
    const $body = $('#ca-calendar-body');
    $body.empty();

    let date = 1;

    for (let i = 0; i < 6; i++) {
        const $row = $('<tr></tr>');

        for (let j = 0; j < 7; j++) {
        if (i === 0 && j < firstDay) {
            $row.append('<td class="text-center py-2"></td>');
            continue;
        }

        if (date > daysInMonth) {
            $row.append('<td class="text-center py-2"></td>');
            continue;
        }

        const d = new Date(year, month, date);
        const ymd = toYMD(d);
        const isPast = (ymd < todayYMD);

        $row.append(`
            <td class="text-center py-2 ca-cal-day ${isPast ? 'disabled text-muted' : ''}"
                data-ymd="${ymd}">
            ${date}
            </td>
        `);

        date++;
        }

        $body.append($row);
        if (date > daysInMonth) break;
    }

    // Nav prev/next (igual que tenías)
    $('#caCalPrev').off('click').on('click', async function () {
        await deleteHoldIfAny();
        State.calMonth--;
        if (State.calMonth < 0) {
        State.calMonth = 11;
        State.calYear--;
        }
        renderCalendar();
        clearSlot();
    });

    $('#caCalNext').off('click').on('click', function () {
        State.calMonth++;
        if (State.calMonth > 11) {
        State.calMonth = 0;
        State.calYear++;
        }
        renderCalendar();
        clearSlot();
    });

    // Click día
    $('#ca-calendar-body .ca-cal-day').off('click').on('click', async function () {
        const ymd = String($(this).data('ymd') || '').trim();
        if (!ymd) return;

        $('#ca-calendar-body .ca-cal-day').removeClass('active selected');
        $(this).addClass('active selected');

        await deleteHoldIfAny();

        clearSlot();
        await loadSlotsForDate(ymd);
    });
    }

  // =========================
  // 7) SLOTS + HOLD
  // =========================
  $('#ca_slots_hint').hide();
  async function loadSlotsForDate(dateYMD) {
    if (!State.employeeId || !State.mode) return;

    $('#ca_slots_hint').hide(); // ✅ al elegir una fecha, ocultar el texto

    $(UI.slotsContainer).html(`<div class="text-center text-muted w-100 py-4">Cargando horas disponibles.</div>`);

    try {
      const data = await fetchJSON(
        `${API.slots}/${encodeURIComponent(State.employeeId)}/${encodeURIComponent(dateYMD)}`
        );

        // En este endpoint, los slots vienen en data.available_slots
        const slots = Array.isArray(data?.available_slots) ? data.available_slots : [];

      if (!slots.length) {
        $(UI.slotsContainer).html(`<div class="text-center text-muted w-100 py-4">No hay horas disponibles para esa fecha.</div>`);
        $('#ca_slots_hint').hide(); // o show() si quieres que el hint sea el mensaje
        return;
    }

      // 2 columnas como tu grid
      const html = slots.map((s) => {
        const start = s.start || '';
        const end = s.end || '';
        const label = s.display || `${start} - ${end}`;

        return `
            <button type="button"
            class="btn btn-outline-primary btn-sm ca-slot"
            data-start="${start}"
            data-end="${end}"
            data-label="${label}">
            ${label}
            </button>
        `;
        }).join('');

        $('#ca_slots_hint').hide(); // ✅ por si acaso

      $(UI.slotsContainer).html(html);

      $('.ca-slot').off('click').on('click', async function () {
        hideError();

        const start = $(this).data('start');
        const end = $(this).data('end');
        const label = $(this).data('label');

        try {
            // ✅ si ya había hold, bórralo antes de reservar otro turno
            await deleteHoldIfAny();

            const holdId = await createHold(dateYMD, start, end);

            // state + hidden
            State.holdId = holdId;
            State.date = dateYMD;
            State.start = start;
            State.end = end;

            setHidden(UI.hidHoldId, holdId);
            setHidden(UI.hidDate, dateYMD);
            setHidden(UI.hidTime, start);
            setHidden(UI.hidEnd, end);

            $('.ca-slot').removeClass('active');
            $(this).addClass('active');

        } catch (e) {
            showError(e.message);
        }
    });

    } catch (e) {
      showError(e.message);
      $(UI.slotsContainer).empty();
    }
  }

  async function deleteHoldIfAny() {
    const holdId = State.holdId || $(UI.hidHoldId).val();

    if (!holdId) return;

    try {
        await fetch(`/holds/${holdId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrf(),
            'Accept': 'application/json'
        }
        });
    } catch (e) {
        console.warn('No se pudo eliminar el hold anterior', e);
    } finally {
        State.holdId = null;
        setHidden(UI.hidHoldId, '');
    }
    }

  async function createHold(dateYMD, start, end) {
    // Ajustado a tu AppointmentHoldController::create (si difiere, cambia keys aquí)
    const payload = {
      employee_id: State.employeeId,
      service_id: State.serviceId,   // ✅ requerido para tu controller
        is_admin: true,                // ✅ 10 minutos
      appointment_date: dateYMD,
      appointment_time: start,
      appointment_end_time: end
    };

    const data = await fetchJSON(API.createHold, { method: 'POST', body: payload });
    const holdId = data?.hold_id || data?.id;

    if (!holdId) throw new Error('No se pudo reservar el turno (HOLD).');
    return holdId;
  }

  // =========================
  // 8) BILLING: mismos datos + menor de edad
  // =========================
  function setBillingEnabled(enabled) {
    disable(UI.billingName, !enabled);
    disable(UI.billingDocType, !enabled);
    disable(UI.billingDocNumber, !enabled);
    disable(UI.billingEmail, !enabled);
    disable(UI.billingPhone, !enabled);
    disable(UI.billingPhoneUI, !enabled);
    disable(UI.billingAddress, !enabled);
  }

  function syncBillingFromPatient() {
        $(UI.billingName).val($(UI.patientName).val() || '');
        $(UI.billingDocType).val($(UI.patientDocType).val() || '');
        $(UI.billingDocNumber).val($(UI.patientDocNumber).val() || '');
        $(UI.billingEmail).val($(UI.patientEmail).val() || '');

        const pUI = document.getElementById('ca_patient_phone_ui');
        const bUI = document.getElementById('ca_billing_phone_ui');
        if (pUI && bUI) {
            const itiP = window._itiByInputId?.['ca_patient_phone_ui'];
            const itiB = window._itiByInputId?.['ca_billing_phone_ui'];

            // Si todavía no está listo el plugin (raro), reintenta en breve
            if (!itiP || !itiB) {
            setTimeout(syncBillingFromPatient, 50);
            } else {
            const iso2 = itiP.getSelectedCountryData()?.iso2;

            // Si billing está disabled por "mismos datos", lo habilitamos 1ms para setear país/valor
            const wasDisabled = bUI.disabled;
            if (wasDisabled) bUI.disabled = false;

            if (iso2) itiB.setCountry(iso2);

            // Copiar SOLO dígitos visibles (nacional) para que no se pegue +593 en el UI
            bUI.value = (pUI.value || '').replace(/\D/g, '');

            // Forzar que billing regenere su hidden E164 (tu setupIntlPhone lo hace en input)
            bUI.dispatchEvent(new Event('input', { bubbles: true }));

            // Restaurar disabled si estaba bloqueado
            if (wasDisabled) bUI.disabled = true;
            }
        }

        $(UI.billingAddress).val($(UI.patientAddress).val() || '');
    }

  function onPatientDobChange() {
    const dob = $(UI.patientDob).val();
    const adult = isAdult(dob);

    if (!adult && dob) {
      // menor => deshabilitar atajo
      $(UI.billingSameChk).prop('checked', false);
      disable(UI.billingSameChk, true);
      setBillingEnabled(true);
      $(UI.minorHint).removeClass('d-none');
      return;
    }

    // si no hay dob o es adulto
    disable(UI.billingSameChk, false);
    $(UI.minorHint).addClass('d-none');

    if ($(UI.billingSameChk).is(':checked')) {
      syncBillingFromPatient();
      setBillingEnabled(false);
    } else {
      setBillingEnabled(true);
    }
  }

  function onBillingSameToggle() {
    if ($(UI.billingSameChk).is(':checked')) {
      syncBillingFromPatient();
      setBillingEnabled(false);
    } else {
      setBillingEnabled(true);
    }
  }

  // =========================
  // 9) PAGO DINÁMICO
  // =========================

    // =========================
    // 9.A00) BLOQUEO: status cita + status pago hasta escoger método
    // =========================
    function lockStatusFields(lock = true) {
        // lock=true  => disabled
        // lock=false => enabled
        disable(UI.status, lock);
        disable(UI.paymentStatus, lock);

        if (lock) {
        // opcional: limpiar selección para que no queden valores “fantasma”
        $(UI.status).val('');
        $(UI.paymentStatus).val('');
        }
    }


  // =========================
    // 9.A0) ESTADOS DE CITA (lista exacta del dropdown)
    // - NO incluir confirmed ni cancelled
    // - pending_verification solo si payment_method=transfer
    // - on_hold SIEMPRE debe existir
    // =========================
    function rebuildAppointmentStatusOptions() {
    const $sel = $(UI.status);
    if (!$sel.length) return;

    const placeholder = $sel.find('option:first').length
        ? $sel.find('option:first')[0].outerHTML
        : `<option value="">Seleccione una opción</option>`;

    const options = [
        { value: 'pending_verification', label: 'Pendiente de verificación', transferOnly: true },
        { value: 'pending_payment',      label: 'Pendiente de pago' },
        { value: 'paid',                 label: 'Pagada' },
        { value: 'completed',            label: 'Completada' },
        { value: 'no_show',              label: 'No asistió' },
        { value: 'on_hold',              label: 'En espera' },
    ];

    // Reemplaza TODO (sin confirmed/cancelled)
    $sel.html(
        placeholder +
        options.map(o => `<option value="${o.value}" data-transfer-only="${o.transferOnly ? '1' : '0'}">${o.label}</option>`).join('')
    );
    }

   // =========================
    // 9.A) REGLAS: status (cita) -> payment_status (pago)
    // =========================
    function applyPaymentStatusRules() {
        const apptStatus = String($(UI.status).val() || '').trim();
        const pm = String($(UI.paymentMethod).val() || '').trim(); // cash|transfer|card
        const isTransfer = (pm === 'transfer');

        // Mapa: estado de cita -> estados de pago permitidos
        // (Valores reales en tu DB)
        const rules = {
        pending_verification: ['pending', 'partial'],
        pending_payment:      ['unpaid', 'partial'],
        paid:                ['paid'],
        completed:           ['paid', 'refunded'],
        no_show:             ['unpaid', 'partial', 'paid', 'refunded'],
        on_hold:             ['pending', 'unpaid', 'partial', 'paid'],
        };

        // Si el status no está en el mapa, no forzamos nada (por ejemplo cancelled si no lo pediste)
        const allowedRaw = rules[apptStatus];
        if (!allowedRaw) return;

        // Regla extra: "pending" solo si es transferencia
        const pendingAllowedByAppt = (apptStatus === 'pending_verification' || apptStatus === 'on_hold');
        const allowed = allowedRaw.filter(v => v !== 'pending' || (isTransfer && pendingAllowedByAppt));

        const $paySel = $(UI.paymentStatus);

        // Guardar lo que estaba seleccionado ANTES de cambiar disabled/hidden
        const prevValue = String($paySel.val() || '').trim();

        // Asegurar placeholder "Seleccione..."
        ensureSelectPlaceholder(UI.paymentStatus, 'Seleccione...');

        // Habilitar/ocultar opciones según allowed
        const all = ['pending','unpaid','partial','paid','refunded'];

        all.forEach(val => {
        const $opt = $paySel.find(`option[value="${val}"]`);
        if (!$opt.length) return;

        const ok = allowed.includes(val);
        // ✅ Mostrar todas, pero deshabilitar (gris) las no permitidas
        $opt.prop('disabled', !ok);
        $opt.prop('hidden', false);
        });

        // ✅ Regla especial: "pending"
        // - Si NO es transferencia: no aparece
        // - Si SÍ es transferencia: aparece, pero SOLO se habilita si está permitido por el estado de la cita
        const $optPending = $paySel.find('option[value="pending"]');
        if ($optPending.length) {
        if (!isTransfer) {
            $optPending.prop('hidden', true);
            $optPending.prop('disabled', true);
        } else {
            $optPending.prop('hidden', false);
            // OJO: aquí NO lo habilitamos "por ser transfer", sino solo si está en allowed
            const pendingAllowed = allowed.includes('pending');
            $optPending.prop('disabled', !pendingAllowed);
        }
        }

        // Si "pending" quedó oculto y estaba seleccionado, reset a placeholder
        if (!isTransfer && String($paySel.val() || '').trim() === 'pending') {
            resetToPlaceholder(UI.paymentStatus);
        }

        // Si lo que tenía seleccionado ya NO está permitido, resetea YA
        // (usar prevValue evita que el browser/Select2 "pegue" el texto)
        if (prevValue !== '' && !allowed.includes(prevValue)) {
        resetToPlaceholder(UI.paymentStatus);
        return;
        }

// Si estaba vacío, se queda vacío (no autoselecciona)
if (prevValue === '') return;
    }

  function onPaymentMethodChange() {
        const pm = String($(UI.paymentMethod).val() || '').trim(); // '' | cash | transfer | card

        // ✅ Si cambió el método, resetea montos y observaciones (como en edición)
        const prev = State.lastPaymentMethod;
        const changed = (prev && pm && prev !== pm);

        if (changed) {
        // Montos
        $(UI.amount).val('0.00').trigger('blur');
        $(UI.amountPaid).val('0.00').trigger('blur');

        // Observaciones de pago (payment_notes)
        $(UI.paymentNotes).val('');
        }

        // Guardar método actual
        State.lastPaymentMethod = pm || null;

        const show = (sel) => $(sel).removeClass('d-none');
        const hide = (sel) => $(sel).addClass('d-none');

        // 0) Ocultar todo lo dinámico
        hide('#ca_payment_fields_block');
        hide(UI.transferBlock);  // #ca_transfer_block
        hide(UI.clientTxWrap);   // #ca_client_tx_wrap

        hide('#ca_paid_at_wrap');     // ✅ ocultar fecha del pago por defecto
        $(UI.paidAt).val('');         // ✅ limpiar valor

        // 1) Si NO han elegido método, bloquear estados y no mostrar nada más
        if (!pm) {
            lockStatusFields(true);
            return;
        }

        // 1.1) Ya hay método: habilitar estados
        lockStatusFields(false);

        // 2) Mostrar campos base (monto/estado/fecha/obs)
        show('#ca_payment_fields_block');

        // Por defecto, mostrar fecha del pago (solo NO aplica para transferencia)
        show('#ca_paid_at_wrap');

        // =====================================================
        // REGLAS: opciones SOLO para transferencia (UI)
        // - status: pending_verification solo transfer
        // - payment_status: pending solo transfer
        // =====================================================
        const isTransfer = (pm === 'transfer');

        rebuildAppointmentStatusOptions();
        // Ocultar/mostrar opciones en los selects (sin eliminar)
        const $statusSel = $(UI.status);
        const $payStatusSel = $(UI.paymentStatus);

        // Estado cita: pending_verification
        const $optPendingVerif = $statusSel.find('option[value="pending_verification"]');
        if ($optPendingVerif.length) {
        $optPendingVerif.prop('hidden', !isTransfer);
        $optPendingVerif.prop('disabled', !isTransfer);

        // Si no es transferencia y estaba seleccionado, corregir
        if (!isTransfer && $statusSel.val() === 'pending_verification') {
            $statusSel.val('pending_payment').trigger('change');
        }
        }

        // Estado pago: pending
        const $optPayPending = $payStatusSel.find('option[value="pending"]');
        if ($optPayPending.length) {
        $optPayPending.prop('hidden', !isTransfer);
        }

        // Si NO es transferencia y estaban seleccionados, corrige al instante
        if (!isTransfer && $statusSel.val() === 'pending_verification') {
            $statusSel.val('pending_payment'); // default en crear
        }
        if (!isTransfer && $payStatusSel.val() === 'pending') {
            $payStatusSel.val('unpaid'); // default visual (backend recalcula si quieres)
        }

        // 3) Mostrar según método
        if (pm === 'card') {
            show(UI.clientTxWrap);
            hide(UI.transferBlock);
        } else if (pm === 'transfer') {
            show(UI.transferBlock);
            hide(UI.clientTxWrap);
            $(UI.clientTx).val('');

            hide('#ca_paid_at_wrap');  // ✅ en transferencia NO va fecha del pago
            $(UI.paidAt).val('');      // ✅ limpiar por si estaba seteado
        } else if (pm === 'cash') {
            hide(UI.transferBlock);
            hide(UI.clientTxWrap);
            $(UI.clientTx).val('');
        }
        // ✅ aplicar reglas status->payment_status cada vez que cambia el método
        applyPaymentStatusRules();
    }

  // =========================
  // 10) SUBMIT
  // =========================
  async function onSubmit() {
    hideError();

    // Validación secuencial mínima
    if (!State.categoryId) return showError('Seleccione el área de atención.');
    if (!State.serviceId) return showError('Seleccione el servicio.');
    if (!State.employeeId) return showError('Seleccione el profesional.');
    if (!State.mode) return showError('Seleccione la modalidad.');
    if (!$(UI.hidHoldId).val() || !$(UI.hidDate).val() || !$(UI.hidTime).val() || !$(UI.hidEnd).val()) {
      return showError('Seleccione fecha y turno.');
    }

    // Required paciente (según tu modal)
    if (!$(UI.patientName).val()) return showError('Ingrese nombre y apellido del paciente.');
    if (!$(UI.patientEmail).val()) return showError('Ingrese correo del paciente.');
    if (!$(UI.patientPhone).val()) return showError('Ingrese teléfono del paciente.');

    // Required pagos (según tu modal)
    if (!$(UI.paymentMethod).val()) return showError('Seleccione el método de pago.');
    if (!$(UI.amount).val()) return showError('Ingrese el monto total a pagar.');
    if (!$(UI.amountPaid).val()) return showError('Ingrese el monto pagado.');
    if (!$(UI.paymentStatus).val()) return showError('Seleccione el estado del pago.');
    if (!$(UI.status).val()) return showError('Seleccione el estado de la cita.');
// canal es opcional, así que NO lo obligues
    // Fecha del pago: solo para cash/card (transfer usa "fecha de la transferencia")
    const pm = String($(UI.paymentMethod).val() || '').trim();

    // 🔒 Refuerzo: si no es transferencia, NO permitir pending/pending_verification
    if (pm !== 'transfer') {
      if ($(UI.status).val() === 'pending_verification') {
        $(UI.status).val('pending_payment');
      }
      if ($(UI.paymentStatus).val() === 'pending') {
        $(UI.paymentStatus).val('unpaid');
      }
    }

    if (pm !== 'transfer') {
    if (!$(UI.paidAt).val()) return showError('Ingrese la fecha del pago.');
    } else {
    // seguridad: si es transferencia, vaciamos payment_paid_at
    $(UI.paidAt).val('');
    }

    // Transfer required si aplica (tu UI tiene asteriscos en 3 campos)
    if ($(UI.paymentMethod).val() === 'transfer') {
      if (!$(UI.transferBankOrigin).val()) return showError('Ingrese el banco de origen.');
      if (!$(UI.transferPayerName).val()) return showError('Ingrese el nombre del titular.');
      if (!$(UI.transferDate).val()) return showError('Ingrese la fecha de la transferencia.');
      // comprobante es opcional en tu UI, no se valida
    }

    // Construir FormData desde el form (incluye file automáticamente)
    const form = document.querySelector(UI.form);
    const fd = new FormData(form);

    // =========================
    // FORZAR CAMPOS QUE A VECES NO VIAJAN (disabled / names)
    // =========================

    // 1) appointment_mode (tu State.mode es lo que el usuario eligió)
    fd.set('appointment_mode', (State.mode || '').toString().toLowerCase());

    // 2) appointment_request_source (viene del select de canal del modal)
    fd.set('appointment_request_source', String($(UI.channel).val() || '').trim());

    // 3) payment_notes (aunque por UI esté lleno, lo forzamos)
    fd.set('payment_notes', String($(UI.paymentNotes).val() || '').trim());

    // 4) amount_paid (por si el input tiene name diferente o no viaja)
    fd.set('amount_paid', String($(UI.amountPaid).val() || '0.00').trim());

    // 5) billing_* (si están disabled por "mismos datos", NO viajan; por eso los seteamos)
    fd.set('billing_name', String($(UI.billingName).val() || '').trim());
    fd.set('billing_doc_type', String($(UI.billingDocType).val() || '').trim());
    fd.set('billing_doc_number', String($(UI.billingDocNumber).val() || '').trim());
    fd.set('billing_email', String($(UI.billingEmail).val() || '').trim());
    fd.set('billing_phone', String($(UI.billingPhone).val() || '').trim());
    fd.set('billing_address', String($(UI.billingAddress).val() || '').trim());

    // ✅ Admin: no existe checkbox de consentimiento en este modal
    // Forzamos a "aceptado" para que el backend no lo exija como si fuera paciente
    fd.set('data_consent', '1');

    // ✅ Admin: forzar términos aceptados para evitar NULL en DB
    fd.set('terms_accepted', '1');

    // URL de store
    const action = $(UI.form).attr('action');
    const url = action && action.trim() !== '' ? action : API.store;

    // UI
    disable(UI.submitBtn, true);
    $(UI.submitBtn).text('Guardando...');

    try {
      const data = await fetchForm(url, fd);

      // success
      alert(data?.message || 'Cita creada correctamente.');
        hideError();

        // ✅ Recargar de una: al recargar, el modal desaparece y se actualiza la tabla
        window.location.reload();

    } catch (e) {
      showError(e.message);
    } finally {
      disable(UI.submitBtn, false);
      $(UI.submitBtn).text('Guardar cita');
    }
  }

  // =========================
  // 11) INIT
  // =========================
    function hideCreateApptModal() {
    const el = document.querySelector(UI.modal);
    if (!el) return;

    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.modal) {
        window.jQuery(el).modal('hide');
    }
    }

    // ✅ Cierre universal para X y botón Cerrar (aunque data-* no funcione)
    $(document).on('click', `${UI.modal} [data-bs-dismiss="modal"], ${UI.modal} [data-dismiss="modal"], ${UI.modal} .btn-close, ${UI.modal} .close`, function (e) {
    e.preventDefault();
    hideCreateApptModal();
});

  function init() {
    // Estado inicial de selects
    disable(UI.serviceSelect, true);
    disable(UI.employeeSelect, true);
    disable(UI.modeSelect, true);

    // Billing
    setBillingEnabled(true);
    disable(UI.billingSameChk, true);
    $(UI.minorHint).addClass('d-none');

    // Calendar placeholder
    renderCalendarPlaceholder();

    // binds
    $(document).on('change', UI.categorySelect, onCategoryChange);
    $(document).on('change', UI.serviceSelect, onServiceChange);
    $(document).on('change', UI.employeeSelect, onEmployeeChange);
    $(document).on('change', UI.modeSelect, onModeChange);

    $(document).on('change', UI.patientDob, onPatientDobChange);
    $(document).on('change', UI.billingSameChk, onBillingSameToggle);

    // si editan datos del paciente y está marcado "same", re-sincroniza
    $(document).on('input',
        UI.patientName + ',' +
        UI.patientDocType + ',' +
        UI.patientDocNumber + ',' +
        UI.patientEmail + ',' +
        '#ca_patient_phone_ui,' +   // ✅ importante
        UI.patientPhone + ',' +
        UI.patientAddress,
        function () {
        if ($(UI.billingSameChk).is(':checked') && !$(UI.billingSameChk).is(':disabled')) {
            syncBillingFromPatient();
        }
    });

    // ✅ Si cambian la bandera del teléfono del paciente, también sincronizar facturación
    $(document).on('countrychange', '#ca_patient_phone_ui', function () {
    if ($(UI.billingSameChk).is(':checked') && !$(UI.billingSameChk).is(':disabled')) {
        syncBillingFromPatient();
    }
    });

    $(document).on('change', UI.paymentMethod, onPaymentMethodChange);

     // ✅ Cuando cambia el estado de la cita, recalcular estados de pago permitidos
    $(document).on('change', UI.status, function () {
        applyPaymentStatusRules();

        // ✅ 1 tick después, para que Select2 no se quede pegado visualmente
        setTimeout(() => {
            applyPaymentStatusRules();
        }, 0);
    });

    // ✅ Monto: solo números y 1 punto mientras escribe
    $(document).on('input', `${UI.amount}, ${UI.amountPaid}`, function () {
    let v = String(this.value || '');

    v = v.replace(',', '.');
    v = v.replace(/[^\d.]/g, '');

    const dot = v.indexOf('.');
    if (dot !== -1) {
        v = v.slice(0, dot + 1) + v.slice(dot + 1).replace(/\./g, '');
    }

    this.value = v;
    });

    // ✅ Al salir del input: forzar 2 decimales
    $(document).on('blur', `${UI.amount}, ${UI.amountPaid}`, function () {
    const formatted = normalizeMoney2(this.value);

    // si quedó vacío, lo dejamos vacío (se verá el placeholder 0.00)
    // si prefieres que SIEMPRE quede 0.00, abajo te dejo esa opción
    this.value = (formatted === '' ? '0.00' : formatted);
    });

    $(document).on('click', UI.submitBtn, function (e) {
      e.preventDefault();
      onSubmit();
    });

    // Cuando se abre el modal: cargar categorías y reset
    $(UI.modal).on('shown.bs.modal', function () {
      hideError();

      // reset UI
      $(UI.form)[0].reset();

      State.lastPaymentMethod = null;

      $(UI.amount).val('0.00');
    $(UI.amountPaid).val('0.00');

      // reset state
      State.categoryId = null;
      State.serviceId = null;
      State.employeeId = null;
      State.mode = null;
      clearSlot();

      // reset selects
      resetSelect(UI.categorySelect);
      resetSelect(UI.serviceSelect);
      resetSelect(UI.employeeSelect);
      $(UI.modeSelect).val('');

      disable(UI.serviceSelect, true);
      disable(UI.employeeSelect, true);
      disable(UI.modeSelect, true);

      // billing
      setBillingEnabled(true);
      disable(UI.billingSameChk, true);
      $(UI.minorHint).addClass('d-none');

      // payment
      rebuildAppointmentStatusOptions();
      lockStatusFields(true);
      onPaymentMethodChange();

       applyPaymentStatusRules();

      renderCalendarPlaceholder();
      loadCategories();
    });

    $(UI.modal).on('hidden.bs.modal', async function () {
        await deleteHoldIfAny();
    });
  }

  $(document).ready(init);
})();

(function () {
  function setupIntlPhone(inputId, hiddenId, hintId) {
    const input  = document.getElementById(inputId);
    const hidden = document.getElementById(hiddenId);
    const hintEl = document.getElementById(hintId);

    if (!input || !hidden || typeof window.intlTelInput !== "function") return null;

    // ✅ Nombres de países en español (sin cargar i18n externo)
    let localizedEs = {};
    try {
    const dn = new Intl.DisplayNames(["es"], { type: "region" });
    const data = (window.intlTelInputGlobals && window.intlTelInputGlobals.getCountryData)
        ? window.intlTelInputGlobals.getCountryData()
        : [];
    data.forEach(c => {
        const code = (c.iso2 || "").toUpperCase();
        const nameEs = dn.of(code);
        if (nameEs) localizedEs[c.iso2] = nameEs;
    });
    } catch (e) {
    localizedEs = {};
    }

    // ✅ v19: traducir el dataset GLOBAL que usa el dropdown
    try {
    const data = window.intlTelInputGlobals.getCountryData();
    data.forEach(c => {
        if (localizedEs[c.iso2]) c.name = localizedEs[c.iso2];
    });
    } catch (e) {}

    // Evita doble init si abres/cierra el modal
    if (input.dataset.itiInit === "1") return window._itiByInputId?.[inputId] || null;
    input.dataset.itiInit = "1";

    const iti = window.intlTelInput(input, {
        initialCountry: "ec",
        separateDialCode: true,
        nationalMode: true,
        formatOnDisplay: false,

        // 🚫 DESACTIVA placeholder automático
        autoPlaceholder: "off",

        preferredCountries: ["ec", "us", "co", "pe", "es"],
        utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/js/utils.js"
    });

    // 🔒 limpieza final
    input.removeAttribute("placeholder");
    input.placeholder = "";

    // Hint dinámico
    function updatePhoneHint() {
      if (!hintEl) return;
      const iso2 = (iti.getSelectedCountryData()?.iso2 || "ec");
      hintEl.textContent = (iso2 === "ec")
        ? "Para Ecuador, registre el número sin el 0 inicial."
        : "Verifique que el país seleccionado sea el correcto.";
    }

    // Limpieza de input (sin validaciones “pesadas”)
    function enforceDigitsAndEcuadorRule() {
      const iso2 = (iti.getSelectedCountryData()?.iso2 || "");
      let digits = (input.value || "").replace(/\D/g, "");

      if (iso2 === "ec") {
        if (digits.startsWith("593")) digits = digits.slice(3);
        if (digits.startsWith("0")) digits = digits.slice(1);
        if (digits.length > 9) digits = digits.slice(0, 9);
      }

      if (input.value !== digits) input.value = digits;
    }

    // Sync al hidden (por ahora lo guardo en E164; luego tú lo separas prefijo/nacional)
    function syncHidden() {
        // dígitos nacionales (lo que el usuario escribe)
        const digits = (input.value || "").replace(/\D/g, "");

        // 1) Intentar E164 real con utils
        let number = "";
        try {
            if (window.intlTelInputUtils && window.intlTelInputUtils.numberFormat) {
            number = iti.getNumber(intlTelInputUtils.numberFormat.E164) || "";
            } else {
            number = iti.getNumber() || "";
            }
        } catch (e) {
            number = "";
        }

        // 2) Fallback: si no viene con +, armarlo manualmente con el dialCode
        if (!number || number.charAt(0) !== "+") {
            const dial = iti.getSelectedCountryData()?.dialCode || "";
            number = (dial && digits) ? (`+${dial}${digits}`) : "";
        }

        hidden.value = number;
        }

    // Inicial
    updatePhoneHint();
    enforceDigitsAndEcuadorRule();
    syncHidden();

    input.addEventListener("input", () => {
      enforceDigitsAndEcuadorRule();
      syncHidden();
    });

    input.addEventListener("blur", () => {
      enforceDigitsAndEcuadorRule();
      syncHidden();
    });

    input.addEventListener("countrychange", () => {
      updatePhoneHint();
      enforceDigitsAndEcuadorRule();
      syncHidden();
    });

    window._itiByInputId = window._itiByInputId || {};
    window._itiByInputId[inputId] = iti;

    return iti;
  }

  // ✅ Inicializa intl-tel-input cuando el modal se abre (BS4/BS5)
    (function () {
    const $ = window.jQuery;

    function initPhones() {
        setupIntlPhone("ca_patient_phone_ui", "ca_patient_phone", "ca_patient_phone_hint");
        setupIntlPhone("ca_billing_phone_ui", "ca_billing_phone", "ca_billing_phone_hint");
    }

    // Si hay jQuery (AdminLTE normalmente sí), usa el evento jQuery que sirve en BS4/BS5
    if ($) {
        $(document).off("shown.bs.modal.caPhones", "#modalCreateAppointment");
        $(document).on("shown.bs.modal.caPhones", "#modalCreateAppointment", function () {
        initPhones();
        });

        // fallback si por alguna razón no se dispara
        $(document).ready(function () {
        if (document.getElementById("modalCreateAppointment")) {
            // no hace nada extra; solo asegura que el DOM exista
        }
        });

        return;
    }

    // Fallback sin jQuery (raro en AdminLTE)
    const modal = document.getElementById("modalCreateAppointment");
    if (modal) {
        modal.addEventListener("shown.bs.modal", initPhones);
    } else {
        initPhones();
    }
    })();
})();