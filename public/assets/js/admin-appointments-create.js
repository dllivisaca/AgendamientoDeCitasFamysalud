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
    consent: '#ca_data_consent',

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
    calMonth: null // 0-11
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
  async function loadSlotsForDate(dateYMD) {
    if (!State.employeeId || !State.mode) return;

    $(UI.slotsContainer).html(`<div class="text-muted small">Cargando turnos…</div>`);

    try {
      const data = await fetchJSON(
        `${API.slots}/${encodeURIComponent(State.employeeId)}/${encodeURIComponent(dateYMD)}`
        );

        // En este endpoint, los slots vienen en data.available_slots
        const slots = Array.isArray(data?.available_slots) ? data.available_slots : [];

      if (!slots.length) {
        $(UI.slotsContainer).html(`<div class="text-muted small">No hay turnos disponibles.</div>`);
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

            $(UI.selectedSlotLabel).text(`Turno seleccionado: ${label} (${dateYMD})`);

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
    disable(UI.billingAddress, !enabled);
  }

  function syncBillingFromPatient() {
    $(UI.billingName).val($(UI.patientName).val() || '');
    $(UI.billingDocType).val($(UI.patientDocType).val() || '');
    $(UI.billingDocNumber).val($(UI.patientDocNumber).val() || '');
    $(UI.billingEmail).val($(UI.patientEmail).val() || '');
    $(UI.billingPhone).val($(UI.patientPhone).val() || '');
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
  function onPaymentMethodChange() {
    const pm = $(UI.paymentMethod).val(); // cash|transfer|card

    // transferencia: mostrar bloque
    if (pm === 'transfer') {
      $(UI.transferBlock).removeClass('d-none');
    } else {
      $(UI.transferBlock).addClass('d-none');
      // opcional: limpiar campos de transferencia al cambiar
      $(UI.transferBankOrigin).val('');
      $(UI.transferPayerName).val('');
      $(UI.transferDate).val('');
      $(UI.transferReference).val('');
      $(UI.trFile).val('');
    }

    // tarjeta: mostrar client tx opcional
    if (pm === 'card') {
      $(UI.clientTxWrap).removeClass('d-none');
    } else {
      $(UI.clientTxWrap).addClass('d-none');
      $(UI.clientTx).val('');
    }
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
    if (!$(UI.paidAt).val()) return showError('Ingrese la fecha del pago.');

    // Transfer required si aplica (tu UI tiene asteriscos en 3 campos)
    if ($(UI.paymentMethod).val() === 'transfer') {
      if (!$(UI.transferBankOrigin).val()) return showError('Ingrese el banco de origen.');
      if (!$(UI.transferPayerName).val()) return showError('Ingrese el nombre del titular.');
      if (!$(UI.transferDate).val()) return showError('Ingrese la fecha de la transferencia.');
      // comprobante es opcional en tu UI, no se valida
    }

    // Consent (tu checkbox viene checked y con value=1)
    // backend normalmente espera boolean
    const consentChecked = $(UI.consent).is(':checked');
    if (!consentChecked) return showError('Debe confirmar el tratamiento de datos.');

    // Construir FormData desde el form (incluye file automáticamente)
    const form = document.querySelector(UI.form);
    const fd = new FormData(form);

    // Asegurar boolean data_consent como 1/0
    fd.set('data_consent', consentChecked ? '1' : '0');

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
      hideCreateApptModal();
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

    // Bootstrap 5
    if (window.bootstrap && window.bootstrap.Modal) {
        const inst = window.bootstrap.Modal.getInstance(el) || new window.bootstrap.Modal(el);
        inst.hide();
        return;
    }

    // Bootstrap 4 (jQuery plugin)
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

    // Payment
    onPaymentMethodChange();

    // binds
    $(document).on('change', UI.categorySelect, onCategoryChange);
    $(document).on('change', UI.serviceSelect, onServiceChange);
    $(document).on('change', UI.employeeSelect, onEmployeeChange);
    $(document).on('change', UI.modeSelect, onModeChange);

    $(document).on('change', UI.patientDob, onPatientDobChange);
    $(document).on('change', UI.billingSameChk, onBillingSameToggle);

    // si editan datos del paciente y está marcado "same", re-sincroniza
    $(document).on('input', UI.patientName + ',' + UI.patientDocType + ',' + UI.patientDocNumber + ',' + UI.patientEmail + ',' + UI.patientPhone + ',' + UI.patientAddress, function () {
      if ($(UI.billingSameChk).is(':checked') && !$(UI.billingSameChk).is(':disabled')) {
        syncBillingFromPatient();
      }
    });

    $(document).on('change', UI.paymentMethod, onPaymentMethodChange);

    $(document).on('click', UI.submitBtn, function (e) {
      e.preventDefault();
      onSubmit();
    });

    // Cuando se abre el modal: cargar categorías y reset
    $(UI.modal).on('shown.bs.modal', function () {
      hideError();

      // reset UI
      $(UI.form)[0].reset();

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
      onPaymentMethodChange();

      renderCalendarPlaceholder();
      loadCategories();
    });

    $(UI.modal).on('hidden.bs.modal', async function () {
        await deleteHoldIfAny();
    });
  }

  $(document).ready(init);
})();