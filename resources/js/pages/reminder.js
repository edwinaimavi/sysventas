var divLoading = document.getElementById('divLoading');
let tableReminders;

document.addEventListener("DOMContentLoaded", function () {
    //csrf token para AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });


    // ============================
    //   DATATABLE REMINDERS
    // ============================
    tableReminders = $('#tableReminders').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.routes.reminderList,
            type: 'GET',
            error: function (xhr, error, thrown) {
                console.error('Error en DataTables (reminders):', error, thrown);
                console.log('Respuesta del servidor:', xhr.responseText);
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },

            { data: 'title', name: 'title' },
            { data: 'client_name', name: 'client_name' },
            { data: 'loan_code', name: 'loan_code' },

            { data: 'remind_at', name: 'remind_at' },
            { data: 'priority', name: 'priority' },
            { data: 'type', name: 'type' },
            { data: 'status', name: 'status' },
            { data: 'channel', name: 'channel' },

            { data: 'acciones', name: 'acciones', orderable: false, searchable: false },
        ],
        order: [[4, 'desc']], // por defecto: remind_at desc
        responsive: true,
        autoWidth: false,
        dom: `
            <'row mb-3'<'col-sm-12 col-md-6 text-start'l><'col-sm-12 col-md-6 text-end'f>>
            <'row'<'col-sm-12'tr>>
            <'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 d-flex justify-content-center justify-content-md-end'p>>
            <'row mt-3'<'col-sm-12 text-center'B>>
        `,
        language: { url: "/vendor/datatables/js/i18n/es-ES.json" },
        buttons: [
            { extend: 'excel', className: 'btn btn-success btn-sm', text: '<i class="fas fa-file-excel"></i> Excel' },
            { extend: 'pdf', className: 'btn btn-danger btn-sm', text: '<i class="fas fa-file-pdf"></i> PDF' },
            { extend: 'print', className: 'btn btn-secondary btn-sm', text: '<i class="fas fa-print"></i> Imprimir' }
        ],
        preDrawCallback: function () {
            divLoading && divLoading.classList.remove('d-none');
        },
        drawCallback: function () {
            divLoading && divLoading.classList.add('d-none');
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
        }
    });


    // ============================
    //   GUARDAR REMINDER (AJAX)
    //   Anti doble-click + divLoading
    // ============================

    let reminderSubmitting = false;

    $('#reminderForm').on('submit', function (e) {
        e.preventDefault();

        // ✅ evita doble submit
        if (reminderSubmitting) return;

        const $form = $(this);
        const id = $form.attr('data-id'); // por si luego habilitas edición

        let url = '';
        let type = 'POST';

        const formData = new FormData(this);

        if (id) {
            url = `/admin/reminders/${id}`;
            type = 'POST';
            formData.append('_method', 'PUT');
        } else {
            url = window.routes.storeReminder;
            type = 'POST';
        }

        // ⚠️ status disabled no viaja; lo aseguramos por frontend también
        if (!formData.get('status')) {
            formData.append('status', 'pending');
        }

        // ✅ bloquear + loading + deshabilitar botón
        reminderSubmitting = true;
        if (typeof divLoading !== 'undefined' && divLoading) divLoading.style.display = "flex";

        const $btnSave = $('#btnSaveReminder');
        $btnSave.prop('disabled', true);

        // opcional: bloquear todos los campos para evitar cambios mientras envía
        $form.find('input, select, textarea, button').prop('disabled', true);
        // pero re-habilitamos el close si quieres (opcional)
        // $form.find('[data-dismiss="modal"]').prop('disabled', false);

        $.ajax({
            url: url,
            type: type,
            data: formData,
            processData: false,
            contentType: false,

            success: function (response) {
                $('#reminderModal').modal('hide');

                // limpiar modo edición
                $form.removeAttr('data-id');

                // recargar tabla
                if (tableReminders) {
                    tableReminders.ajax.reload(null, false);
                }

                Swal.fire({
                    title: response.message || 'Recordatorio guardado.',
                    icon: "success",
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });
            },

            error: function (xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors || {};

                    // limpiar errores anteriores
                    $form.find('.is-invalid').removeClass('is-invalid');
                    $form.find('.invalid-feedback').text('');

                    $.each(errors, function (key, messages) {
                        const $input = $(`#${key}`);
                        if ($input.length) {
                            $input.addClass('is-invalid');
                            $(`#${key}-error`).text(messages[0]);
                        }
                    });
                } else {
                    console.error('Error al guardar recordatorio', xhr);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'Ocurrió un error inesperado.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3500
                    });
                }
            },

            // ✅ siempre se ejecuta (success/error)
            complete: function () {
                reminderSubmitting = false;

                if (typeof divLoading !== 'undefined' && divLoading) divLoading.style.display = "none";

                // re-habilitar campos y botón
                $form.find('input, select, textarea, button').prop('disabled', false);
                $btnSave.prop('disabled', false);

                // tu status está disabled por diseño, lo devolvemos a su estado (opcional)
                $('#status').prop('disabled', true);
            }
        });
    });

    // ============================
    //   LIMPIAR MODAL AL CERRAR
    // ============================
    $('#reminderModal').on('hidden.bs.modal', function () {

        const $form = $('#reminderForm');

        // 1) Reset del form (valores)
        if ($form.length && $form[0]) {
            $form[0].reset();
        }

        // 2) Quitar modo edición (si luego lo usas)
        $form.removeAttr('data-id');

        // 3) Limpiar errores visuales
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').text('');

        // 4) Título del modal a "Nuevo Recordatorio"
        $('#reminderModalLabel').text('Nuevo Recordatorio');

        // 5) Reset de datos del panel izquierdo (si existen)
        $('#left_title').text('Sin título');
        /*  $('#left_type').text('Manual'); */
        $('#left_priority').text('Normal');
        $('#left_status').text('Pendiente');
        /*  $('#left_channel').text('Sistema'); */
        $('#left_remind_at').text('—');
        $('#left_client').text('—');
        $('#left_loan').text('—');

        // 6) Reset de selects a defaults (por si no quedan por reset)
        $('#type').val('manual');
        $('#priority').val('normal');
        $('#channel').val('system');

        $('#client_id').empty().append(`<option value="">— Opcional —</option>`);
        $('#loan_id').empty().append(`<option value="">— Opcional —</option>`);

        // status normalmente disabled; lo dejamos en pending visualmente
        $('#status').val('pending');
    });


    function fillClients() {
        return $.get(window.routes.clientsList)
            .done(function (res) {
                const $client = $('#client_id');
                $client.empty().append(`<option value="">— Opcional —</option>`);

                (res.data || []).forEach(c => {
                    $client.append(`<option value="${c.id}">${c.full_name}</option>`);
                });
            })
            .fail(function (xhr) {
                console.error('Error cargando clientes', xhr.responseText);
            });
    }

    function fillLoansByClient(clientId) {
        const $loan = $('#loan_id');
        $loan.empty().append(`<option value="">— Opcional —</option>`);

        if (!clientId) return;

        const url = window.routes.clientLoans.replace('__ID__', clientId);

        $.get(url)
            .done(function (res) {
                (res.data || []).forEach(l => {
                    $loan.append(`<option value="${l.id}" data-loan_code="${l.loan_code}">${l.text}</option>`);
                });
            })
            .fail(function (xhr) {
                console.error('Error cargando préstamos', xhr.responseText);
            });
    }

    // Cuando se abre el modal: carga clientes
    $('#reminderModal').on('show.bs.modal', function () {
        fillClients();
    });

    // Al cambiar cliente: traer préstamos y actualizar panel izquierdo
    $(document).on('change', '#client_id', function () {
        const clientName = $(this).find('option:selected').text() || '—';
        $('#left_client').text($(this).val() ? clientName : '—');

        // reset préstamo
        $('#left_loan').text('—');
        $('#loan_id').val('');

        fillLoansByClient($(this).val());
    });

    // Al cambiar préstamo: actualizar panel izquierdo
    $(document).on('change', '#loan_id', function () {
        const loanText = $(this).find('option:selected').text() || '—';
        $('#left_loan').text($(this).val() ? loanText : '—');
    });



    // ============================
    //   PANEL IZQUIERDO (AUTO UPDATE)
    // ============================

    function pad2(n) { return String(n).padStart(2, '0'); }

    // Convierte "2025-12-31T14:30" -> "2025-12-31 14:30"
    function formatDateTimeLocal(value) {
        if (!value) return '—';
        // value viene como YYYY-MM-DDTHH:mm
        const parts = value.split('T');
        if (parts.length !== 2) return value;
        return `${parts[0]} ${parts[1]}`;
    }

    function setBadge($el, text, type) {
        // type: info|danger|secondary|warning|success|dark
        if (!$el || !$el.length) return;

        // limpiar clases badge-* (Bootstrap 4)
        $el.removeClass(function (index, className) {
            return (className.match(/(^|\s)badge-\S+/g) || []).join(' ');
        });

        $el.addClass(`badge badge-${type} py-2 px-3 mt-1`);
        $el.text(text);
    }

    function updateLeftTitle() {
        const t = ($('#title').val() || '').trim();
        $('#left_title').text(t ? t : '—');
    }

    function updateLeftRemindAt() {
        const v = $('#remind_at').val();
        $('#left_remind_at').text(formatDateTimeLocal(v));
    }

    function updateLeftPriority() {
        const v = $('#priority').val();

        const map = {
            low: { text: 'Baja', badge: 'secondary' },
            normal: { text: 'Normal', badge: 'info' },
            high: { text: 'Alta', badge: 'danger' }
        };

        const p = map[v] || { text: (v || '—'), badge: 'secondary' };

        setBadge($('#left_priority'), p.text, p.badge);
    }

    function updateLeftClient() {
        const $sel = $('#client_id');
        const has = !!$sel.val();
        const name = $sel.find('option:selected').text() || '—';
        $('#left_client').text(has ? name : '—');
    }

    function updateLeftLoan() {
        const $sel = $('#loan_id');
        const has = !!$sel.val();
        const text = $sel.find('option:selected').text() || '—';
        $('#left_loan').text(has ? text : '—');
    }

    // Opcionales (solo si existen en tu HTML)
    function updateLeftType() {
        if (!$('#left_type').length) return;
        const v = $('#type').val();
        const labels = {
            manual: 'Manual',
            payment_due: 'Pago por vencer',
            payment_overdue: 'Pago vencido',
            loan_finish: 'Préstamo finaliza'
        };
        $('#left_type').text(labels[v] || (v || '—'));
    }

    function updateLeftChannel() {
        if (!$('#left_channel').length) return;
        const v = $('#channel').val();
        const labels = {
            system: 'Sistema',
            email: 'Email',
            whatsapp: 'WhatsApp',
            sms: 'SMS'
        };
        $('#left_channel').text(labels[v] || (v || '—'));
    }

    // Ejecuta todo junto (útil al abrir modal o al editar)
    function syncLeftPanelAll() {
        updateLeftTitle();
        updateLeftRemindAt();
        updateLeftPriority();
        updateLeftClient();
        updateLeftLoan();
        updateLeftType();
        updateLeftChannel();
    }

    // ============================
    //   EVENTOS: cuando el usuario cambia algo
    // ============================

    // título (mientras escribe)
    $(document).on('input', '#title', function () {
        updateLeftTitle();
    });

    // fecha/hora
    $(document).on('change input', '#remind_at', function () {
        updateLeftRemindAt();
    });

    // prioridad
    $(document).on('change', '#priority', function () {
        updateLeftPriority();
    });

    // tipo (opcional)
    $(document).on('change', '#type', function () {
        updateLeftType();
    });

    // canal (opcional)
    $(document).on('change', '#channel', function () {
        updateLeftChannel();
    });

    // cliente: actualiza panel + carga préstamos
    $(document).on('change', '#client_id', function () {
        updateLeftClient();

        // reset préstamo y panel
        $('#loan_id').empty().append(`<option value="">— Opcional —</option>`);
        $('#left_loan').text('—');

        const clientId = $(this).val();
        fillLoansByClient(clientId);
    });

    // préstamo
    $(document).on('change', '#loan_id', function () {
        updateLeftLoan();
    });

    // ============================
    //   Al abrir modal: sincroniza defaults y carga clientes
    // ============================
    $('#reminderModal').on('show.bs.modal', function () {
        // primero sincroniza lo que ya está en el form
        syncLeftPanelAll();

        // carga clientes (cuando termine, si hay cliente seleccionado, carga préstamos)
        fillClients().done(function () {
            // por si quedó seleccionado algo (modo edición futuro)
            updateLeftClient();

            const clientId = $('#client_id').val();
            if (clientId) {
                fillLoansByClient(clientId);
            }
        });
    });


    // ============================
    //   VER RECORDATORIO
    // ============================
    $(document).on('click', '.viewReminder', function () {

        const $btn = $(this);

        const id = $btn.data('id');

        if (!id) return;

        // 1️⃣ Mostrar loading
        if (typeof divLoading !== 'undefined' && divLoading) {
            divLoading.style.display = 'flex';
        }

        // 2️⃣ Obtener detalle desde backend (fuente confiable)
        const url = window.routes.showReminderJson.replace('__ID__', id);

        $.get(url)
            .done(function (res) {
                if (!res || res.status !== 'success') {
                    Swal.fire('Error', 'No se pudo cargar el recordatorio', 'error');
                    return;
                }

                const r = res.data || {};

                // 3️⃣ Llenar modal
                $('#vr_title').text(r.title || '—');
                $('#vr_message').text(r.message || '—');
                $('#vr_client').text(r.client || '—');
                $('#vr_loan').text(r.loan || '—');
                $('#vr_remind_at').text(r.remind_at || '—');
                $('#vr_expires_at').text(r.expires_at || '—');

                $('#vr_priority').text(r.priority || '—');
                $('#vr_type').text(r.type || '—');
                $('#vr_status').text(r.status || '—');

                // 4️⃣ Abrir modal
                $('#viewReminderModal').modal('show');

                // 5️⃣ Si NO está leído → marcar como leído
                const isRead = String($btn.data('is_read')) === '1';

                if (!isRead && window.routes.markReadReminder) {
                    $.post(
                        window.routes.markReadReminder.replace('__ID__', id)
                    ).always(function () {
                        // refrescar campana
                        if (typeof fetchNavbarReminders === 'function') {
                            fetchNavbarReminders();
                        }

                        // refrescar tabla
                        if (tableReminders) {
                            tableReminders.ajax.reload(null, false);
                        }
                    });
                }
            })
            .fail(function () {
                Swal.fire('Error', 'Error cargando el recordatorio', 'error');
            })
            .always(function () {
                if (typeof divLoading !== 'undefined' && divLoading) {
                    divLoading.style.display = 'none';
                }
            });
    });

    // ============================
    //   MARCAR COMO LEÍDO (BOTÓN)
    // ============================
    $(document).on('click', '.markReadReminder', function (e) {
        e.preventDefault();
        e.stopPropagation(); // ⛔ evita que dispare otros eventos

        const $btn = $(this);
        const id = $btn.data('id');

        if (!id) return;

        // Evitar doble click
        if ($btn.data('loading')) return;
        $btn.data('loading', true);

        // Opcional: deshabilitar botón
        $btn.prop('disabled', true);

        // Llamada backend
        $.post(
            window.routes.markReadReminder.replace('__ID__', id)
        )
            .done(function () {

                // 🔔 refrescar campana
                if (typeof fetchNavbarReminders === 'function') {
                    fetchNavbarReminders();
                }

                // 📋 refrescar tabla
                if (tableReminders) {
                    tableReminders.ajax.reload(null, false);
                }

                // 🔥 feedback rápido
                Swal.fire({
                    icon: 'success',
                    title: 'Marcado como leído',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500
                });
            })
            .fail(function () {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo marcar como leído',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
            })
            .always(function () {
                $btn.data('loading', false);
                $btn.prop('disabled', false);
            });
    });

    // ============================
    //   CANCELAR RECORDATORIO
    // ============================
    $(document).on('click', '.cancelReminder', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const id = $(this).data('id');
        if (!id) return;

        Swal.fire({
            title: '¿Cancelar recordatorio?',
            text: 'El recordatorio no se eliminará, solo se marcará como cancelado.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) return;

            divLoading && (divLoading.style.display = "flex");

            $.post(
                window.routes.cancelReminder.replace('__ID__', id)
            )
                .done(function (res) {

                    // refrescar campana
                    if (typeof fetchNavbarReminders === 'function') {
                        fetchNavbarReminders();
                    }

                    // refrescar tabla
                    if (tableReminders) {
                        tableReminders.ajax.reload(null, false);
                    }

                    Swal.fire({
                        icon: 'success',
                        title: res.message || 'Recordatorio cancelado',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2500
                    });
                })
                .fail(function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'No se pudo cancelar.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                })
                .always(function () {
                    divLoading && (divLoading.style.display = "none");
                });
        });
    });


});