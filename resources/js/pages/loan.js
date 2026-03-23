var divLoading = document.getElementById('divLoading');
let tableLoan;
// ============================
// LOCKS ANTI DOBLE CLICK (por acción)
// ============================
const submitLocks = {
    loanSave: false,
    disbursementSave: false,
    incrementSave: false,
    refinanceSave: false
};

function lock(action) {
    if (submitLocks[action]) return false; // ya está bloqueado
    submitLocks[action] = true;
    return true;
}

function unlock(action) {
    submitLocks[action] = false;
}

document.addEventListener("DOMContentLoaded", function () {
    //csrf token para AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // ============================
    //   DATATABLE PRÉSTAMOS
    // ============================
    // INICIALIZAR DATATABLE PARA PRÉSTAMOS
    tableLoan = $('#tableLoan').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.routes.loanList,
            type: 'GET',
            error: function (xhr, error, thrown) {
                console.error('Error en DataTables (loans):', error, thrown);
                console.log('Respuesta del servidor:', xhr.responseText);
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'id', name: 'id' },
            { data: 'loan_code', name: 'loan_code' },
            { data: 'client_name', name: 'client_name' },
            { data: 'guarantor_name', name: 'guarantor_name' },
            { data: 'amount', name: 'amount' },
            { data: 'term_months', name: 'term_months' },
            { data: 'status', name: 'status' },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],
        responsive: true,
        autoWidth: false,
        dom: `
        <'row mb-3'<'col-sm-12 col-md-6 text-start'l><'col-sm-12 col-md-6 text-end'f>>
        <'row'<'col-sm-12'tr>>
        <'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 d-flex justify-content-center justify-content-md-end'p>>
        <'row mt-3'<'col-sm-12 text-center'B>>
    `,
        language: {
            url: "/vendor/datatables/js/i18n/es-ES.json"
        },
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


    //Ver información del préstamo
    $(document).on('click', '.viewLoan', function () {

        // ================================
        // 1. CAPTURAR TODOS LOS DATA-*
        // ================================
        const loan_code = $(this).data('loan_code');
        const status = $(this).data('status');
        const client_name = $(this).data('client_name');
        const guarantor_name = $(this).data('guarantor_name');
        const branch_name = $(this).data('branch_name');
        const user_name = $(this).data('user_name');

        const amount = $(this).data('amount');
        const term_months = $(this).data('term_months');
        const interest_rate = $(this).data('interest_rate');

        const monthly_payment = $(this).data('monthly_payment');
        const total_payable = $(this).data('total_payable');
        const disbursement_date = $(this).data('disbursement_date');

        const id = $(this).data('id');
        const created_at = $(this).data('created_at');
        const notes = $(this).data('notes');

        // ================================
        // 2. RELLENAR CAMPOS EN EL MODAL
        // ================================

        $('#vl_loan_code').text(loan_code || '—');
        $('#vl_client_name').text(client_name || '—');
        $('#vl_guarantor_name').text(guarantor_name || 'Sin garante');
        $('#vl_branch').text(branch_name || '—');
        $('#vl_user').text(user_name || '—');

        $('#vl_amount').text(`S/ ${parseFloat(amount || 0).toFixed(2)}`);
        $('#vl_term_months').text((term_months || 0) + ' meses');
        $('#vl_interest_rate').text((interest_rate || 0) + ' %');
        $('#vl_monthly_payment').text(`S/ ${parseFloat(monthly_payment || 0).toFixed(2)}`);
        $('#vl_total_payable').text(`S/ ${parseFloat(total_payable || 0).toFixed(2)}`);
        $('#vl_disbursement_date').text(disbursement_date || '—');

        $('#vl_refinances_section').addClass('d-none');
        $('#vl_refinances_list').html('');
        $('#vl_refinances_summary').text('');
        $('#vl_refinance_badge_wrap').addClass('d-none');


        $('#vl_id').text(id);
        $('#vl_created_at').text(created_at || '—');
        $('#vl_notes').text(notes || '—');

        // ================================
        // 3. BADGE DE ESTADO
        // ================================
        let badgeClass = 'badge-secondary';
        let badgeText = '—';

        switch (status) {
            case 'pending':
                badgeClass = 'badge-warning';
                badgeText = 'Pendiente';
                break;
            case 'approved':
                badgeClass = 'badge-primary';
                badgeText = 'Aprobado';
                break;
            case 'rejected':
                badgeClass = 'badge-danger';
                badgeText = 'Rechazado';
                break;
            case 'disbursed':
                badgeClass = 'badge-success';
                badgeText = 'Desembolsado';
                break;
            case 'canceled':
                badgeClass = 'badge-secondary';
                badgeText = 'Cancelado';
                break;
        }

        $('#vl_status_badge')
            .removeClass()
            .addClass(`badge ${badgeClass} py-2 px-3`)
            .text(badgeText);

        $('#vl_status_text').text(badgeText);

        // ================================
        // 4. LIMPIAR SECCIÓN DE DESEMBOLSOS
        // ================================
        $('#vl_disbursements_section').addClass('d-none');
        $('#vl_disbursements_list').html('');
        $('#vl_disbursements_summary').text('');

        // ================================
        // 5. CARGAR DESEMBOLSOS POR AJAX
        // ================================
        loadDisbursements(id);

        loadSchedule(id);


        // 6. CARGAR INCREMENTOS POR AJAX
        loadIncrements(id);

        // 7. CARGAR REFINANCIAMIENTOS POR AJAX
        loadRefinances(id);


        // ================================
        // 6. MOSTRAR MODAL
        // ================================
        $('#viewLoanModal').modal('show');
    });



    // ============================
    //   EDITAR PRÉSTAMO
    // ============================
    $(document).on('click', '.editLoan', function () {

        const $btn = $(this);

        // 1. TOMAMOS TODOS LOS DATA-*
        const id = $btn.data('id');
        const loan_code = $btn.data('loan_code');
        const client_id = $btn.data('client_id');
        const guarantor_id = $btn.data('guarantor_id');
        const branch_id = $btn.data('branch_id');
        const user_id = $btn.data('user_id');

        const amount = $btn.data('amount');
        const term_months = $btn.data('term_months');
        const interest_rate = $btn.data('interest_rate');
        const monthly_payment = $btn.data('monthly_payment');
        const total_payable = $btn.data('total_payable');
        const disbursement_date = $btn.data('disbursement_date');
        const due_date = $btn.data('due_date');
        const status = $btn.data('status'); // 👈 ya lo tenías
        const notes = $btn.data('notes');

        const client_name = $btn.data('client_name');
        const guarantor_name = $btn.data('guarantor_name');
        const branch_name = $btn.data('branch_name');
        const user_name = $btn.data('user_name');

        const $form = $('#loanForm');

        // 2. SETEAMOS EL ID PARA QUE EL SUBMIT SEPA QUE ES EDICIÓN
        $form.attr('data-id', id);

        // 👉 Guardamos estado original en un data-atributo del form
        $form.attr('data-original-status', status || '');

        // 3. RELLENAMOS LOS CAMPOS DEL FORM
        $('#loan_code').val(loan_code);
        $('#client_id').val(client_id);
        $('#guarantor_id').val(guarantor_id || '');
        $('#branch_id').val(branch_id);
        $('#user_id').val(user_id);

        $('#amount').val(amount);
        $('#term_months').val(term_months);
        $('#interest_rate').val(interest_rate);
        $('#monthly_payment').val(monthly_payment);
        $('#total_payable').val(total_payable);
        $('#disbursement_date').val(disbursement_date);
        $('#due_date').val(due_date || '');
        $('#status').val(status); // 👈 importante

        $('#notes').val(notes);

        // 4. LIMPIAR ERRORES
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').text('');

        // 5. RESUMEN IZQUIERDO
        $('#left_client_name').text(client_name || 'No seleccionado');
        $('#left_guarantor_name').text(guarantor_name || 'Opcional');
        if (branch_name) $('#left_branch_name').text(branch_name);
        if (user_name) $('#left_user_name').text(user_name);

        // 6. CAMBIAR ICONO Y TÍTULO DEL MODAL
        $('.icon_modal_loan').html('<i class="far fa-edit text-secondary"></i>');
        $('#loanModalLabel').html('Editar Préstamo');

        // 7. MOSTRAR MODAL
        $('#loanModal').modal('show');
    });




    // ============================
    //   CÁLCULO AUTOMÁTICO CUOTA / TOTAL
    // ============================
    // ============================
    //   CÁLCULO AUTOMÁTICO CUOTA / TOTAL (SEGÚN TU SISTEMA)
    // ============================
    function recalcLoan() {
        const amount = parseFloat($('#amount').val());
        const n = parseInt($('#term_months').val(), 10);
        const ratePercent = parseFloat($('#interest_rate').val()); // % mensual

        if (!amount || !n || n <= 0 || isNaN(ratePercent) || ratePercent < 0) {
            $('#monthly_payment').val('');
            $('#total_payable').val('');
            return;
        }

        const r = ratePercent / 100; // decimal

        let pmt = 0;
        if (r === 0) {
            pmt = amount / n;
        } else {
            const pow = Math.pow(1 + r, n);
            pmt = amount * ((r * pow) / (pow - 1));
        }

        const total = pmt * n;

        $('#monthly_payment').val(pmt.toFixed(2));
        $('#total_payable').val(total.toFixed(2));
    }




    // ⭐ REACTIVAR EL CÁLCULO AUTOMÁTICO
    $('#amount, #term_months, #interest_rate').on('input change', function () {
        recalcLoan();
    });





    $('#loanModal').on('show.bs.modal', function () {
        const $form = $('#loanForm');

        // si NO hay data-id => es NUEVO
        if (!$form.attr('data-id')) {
            // limpiar estado original
            $form.removeAttr('data-original-status');

            // por si acaso, setear select a pendiente
            $('#status').val('pending');

            $.get(window.routes.generateCode, function (res) {
                $('#loan_code').val(res.code);
            });
        }
    });

    // ============================
    //   GUARDAR / ACTUALIZAR PRÉSTAMO
    // ============================
    $('#loanForm').on('submit', function (e) {
        e.preventDefault();

        // ⛔ evita doble envío SOLO para este submit
        if (!lock('loanSave')) return;

        divLoading && (divLoading.style.display = 'flex');

        const $form = $(this);
        const id = $form.attr('data-id');

        // respetar estado original si era disbursed
        const originalStatus = $form.attr('data-original-status');
        if (originalStatus === 'disbursed') {
            $('#status').val('disbursed');
        }

        let url, method;
        const formData = $form.serialize();

        if (id) {
            url = `/admin/loans/${id}`;
            method = 'POST';
        } else {
            url = window.routes.storeLoan;
            method = 'POST';
        }

        let dataToSend = formData;
        if (id) dataToSend = formData + '&_method=PUT';

        $.ajax({
            url: url,
            type: method,
            data: dataToSend,
            success: function (response) {
                divLoading && (divLoading.style.display = 'none');
                unlock('loanSave');

                $('#loanModal').modal('hide');
                $form.removeAttr('data-id');

                tableLoan && tableLoan.ajax.reload(null, false);

                Swal.fire({
                    icon: 'success',
                    title: response.message || 'Préstamo guardado correctamente',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            },
            error: function (xhr) {
                divLoading && (divLoading.style.display = 'none');
                unlock('loanSave');

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors || {};

                    $form.find('.is-invalid').removeClass('is-invalid');
                    $form.find('.invalid-feedback').text('');

                    $.each(errors, function (field, messages) {
                        const input = $('#' + field);
                        if (input.length) {
                            input.addClass('is-invalid');
                            $('#' + field + '-error').text(messages[0]);
                        }
                    });
                } else {
                    console.error('Error al guardar préstamo', xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'Ocurrió un error al guardar el préstamo.'
                    });
                }
            }
        });
    });



    //limpiar modal al cerrarlo

    $('#loanModal').on('hidden.bs.modal', function () {
        unlock('loanSave');
        const $form = $('#loanForm');

        $form[0].reset();
        $form.removeAttr('data-id');

        $('#loanModalLabel').html('Nuevo Préstamo');

        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').text('');




        // Resumen izquierdo
        $('#left_client_name').text('No seleccionado');
        $('#left_guarantor_name').text('Opcional');
    });



    $('#viewLoanModal').on('hidden.bs.modal', function () {
        $('#vl_disbursements_section').addClass('d-none');
        $('#vl_disbursements_list').html('');
        $('#vl_disbursements_summary').text('');

        // incrementos
        $('#vl_increments_section').addClass('d-none');
        $('#vl_increments_list').html('');
        $('#vl_increments_summary').text('');

        // refinanciamientos
        $('#vl_refinances_section').addClass('d-none');
        $('#vl_refinances_list').html('');
        $('#vl_refinances_summary').text('');
        $('#vl_refinance_badge_wrap').addClass('d-none');


        $('#vl_schedule_section').addClass('d-none');
        $('#vl_schedule_tbody').html('');
        $('#vl_schedule_summary').text('');

    });

    $('#client_id').on('change', function () {
        const name = $(this).find('option:selected').data('name') || 'No seleccionado';
        $('#left_client_name').text(name);
    });


    $('#guarantor_id').on('change', function () {
        const text = $(this).find('option:selected').text() || 'Opcional';
        $('#left_guarantor_name').text(text);
    });



    // ============================
    //   ELIMINAR PRÉSTAMO
    // ============================
    $(document).on('click', '.deleteLoan', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {

            if (result.isConfirmed) {
                $.ajax({
                    url: `${window.routes.deleteLoan}/${id}`, // definido en tu blade
                    type: 'DELETE',

                    success: function (response) {

                        if (tableLoan) {
                            tableLoan.ajax.reload(null, false);
                        }

                        Swal.fire({
                            icon: 'success',
                            title: response.message || 'Préstamo eliminado correctamente',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    },

                    error: function (xhr) {
                        console.error('Error al eliminar préstamo', xhr);

                        Swal.fire(
                            'Error',
                            (xhr.responseJSON && xhr.responseJSON.message)
                                ? xhr.responseJSON.message
                                : 'Ocurrió un error al eliminar el préstamo.',
                            'error'
                        );
                    }
                });
            }
        });
    });


    //INICIO PARA EL DESEMBOLSO =======================================================================



    // ============================
    //   ABRIR MODAL DE DESEMBOLSO
    // ============================
    $(document).on('click', '.disbursementModal', function () {

        const $btn = $(this);

        const loan_id = $btn.data('loan_id');
        const loan_code = $btn.data('loan_code');
        const client_name = $btn.data('client_name');
        const amount = parseFloat($btn.data('amount') || 0);
        const totalDisbursed = parseFloat($btn.data('total_disbursed') || 0);    // total desembolsado
        const remaining = parseFloat($btn.data('remaining') || (amount - totalDisbursed));

        // 👇 Normalizamos igual que en Blade
        const rawStatus = $btn.data('status') || '';
        const status = String(rawStatus).toLowerCase().trim();

        const isFully = String($btn.data('is_fully_disbursed')) === '1';

        // (Opcional) para que tu mismo veas qué llega
        console.log('status:', status, 'isFully:', isFully, 'loan_id:', loan_id,
            'amount:', amount, 'totalDisbursed:', totalDisbursed, 'remaining:', remaining);

        // 1) Si ya está totalmente desembolsado
        if (isFully || remaining <= 0) {
            Swal.fire({
                icon: 'info',
                title: 'No se puede desembolsar',
                text: 'El préstamo ya se encuentra totalmente desembolsado.'
            });
            return;
        }


        // 2) Validar estado del préstamo (permitir approved y disbursed)
        if (!['approved', 'disbursed'].includes(status)) {
            let motivo = '';
            switch (status) {
                case 'pending':
                    motivo = 'El préstamo aún está pendiente. Primero debe ser aprobado.';
                    break;
                case 'rejected':
                    motivo = 'El préstamo está rechazado. No se puede realizar desembolsos.';
                    break;
                case 'canceled':
                    motivo = 'El préstamo está cancelado. No se puede realizar desembolsos.';
                    break;
                default:
                    motivo = 'El estado actual del préstamo no permite realizar desembolsos.';
            }

            Swal.fire({
                icon: 'info',
                title: 'No se puede desembolsar',
                text: motivo
            });

            return; // NO abrimos el modal
        }


        // 3) Si está aprobado y no está totalmente desembolsado -> ABRIR MODAL

        // Setear hidden y textos
        $('#disb_loan_id').val(loan_id);
        $('#disb_loan_code').text(loan_code || '—');
        $('#disb_loan_code_badge').text(loan_code || '—');
        $('#disb_client_name').text(client_name || '—');
        $('#disb_loan_amount').text('S/ ' + amount.toFixed(2));

        // Resumen izquierdo
        $('#disb_loan_amount')
            .text('S/ ' + amount.toFixed(2))
            .data('loan_total', amount);

        $('#disb_total_disbursed')
            .text('S/ ' + totalDisbursed.toFixed(2));

        $('#disb_remaining_amount')
            .text('S/ ' + remaining.toFixed(2))
            .data('remaining', remaining);


        // Limpiar campos del formulario de desembolso
        const $form = $('#disbursementForm');
        $form.data('remaining', remaining);


        $form[0].reset();
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').text('');

        // Por defecto estado "completed"
        $('#disb_status').val('completed');

        // Abrir modal
        $('#disbursementModal').modal('show');
    });




    // ============================
    //   GUARDAR DESEMBOLSO (AJAX)
    // ============================
    // ============================
    //   GUARDAR DESEMBOLSO (AJAX)
    // ============================
    $('#disbursementForm').on('submit', function (e) {
        e.preventDefault();

        // ⛔ evita doble envío SOLO de desembolso
        if (!lock('disbursementSave')) return;

        const $form = $(this);

        const disbAmount = parseFloat($('#disb_amount').val() || 0);

        let remaining = parseFloat($form.data('remaining'));
        if (isNaN(remaining)) {
            remaining = parseFloat($('#disb_remaining_amount').data('remaining') || 0);
        }

        if (!disbAmount || disbAmount <= 0) {
            Swal.fire({ icon: 'warning', title: 'Monto inválido', text: 'Debes ingresar un monto mayor a 0.' });
            unlock('disbursementSave');
            return;
        }

        const diff = Math.abs(disbAmount - remaining);

        if (diff > 0.009) {
            let mensaje = (disbAmount > remaining)
                ? `El monto ingresado (S/ ${disbAmount.toFixed(2)}) excede el saldo pendiente (S/ ${remaining.toFixed(2)}).`
                : `El saldo pendiente es S/ ${remaining.toFixed(2)} y estás registrando S/ ${disbAmount.toFixed(2)}. Debe coincidir exacto.`;

            Swal.fire({ icon: 'warning', title: 'Monto no coincide', text: mensaje });
            unlock('disbursementSave');
            return;
        }

        divLoading && (divLoading.style.display = 'flex');

        const formData = new FormData(this);

        $.ajax({
            url: window.routes.storeDisbursement,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                divLoading && (divLoading.style.display = 'none');
                unlock('disbursementSave');

                $('#disbursementModal').modal('hide');

                tableLoan && tableLoan.ajax.reload(null, false);

                Swal.fire({
                    icon: 'success',
                    title: response.message || 'Desembolso registrado correctamente',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            },
            error: function (xhr) {
                divLoading && (divLoading.style.display = 'none');
                unlock('disbursementSave');

                if (xhr.status === 422) {

                    // 🔴 CASO: error de negocio (ej: caja cerrada)
                    if (xhr.responseJSON && xhr.responseJSON.message && !xhr.responseJSON.errors) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No se puede desembolsar',
                            text: xhr.responseJSON.message
                        });
                        return;
                    }

                    // 🔵 CASO: validaciones de formulario
                    const errors = xhr.responseJSON.errors || {};

                    $form.find('.is-invalid').removeClass('is-invalid');
                    $form.find('.invalid-feedback').text('');

                    $.each(errors, function (field, messages) {
                        const input = $('#' + field);
                        if (input.length) {
                            input.addClass('is-invalid');
                            $('#' + field + '-error').text(messages[0]);
                        }
                    });

                    return;
                }
                else {
                    console.error('Error al guardar desembolso', xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'Ocurrió un error al guardar el desembolso.'
                    });
                }
            }
        });
    });




    // limpiar modal de desembolso al cerrarlo (opcional)
    $('#disbursementModal').on('hidden.bs.modal', function () {
        unlock('disbursementSave');
        const $form = $('#disbursementForm');
        $form[0].reset();
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').text('');
    });

    // Mostrar nombre del archivo en el label del custom-file
    $(document).on('change', '#receipt_file', function () {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName || 'Seleccionar archivo...');
    });


    // ============================
    //   HELPERS DESembolso
    // ============================
    function formatCurrency(amount) {
        const num = parseFloat(amount || 0);
        return 'S/ ' + num.toFixed(2);
    }

    function buildDisbursementCard(d) {
        const date = d.disbursement_date || '—';
        const amount = formatCurrency(d.amount);
        const method = d.method || '—';
        const reference = d.reference || '—';
        const notes = d.notes || '—';
        const receiptNumber = d.receipt_number || '—';
        const status = (d.status || '—');

        let fileHtml = '';

        if (d.receipt_file_url) {
            const ext = (d.receipt_file_type || '').toLowerCase();

            // Si es imagen
            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                fileHtml = `
                <div class="mt-3">
                    <small class="text-muted d-block mb-1">Comprobante</small>
                    <a href="${d.receipt_file_url}" target="_blank">
                        <img src="${d.receipt_file_url}" class="img-thumbnail"
                             style="max-height:150px; object-fit:cover;">
                    </a>
                </div>
            `;
            }

            // Si es PDF
            if (ext === 'pdf') {
                fileHtml = `
                <div class="mt-3">
                    <small class="text-muted d-block mb-1">Comprobante</small>
                    <a href="${d.receipt_file_url}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-file-pdf mr-1"></i> Ver PDF
                    </a>
                </div>
            `;
            }
        }

        return `
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Fecha de desembolso</small>
                        <div class="font-weight-600">${date}</div>

                        <small class="text-muted d-block mt-2">Monto desembolsado</small>
                        <div class="font-weight-600 text-dark">${amount}</div>

                        <small class="text-muted d-block mt-2">Método</small>
                        <div class="font-weight-600">${method}</div>

                        <small class="text-muted d-block mt-2">N° comprobante</small>
                        <div class="font-weight-600">${receiptNumber}</div>
                    </div>

                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Referencia</small>
                        <div class="font-weight-600">${reference}</div>

                        <small class="text-muted d-block mt-2">Estado</small>
                        <div class="font-weight-600 text-capitalize">${status}</div>

                        <small class="text-muted d-block mt-2">Notas</small>
                        <div class="font-weight-600 text-muted" style="white-space:pre-line;">
                            ${notes}
                        </div>

                        ${fileHtml}
                    </div>
                </div>
            </div>
        </div>
    `;
    }

    function buildIncrementCard(inc) {
        const date = inc.created_at || '—';
        const oldAmt = formatCurrency(inc.old_amount);
        const incAmt = formatCurrency(inc.increment_amount);
        const newAmt = formatCurrency(inc.new_amount);
        const notes = inc.notes || '—';
        const user = inc.user_name || '—';

        return `
        <div class="card mb-2 border-0 shadow-sm">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Fecha</small>
                        <div class="font-weight-600">${date}</div>
                    </div>
                    <div class="text-right">
                        <small class="text-muted d-block">Monto anterior</small>
                        <div class="font-weight-600">${oldAmt}</div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-sm-6 mb-1">
                        <small class="text-muted d-block">Incremento</small>
                        <div class="font-weight-600 text-primary">${incAmt}</div>
                    </div>
                    <div class="col-sm-6 mb-1">
                        <small class="text-muted d-block">Monto nuevo</small>
                        <div class="font-weight-600 text-success">${newAmt}</div>
                    </div>
                </div>

                <div class="mt-2 d-flex justify-content-between">
                    <div>
                        <small class="text-muted d-block">Registrado por</small>
                        <div class="font-weight-600">${user}</div>
                    </div>
                </div>

                <div class="mt-2">
                    <small class="text-muted d-block">Notas</small>
                    <div class="font-weight-600 text-muted" style="white-space:pre-line;">
                        ${notes}
                    </div>
                </div>
            </div>
        </div>
    `;
    }


    function buildRefinanceCard(r) {
        const date = r.refinance_date || r.created_at || '—';
        const base = formatCurrency(r.base_balance);
        const rate = (r.interest_rate ?? '—') + ' %';
        const term = (r.new_term_months ?? '—') + ' meses';
        const due = r.new_due_date || '—';
        const notes = r.notes || '—';
        const status = (r.status || '—');
        const user = r.user_name || '—';

        return `
    <div class="card mb-2 border-0 shadow-sm">
        <div class="card-body py-2">
            <div class="d-flex justify-content-between">
                <div>
                    <small class="text-muted d-block">Fecha</small>
                    <div class="font-weight-600">${date}</div>
                </div>
                <div class="text-right">
                    <small class="text-muted d-block">Estado</small>
                    <div class="font-weight-600 text-capitalize">${status}</div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-sm-6 mb-1">
                    <small class="text-muted d-block">Saldo base</small>
                    <div class="font-weight-600">${base}</div>
                </div>
                <div class="col-sm-6 mb-1">
                    <small class="text-muted d-block">Tasa</small>
                    <div class="font-weight-600">${rate}</div>
                </div>
                <div class="col-sm-6 mb-1">
                    <small class="text-muted d-block">Nuevo plazo</small>
                    <div class="font-weight-600">${term}</div>
                </div>
                <div class="col-sm-6 mb-1">
                    <small class="text-muted d-block">Nuevo vencimiento</small>
                    <div class="font-weight-600">${due}</div>
                </div>
            </div>

            <div class="mt-2">
                <small class="text-muted d-block">Registrado por</small>
                <div class="font-weight-600">${user}</div>
            </div>

            <div class="mt-2">
                <small class="text-muted d-block">Notas</small>
                <div class="font-weight-600 text-muted" style="white-space:pre-line;">${notes}</div>
            </div>
        </div>
    </div>
    `;
    }

    function loadRefinances(loanId) {
        const $section = $('#vl_refinances_section');
        const $list = $('#vl_refinances_list');
        const $summary = $('#vl_refinances_summary');
        const $badgeWrap = $('#vl_refinance_badge_wrap');

        if (!loanId) {
            $section.addClass('d-none');
            $list.html('');
            $summary.text('');
            $badgeWrap.addClass('d-none');
            return;
        }

        const url = window.routes.refinanceHistory.replace(':id', loanId);

        $.ajax({
            url,
            type: 'GET',
            success: function (resp) {
                if (!resp || resp.status !== 'success') {
                    $section.addClass('d-none');
                    $badgeWrap.addClass('d-none');
                    return;
                }

                const data = resp.data || {};
                const refinances = data.refinances || [];

                // ✅ badge rojo si tiene refinance activo o si hay historial
                const hasActive = data.summary && data.summary.has_active_refinance;
                if (hasActive || refinances.length > 0) {
                    $badgeWrap.removeClass('d-none');
                } else {
                    $badgeWrap.addClass('d-none');
                }

                if (!refinances.length) {
                    $section.addClass('d-none');
                    $list.html('');
                    $summary.text('');
                    return;
                }

                let html = '';
                refinances.forEach(r => html += buildRefinanceCard(r));
                $list.html(html);

                const count = refinances.length;
                const label = count === 1 ? 'refinanciamiento' : 'refinanciamientos';

                const lastDate = (data.summary && data.summary.last_refinance_date) ? data.summary.last_refinance_date : null;
                const extra = lastDate ? ` • Último: ${lastDate}` : '';

                $summary.text(`${count} ${label}${extra}`);
                $section.removeClass('d-none');
            },
            error: function (xhr) {
                console.error('Error cargando refinanciamientos', xhr);
                $section.addClass('d-none');
                $badgeWrap.addClass('d-none');
            }
        });
    }


    function loadDisbursements(loanId) {
        const $section = $('#vl_disbursements_section');
        const $list = $('#vl_disbursements_list');
        const $summary = $('#vl_disbursements_summary');

        if (!loanId) {
            $section.addClass('d-none');
            $list.html('');
            $summary.text('');
            return;
        }

        const url = window.routes.loanDisbursementsByLoan.replace(':id', loanId);

        $.ajax({
            url: url,
            type: 'GET',
            success: function (resp) {
                if (!resp || resp.status !== 'success') {
                    $section.addClass('d-none');
                    return;
                }

                const data = resp.data || {};
                const disbursements = data.disbursements || [];

                if (!disbursements.length) {
                    // No hay desembolsos -> ocultar bloque
                    $section.addClass('d-none');
                    $list.html('');
                    $summary.text('');
                    return;
                }

                let html = '';
                let total = 0;

                disbursements.forEach(function (d) {
                    total += parseFloat(d.amount || 0);
                    html += buildDisbursementCard(d);
                });

                $list.html(html);

                // Resumen: "2 desembolsos • S/ 1,500.00"
                const count = disbursements.length;
                const label = count === 1 ? 'desembolso' : 'desembolsos';
                $summary.text(`${count} ${label} • ${formatCurrency(total)}`);

                $section.removeClass('d-none');
            },
            error: function (xhr) {
                console.error('Error cargando desembolsos', xhr);
                // ante error no rompemos el modal, solo ocultamos sección
                $('#vl_disbursements_section').addClass('d-none');
            }
        });
    }


    function loadIncrements(loanId) {
        const $section = $('#vl_increments_section');
        const $list = $('#vl_increments_list');
        const $summary = $('#vl_increments_summary');

        if (!loanId) {
            $section.addClass('d-none');
            $list.html('');
            $summary.text('');
            return;
        }

        const url = window.routes.loanIncrementsByLoan.replace(':id', loanId);

        $.ajax({
            url: url,
            type: 'GET',
            success: function (resp) {
                if (!resp || resp.status !== 'success') {
                    $section.addClass('d-none');
                    return;
                }

                const data = resp.data || {};
                const increments = data.increments || [];

                if (!increments.length) {
                    $section.addClass('d-none');
                    $list.html('');
                    $summary.text('');
                    return;
                }

                let html = '';
                let total = 0;

                increments.forEach(function (inc) {
                    total += parseFloat(inc.increment_amount || 0);
                    html += buildIncrementCard(inc);
                });

                $list.html(html);

                const count = increments.length;
                const label = count === 1 ? 'incremento' : 'incrementos';
                const totalText = formatCurrency(total);

                // Si backend mandó el último monto nuevo:
                let extra = '';
                if (data.summary && data.summary.last_new_amount !== null) {
                    extra = ` • Monto actual: ${formatCurrency(data.summary.last_new_amount)}`;
                }

                $summary.text(`${count} ${label} • +${totalText}${extra}`);

                $section.removeClass('d-none');
            },
            error: function (xhr) {
                console.error('Error cargando incrementos', xhr);
                $section.addClass('d-none');
            }
        });
    }


    // ============================
    //   ABRIR MODAL INCREMENTO
    // ============================
    $(document).on('click', '.incrementLoan', function () {
        const $btn = $(this);

        const loanId = $btn.data('id');
        const loanCode = $btn.data('loan_code');
        const clientName = $btn.data('client_name');
        const amount = parseFloat($btn.data('amount') || 0);
        const rawStatus = $btn.data('status') || '';
        const status = String(rawStatus).toLowerCase().trim();

        // Reglas de negocio (ajústalas tú):
        // Ejemplo: solo permitir incrementar si está approved o disbursed
        if (!['approved', 'disbursed'].includes(status)) {
            Swal.fire({
                icon: 'info',
                title: 'No se puede incrementar',
                text: 'Solo se puede incrementar préstamos aprobados o desembolsados.'
            });
            return;
        }

        // Llenar el form del modal
        $('#inc_loan_id').val(loanId);
        $('#inc_loan_code').text(loanCode || '—');
        $('#inc_client_name').text(clientName || '—');
        $('#inc_current_amount').text('S/ ' + amount.toFixed(2));

        // Limpiar campos
        $('#increment_amount').val('');
        $('#inc_notes').val('');
        $('#inc_new_amount_box').hide();
        $('#inc_new_amount_text').text('S/ 0.00');

        const $form = $('#incrementForm');
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').text('');

        // Mostrar modal
        $('#incrementModal').modal('show');
    });

    // Preview del nuevo monto del préstamo
    $('#increment_amount').on('input', function () {
        const inc = parseFloat($(this).val() || 0);
        const currentText = $('#inc_current_amount').text().replace('S/', '').trim();
        const current = parseFloat(currentText || 0);

        if (inc > 0) {
            const nuevo = current + inc;
            $('#inc_new_amount_text').text('S/ ' + nuevo.toFixed(2));
            $('#inc_new_amount_box').show();
        } else {
            $('#inc_new_amount_box').hide();
        }
    });

    $('#incrementForm').on('submit', function (e) {
        e.preventDefault();

        if (!lock('incrementSave')) return;

        divLoading && (divLoading.style.display = 'flex');

        const $form = $(this);
        const formData = $form.serialize();

        $.ajax({
            url: window.routes.storeLoanIncrement,
            type: 'POST',
            data: formData,
            success: function (res) {
                divLoading && (divLoading.style.display = 'none');
                unlock('incrementSave');

                $('#incrementModal').modal('hide');
                tableLoan && tableLoan.ajax.reload(null, false);

                Swal.fire({
                    icon: 'success',
                    title: res.message || 'Incremento registrado correctamente.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            },
            error: function (xhr) {
                divLoading && (divLoading.style.display = 'none');
                unlock('incrementSave');

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors || {};
                    $form.find('.is-invalid').removeClass('is-invalid');
                    $form.find('.invalid-feedback').text('');

                    $.each(errors, function (field, messages) {
                        const input = $('#' + field);
                        if (input.length) {
                            input.addClass('is-invalid');
                            $('#' + field + '-error').text(messages[0]);
                        }
                    });
                } else {
                    console.error('Error guardando incremento', xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'Ocurrió un error al guardar el incremento.'
                    });
                }
            }
        });
    });




    function recalcDueDate() {
        const disb = $('#disbursement_date').val();
        const termMonths = parseInt($('#term_months').val(), 10);

        if (!disb || !termMonths || termMonths <= 0) {
            $('#due_date').val('');
            return;
        }

        // disb = 'YYYY-MM-DD'
        const parts = disb.split('-');
        if (parts.length !== 3) {
            $('#due_date').val('');
            return;
        }

        const year = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10) - 1; // JS: 0-11
        const day = parseInt(parts[2], 10);

        const d = new Date(year, month, day);
        d.setMonth(d.getMonth() + termMonths);

        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');

        $('#due_date').val(`${yyyy}-${mm}-${dd}`);
    }
    // Recalcular cada vez que cambie monto / plazo / tasa
    $('#term_months, #disbursement_date').on('input change', function () {
        recalcDueDate();
    });


    // ============================
    // REFINANCIAR - ABRIR MODAL + CARGAR INFO
    // ============================
    $(document).on('click', '.refinanceLoan', function () {
        const loanId = $(this).data('id');
        const loanCode = $(this).data('loan_code') || '—';
        $('#rf_new_due_date').val('');
        $('#rf_notes').val('');

        // limpiar errores
        $('#rf_refinance_date_error, #rf_new_term_months_error, #rf_base_balance_error, #rf_interest_rate_error').text('');

        // set básico en modal
        $('#rf_loan_id').val(loanId);
        $('#rf_loan_code').text(loanCode);

        // defaults
        const today = new Date().toISOString().slice(0, 10);
        $('#rf_refinance_date').val(today);
        $('#rf_new_term_months').val(1);
        // si new_due_date es requerido en controller, ponlo por defecto hoy+30
        // (opcional) si quieres:
        // $('#rf_new_due_date').val(addDays(today, 30));

        // mostrar cargando
        $('#rf_remaining').text('Cargando...');
        $('#rf_due_date').text('Cargando...');
        $('#rf_overdue_badge').html('');

        // abrir modal
        $('#refinanceModal').modal('show');

        // pedir info al backend
        const url = window.routes.refinanceInfo.replace(':id', loanId);

        $.get(url)
            .done(function (res) {
                if (!res || res.status !== 'success') {
                    Swal.fire({ icon: 'error', title: 'Error', text: (res && res.message) ? res.message : 'No se pudo cargar info.' });
                    return;
                }

                const d = res.data;

                // mostrar info
                $('#rf_remaining').text('S/ ' + parseFloat(d.remaining_balance || 0).toFixed(2));
                $('#rf_due_date').text(d.due_date || '—');

                // badge vencido
                if (d.is_overdue) {
                    $('#rf_overdue_badge').html('<span class="badge bg-danger ms-2">Vencido</span>');
                } else {
                    $('#rf_overdue_badge').html('<span class="badge bg-success ms-2">No vencido</span>');
                }

                // si ya tiene refinance activo, bloquear guardar (por seguridad UI)
                if (d.has_active_refinance) {
                    $('#btnSaveRefinance').prop('disabled', true);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Refinanciamiento activo',
                        text: 'Este préstamo ya tiene un refinanciamiento activo.'
                    });
                } else {
                    $('#btnSaveRefinance').prop('disabled', false);
                }

                // sugerir base_balance con saldo pendiente
                $('#rf_base_balance').val(parseFloat(d.remaining_balance || 0).toFixed(2));

                // sugerir new_due_date si quieres: usar la misma due_date actual o +30
                if (d.due_date) {
                    // opcional: poner una fecha por defecto mayor a refinance_date
                    // si quieres usar la misma due_date, descomenta:
                    // $('#rf_new_due_date').val(d.due_date);
                }
            })
            .fail(function (xhr) {
                console.error(xhr.responseText);
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la información del préstamo.' });
            });
    });


    // ============================
    // REFINANCIAR - GUARDAR (AJAX)
    // ============================
    $(document).on('click', '#btnSaveRefinance', function () {

        if (!lock('refinanceSave')) return;

        const loanId = $('#rf_loan_id').val();

        const payload = {
            loan_id: loanId,
            refinance_date: $('#rf_refinance_date').val(),
            new_term_months: $('#rf_new_term_months').val(),
            new_due_date: $('#rf_new_due_date').val(),
            base_balance: $('#rf_base_balance').val(),
            interest_rate: $('#rf_interest_rate').val(),
            notes: $('#rf_notes').val(),
        };

        // limpiar errores
        $('#rf_refinance_date_error, #rf_new_term_months_error, #rf_base_balance_error, #rf_interest_rate_error').text('');

        // validación rápida front
        if (!payload.loan_id) {
            Swal.fire({ icon: 'warning', title: 'Falta préstamo', text: 'No se detectó el préstamo.' });
            unlock('refinanceSave');
            return;
        }
        if (!payload.refinance_date) {
            $('#rf_refinance_date_error').text('La fecha es obligatoria.');
            unlock('refinanceSave');
            return;
        }
        if (!payload.new_term_months || parseInt(payload.new_term_months) < 1) {
            $('#rf_new_term_months_error').text('El plazo debe ser mínimo 1.');
            unlock('refinanceSave');
            return;
        }
        if (!payload.new_due_date) {
            Swal.fire({ icon: 'warning', title: 'Falta vencimiento', text: 'Debes seleccionar la nueva fecha de vencimiento.' });
            unlock('refinanceSave');
            return;
        }

        $.ajax({
            url: window.routes.refinanceStore,
            type: 'POST',
            data: payload,
            success: function (res) {
                unlock('refinanceSave');

                if (!res || res.status !== 'success') {
                    Swal.fire({ icon: 'error', title: 'Error', text: (res && res.message) ? res.message : 'No se pudo refinanciar.' });
                    return;
                }

                $('#refinanceModal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: res.message || 'Refinanciado correctamente',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });

                tableLoan && tableLoan.ajax.reload(null, false);
            },
            error: function (xhr) {
                unlock('refinanceSave');

                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errs = xhr.responseJSON.errors;

                    if (errs.refinance_date) $('#rf_refinance_date_error').text(errs.refinance_date[0]);
                    if (errs.new_term_months) $('#rf_new_term_months_error').text(errs.new_term_months[0]);
                    if (errs.interest_rate) $('#rf_interest_rate_error').text(errs.interest_rate[0]);
                    if (errs.new_due_date) Swal.fire({ icon: 'warning', title: 'Revisa la fecha', text: errs.new_due_date[0] });

                    return;
                }

                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error al guardar refinanciamiento.';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }
        });
    });

    $('#refinanceModal').on('hidden.bs.modal', function () {
        unlock('refinanceSave');
    });



    // ============================
    //   SELECT2 CLIENTE (buscador)
    // ============================
    function initClientSelect2() {
        const $client = $('#client_id');

        if (!$client.length) return;

        // Evitar doble inicialización
        if ($client.hasClass('select2-hidden-accessible')) {
            $client.select2('destroy');
        }

        $client.select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Buscar por nombre o DNI...',
            allowClear: true,
            dropdownParent: $('#loanModal'),
            matcher: function (params, data) {

                if ($.trim(params.term) === '') {
                    return data;
                }

                const term = params.term.toLowerCase();
                const text = (data.text || '').toLowerCase();

                // Buscar en texto visible (nombre + DNI)
                if (text.includes(term)) {
                    return data;
                }

                return null;
            }
        });
    }

    // Inicializar al abrir modal
    $('#loanModal').on('shown.bs.modal', function () {
        initClientSelect2();
    });



    function money(v) {
        const n = Number(v || 0);
        return 'S/ ' + n.toFixed(2);
    }

    function buildScheduleRow(r) {
        const mes = r.installment_no ?? '—';
        const venc = r.due_date ? r.due_date : '—';

        return `
        <tr>
            <td class="text-center">${mes}</td>
            <td>${venc}</td>
            <td class="text-right">${money(r.opening_balance)}</td>
            <td class="text-right">${money(r.interest)}</td>
            <td class="text-right">${money(r.amortization)}</td>
            <td class="text-right font-weight-bold">${money(r.payment)}</td>
        </tr>
    `;
    }

    function loadSchedule(loanId) {
        const $section = $('#vl_schedule_section');
        const $tbody = $('#vl_schedule_tbody');
        const $summary = $('#vl_schedule_summary');

        $section.addClass('d-none');
        $tbody.html('');
        $summary.text('');

        if (!loanId || !window.routes.loanSchedulesByLoan) return;

        const url = window.routes.loanSchedulesByLoan.replace(':id', loanId);

        $.get(url)
            .done(function (resp) {
                if (!resp || resp.status !== 'success') return;

                let rows = [];

                // ✅ Normaliza a rows siempre
                if (Array.isArray(resp.data)) {
                    rows = resp.data;
                } else {
                    rows = (resp.data && resp.data.rows) ? resp.data.rows : [];
                }

                // ⛔ ignorar month0 completamente
                if (!rows.length) return;

                let html = '';
                rows.forEach(r => html += buildScheduleRow(r));
                $tbody.html(html);

                $summary.text(`${rows.length} cuotas`);
                $section.removeClass('d-none');
            })
            .fail(function (xhr) {
                console.error('Error cargando cronograma', xhr.responseText || xhr);
            });
    }





});
