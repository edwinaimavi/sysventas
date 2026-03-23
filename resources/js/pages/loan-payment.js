var divLoading = document.getElementById('divLoading');
let tableLoanPayments;

document.addEventListener("DOMContentLoaded", function () {


    // ============================
    //  IMPRIMIR TICKET (helper)
    // ============================
    function openReceipt(paymentId) {
        if (!paymentId) return;

        // Si tienes route en window.routes, úsala, si no, fallback:
        let url = (window.routes && window.routes.paymentReceipt)
            ? window.routes.paymentReceipt.replace(':id', paymentId)
            : `/admin/loan-payments/${paymentId}/receipt`;

        // Abrir en nueva ventana/pestaña para imprimir
        window.open(url, '_blank', 'width=380,height=720');
    }

    // CSRF token para AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // ============================
    //   DATATABLE PAGOS
    // ============================
    tableLoanPayments = $('#tableLoanPayments').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.routes.paymentList, // route('loan-payments.list')
            type: 'GET',
            error: function (xhr, error, thrown) {
                console.error('Error en DataTables (payments):', error, thrown);
                console.log('Respuesta del servidor:', xhr.responseText);
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'payment_code', name: 'payment_code' },
            { data: 'loan_code', name: 'loan_code' },
            { data: 'client_name', name: 'client_name' },
            { data: 'payment_date', name: 'payment_date' },
            { data: 'amount', name: 'amount' },
            { data: 'method', name: 'method' },
            { data: 'payment_type', name: 'payment_type' }, // ⭐ nuevo
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
        ]
    });

    // ============================
    //   GUARDAR PAGO (AJAX)
    // ============================
    $('#paymentForm').on('submit', function (e) {
        e.preventDefault();

        const $form = $(this);
        const id = $form.attr('data-id');

        let url = window.routes.storePayment;
        let method = 'POST';

        const formData = new FormData(this);

        // ============================
        //  VALIDACIONES
        // ============================
        const base = parseFloat($form.data('base-balance') || 0);
        const amount = parseFloat($('#amount').val() || 0);
        const type = $('#payment_type').val();

        if (!$('#loan_id').val()) {
            Swal.fire({
                icon: 'warning',
                title: 'Falta seleccionar préstamo',
                text: 'Debes seleccionar un préstamo antes de registrar el pago.'
            });
            return;
        }

        // ✅ NO permitir pagar más que el saldo
        if (base > 0 && amount > base + 0.009) {
            Swal.fire({
                icon: 'warning',
                title: 'Monto inválido',
                text: 'El monto no puede ser mayor al saldo pendiente actual.'
            });
            return;
        }

        // ✅ Reglas tipo vs monto (las que ya tenías)
        if (base > 0 && amount > 0) {
            const diff = Math.abs(base - amount);

            if (type === 'partial' && diff <= 0.009) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tipo de pago incorrecto',
                    text: 'El monto cubre el 100% del saldo. Marca "Pago total".'
                });
                return;
            }

            if (type === 'full' && diff > 0.009) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Monto insuficiente para pago total',
                    text: 'Para pago total, el monto debe ser igual al saldo pendiente actual.'
                });
                return;
            }
        }

        if (id) {
            url = `/admin/loan-payments/${id}`;
            formData.append('_method', 'PUT');
        }

        if (divLoading) divLoading.style.display = 'flex';

        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (divLoading) divLoading.style.display = 'none';

                $('#paymentModal').modal('hide');
                $form.removeAttr('data-id');

                if (tableLoanPayments) {
                    tableLoanPayments.ajax.reload(null, false);
                }

                Swal.fire({
                    icon: 'success',
                    title: response.message || 'Pago registrado correctamente.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                // ============================
                // ✅ ABRIR + IMPRIMIR TICKET
                // ============================
                // Intenta leer el ID del pago desde varias formas posibles
                let paymentId = null;

                // Caso normal: store retorna { data: { id: ... } }
                if (response && response.data) {
                    paymentId = response.data.id || response.data.payment_id || null;
                }

                // Fallbacks por si cambiaste response en otro lado
                if (!paymentId) paymentId = response.id || response.payment_id || null;

                console.log('PAYMENT ID DETECTED =>', paymentId);

                openReceipt(paymentId);
            },
            error: function (xhr) {
                if (divLoading) divLoading.style.display = 'none';

                if (xhr.status === 409 && xhr.responseJSON?.code === 'NO_CASHBOX_OPEN') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Caja cerrada',
                        text: xhr.responseJSON.message,
                    });
                    return;
                }

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
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Ocurrió un error al guardar el pago.'
                });

            }
        });
    });


    // ============================
    //   LIMPIAR MODAL AL CERRAR
    // ============================
    $('#paymentModal').on('hidden.bs.modal', function () {
        $('#cashBox').hide();
        $('#cash_received').val('');
        $('#cash_change').val('');
        $('#expense_amount').val('');
        $('#expense_type').val('');
        $('#expense_description').val('');

        const $form = $('#paymentForm');

        if ($form.length && $form[0]) {
            $form[0].reset();
        }

        $form.removeAttr('data-id');
        $form.removeData('original-remaining');
        $form.removeData('base-balance');

        $('#paymentModalLabel').text('Nuevo Pago');

        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').text('');

        $('#left_loan_code').text('No seleccionado');
        $('#left_total_payable').text('S/ 0.00');
        $('#left_client_name').text('—');
        $('#left_branch_name').text('Sucursal');
        $('#left_user_name').text('Usuario');
        $('#left_current_balance').text('S/ 0.00');   // ⭐ nuevo
        $('#left_remaining_balance').text('S/ 0.00');

        $('#remaining_balance').val('');

        $('.custom-file-label[for="receipt_file"]').text('Seleccionar archivo...');
    });


    // ============================
    //   MODAL - AL ABRIR
    // ============================
    $('#paymentModal').on('show.bs.modal', function () {
        const $form = $('#paymentForm');

        // =========================================
        // 1) GENERAR CÓDIGO SOLO SI ES NUEVO
        // =========================================
        if (!$form.attr('data-id') && window.routes && window.routes.generatePaymentCode) {
            $.get(window.routes.generatePaymentCode, function (res) {
                if (res && res.code) {
                    $('#payment_code').val(res.code);
                }
            });
        }

        // =========================================
        // 2) MOSTRAR SUCURSAL Y USUARIO
        // =========================================

        const branchName = $('#branch_id').data('branch_name') || "Sucursal";
        const userName = $('#user_id').data('user_name') || "Usuario";

        $('#left_branch_name').text(branchName);
        $('#left_user_name').text(userName);
        toggleCashBox();
    });


    // ============================
    //   CAMBIO DE PRÉSTAMO
    // ============================
    // Cuando cambia el préstamo seleccionado
    $('#loan_id').on('change', function () {
        const $opt = $(this).find('option:selected');
        const loanId = $(this).val();

        loadSchedules(loanId);

        const loanCode = $opt.data('loan_code') || '—';
        const clientName = $opt.data('client_name') || '—';

        // Resumen básico (rápido)
        $('#left_loan_code').text(loanCode);
        $('#left_client_name').text(clientName);

        if (!loanId) return;

        const url = window.routes.paymentBalance.replace(':id', loanId);

        if (divLoading) divLoading.style.display = 'flex';

        $.get(url)
            .done(function (resp) {
                if (divLoading) divLoading.style.display = 'none';

                if (!resp || resp.status !== 'success') return;

                const totalPayable = parseFloat(resp.data.total_payable || 0);
                const baseBalance = parseFloat(resp.data.remaining_balance || 0);

                // Guardamos saldo base real
                $('#paymentForm').data('base-balance', baseBalance);

                $('#left_total_payable').text('S/ ' + totalPayable.toFixed(2));
                $('#left_current_balance').text('S/ ' + baseBalance.toFixed(2));

                // Inicialmente, remaining = base
                $('#left_remaining_balance').text('S/ ' + baseBalance.toFixed(2));
                $('#remaining_balance').val(baseBalance.toFixed(2));

                // respetar tipo de pago actual
                const paymentType = $('#payment_type').val();
                if (paymentType === 'full') {
                    $('#amount').val(baseBalance.toFixed(2)).prop('readonly', true);
                    $('#left_remaining_balance').text('S/ 0.00');
                    $('#remaining_balance').val('0.00');
                } else {
                    $('#amount').val('').prop('readonly', false);
                }
            })



            .fail(function () {
                if (divLoading) divLoading.style.display = 'none';
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo obtener el saldo actual del préstamo.' });
            });

        /* loadSchedules(loanId); */
    });



    // Cuando escribe en MONTO PAGADO (S/)
    $('#amount').on('input change', function () {
        const paymentType = $('#payment_type').val();

        // si es pago total, el monto lo maneja el sistema (readonly)
        if (paymentType === 'full') {
            return;
        }

        const amount = parseFloat($(this).val() || 0);
        const base = parseFloat($('#paymentForm').data('base-balance') || 0);

        if (!base || base <= 0) {
            $('#remaining_balance').val('');
            $('#left_remaining_balance').text('S/ 0.00');
            return;
        }

        let newBalance = base - amount;
        if (newBalance < 0) newBalance = 0;

        // 🚫 Regla: pago parcial no puede cubrir el 100% del saldo
        if (paymentType === 'partial' && newBalance <= 0.009) {
            Swal.fire({
                icon: 'warning',
                title: 'Revisa el tipo de pago',
                text: 'No puedes registrar un pago parcial por el 100% del saldo pendiente. Si cancela todo, selecciona "Pago total".'
            });

            // resetear monto y saldo visual
            $(this).val('');
            $('#remaining_balance').val(base.toFixed(2));
            $('#left_remaining_balance').text('S/ ' + base.toFixed(2));
            return;
        }

        $('#remaining_balance').val(newBalance.toFixed(2));
        $('#left_remaining_balance').text('S/ ' + newBalance.toFixed(2));
    });


    $('#payment_type').on('change', function () {
        const type = $(this).val();
        const base = parseFloat($('#paymentForm').data('base-balance') || 0);

        if (!base || base <= 0) {
            $('#amount').val('').prop('readonly', false);
            $('#remaining_balance').val('');
            $('#left_remaining_balance').text('S/ 0.00');
            return;
        }

        if (type === 'full') {
            // Pago total -> monto = saldo pendiente, saldo luego del pago = 0
            $('#amount').val(base.toFixed(2)).prop('readonly', true);
            $('#remaining_balance').val('0.00');
            $('#left_remaining_balance').text('S/ 0.00');
        } else {
            // Pago parcial / amortización -> el usuario escribe el monto
            $('#amount').val('').prop('readonly', false);
            $('#remaining_balance').val(base.toFixed(2));
            $('#left_remaining_balance').text('S/ ' + base.toFixed(2));
        }
    });





    // ============================
    //   EDITAR PAGO
    // ============================
    $(document).on('click', '.editPayment', function () {

        const $btn = $(this);
        const $form = $('#paymentForm');

        // ID del pago (para saber que es edición)
        const id = $btn.data('id');

        // Datos principales
        const loanId = $btn.data('loan_id');
        const paymentCode = $btn.data('payment_code');
        const paymentDate = $btn.data('payment_date');
        const amount = $btn.data('amount');
        const capital = $btn.data('capital');
        const interest = $btn.data('interest');
        const lateFee = $btn.data('late_fee');
        const method = $btn.data('method');
        const reference = $btn.data('reference');
        const receiptNumber = $btn.data('receipt_number');
        const status = $btn.data('status');
        const notes = $btn.data('notes');
        const remainingBalance = $btn.data('remaining_balance');

        // Datos para el resumen izquierdo
        const loanCode = $btn.data('loan_code');
        const clientName = $btn.data('client_name');
        const branchName = $btn.data('branch_name');
        const userName = $btn.data('user_name');

        // Marcar el form en modo edición
        $form.attr('data-id', id);

        // ============================
        //   RELLENAR CAMPOS DEL FORM
        // ============================
        $('#loan_id').val(loanId);           // select préstamo
        $('#payment_code').val(paymentCode);
        $('#payment_date').val(paymentDate);
        $('#amount').val(amount);
        $('#capital').val(capital);
        $('#interest').val(interest);
        $('#late_fee').val(lateFee);
        $('#method').val(method || '');
        $('#reference').val(reference);
        $('#receipt_number').val(receiptNumber);
        $('#status').val(status);
        $('#notes').val(notes);

        // Saldo restante (si lo estás usando en el form)
        if (typeof remainingBalance !== 'undefined') {
            const rb = parseFloat(remainingBalance || 0);
            $('#remaining_balance').val(rb.toFixed(2));

            // base-balance para el cálculo cuando cambias el monto
            // base = saldo antes del pago = saldo_actual + monto_pagado
            const baseBalance = parseFloat(amount || 0) + rb;
            $form.data('base-balance', baseBalance);
            $('#left_remaining_balance').text('S/ ' + rb.toFixed(2));
        }

        // ============================
        //   RESUMEN IZQUIERDO
        // ============================
        $('#left_loan_code').text(loanCode || '—');
        $('#left_client_name').text(clientName || '—');
        $('#left_branch_name').text(branchName || 'Sucursal');
        $('#left_user_name').text(userName || 'Usuario');

        // (opcional) limpiar errores anteriores
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').text('');

        // Cambiar título del modal a "Editar Pago"
        $('#paymentModalLabel').text('Editar Pago');

        // Importante: evitar que al abrir el modal se regenere el código
        // Tu handler de show.bs.modal ya revisa si hay data-id,
        // así que con esto es suficiente.

        // Abrir el modal
        $('#paymentModal').modal('show');
    });




    // ============================
    //   VER PAGO
    // ============================
    $(document).on('click', '.viewPayment', function () {

        const $btn = $(this);

        // === DATOS PRINCIPALES ===
        $('#vp_payment_code').text($btn.data('payment_code') || '—');
        $('#vp_payment_date').text($btn.data('payment_date') || '—');
        $('#vp_amount').text('S/ ' + parseFloat($btn.data('amount') || 0).toFixed(2));
        $('#vp_capital').text('S/ ' + parseFloat($btn.data('capital') || 0).toFixed(2));
        $('#vp_interest').text('S/ ' + parseFloat($btn.data('interest') || 0).toFixed(2));
        $('#vp_late_fee').text('S/ ' + parseFloat($btn.data('late_fee') || 0).toFixed(2));

        // === MÉTODO / REFERENCIA ===
        $('#vp_method').text($btn.data('method') || '—');
        $('#vp_reference').text($btn.data('reference') || '—');

        // === ARCHIVO ===
        const file = $btn.data('receipt_file');
        if (file) {
            const url = `/storage/${file}`;
            const ext = file.split('.').pop().toLowerCase();

            if (['jpg', 'jpeg', 'png', 'webp'].includes(ext)) {
                $('#vp_receipt_file').html(
                    `<a href="${url}" target="_blank">
                    <img src="${url}" class="img-thumbnail" style="max-height:120px;">
                </a>`
                );
            } else if (ext === "pdf") {
                $('#vp_receipt_file').html(
                    `<a href="${url}" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-file-pdf mr-1"></i> Ver PDF
                </a>`
                );
            }
        } else {
            $('#vp_receipt_file').text('—');
        }

        // === INFO RELACIONAL ===
        $('#vp_loan_code').text($btn.data('loan_code') || '—');
        $('#vp_client_name').text($btn.data('client_name') || '—');
        $('#vp_branch').text($btn.data('branch_name') || '—');
        $('#vp_user').text($btn.data('user_name') || '—');

        // === ESTADO ===
        const status = ($btn.data('status') || '').toLowerCase();
        let label = 'Pendiente', badge = 'badge-warning';

        if (status === 'completed') { label = 'Completado'; badge = 'badge-success'; }
        if (status === 'reversed') { label = 'Revertido'; badge = 'badge-danger'; }

        $('#vp_status_badge').attr('class', `badge ${badge} py-2 px-3`).text(label);
        $('#vp_status_text').text(label);

        // === NOTAS ===
        $('#vp_notes').text($btn.data('notes') || '—');

        // ============================
        //   NUEVO: CUOTAS + GASTO
        // ============================

        // cuotas pagadas ese día (puede venir "1,2,3" o "")
        const instStr = ($btn.data('installments') || '').toString().trim();
        if (instStr) {
            // Si pagó varias cuotas, mostramos "1,2,3"
            $('#vp_installment_number').text(instStr);
        } else {
            $('#vp_installment_number').text('—');
        }

        // gasto adicional
        $('#vp_additional_expense').text('S/ ' + parseFloat($btn.data('expense_amount') || 0).toFixed(2));

        // (opcional) mostrar tipo/desc en notas o en un campo extra si lo agregas en el modal
        // console.log($btn.data('expense_type'), $btn.data('expense_description'));


        // Mostrar modal
        $('#viewPaymentModal').modal('show');
    });

    // ============================
    //   ANULAR PAGO (con contraseña)
    // ============================
    $(document).on('click', '.deletePayment', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: '¿Anular este pago?',
            text: 'El pago será marcado como REVERTIDO, se recalculará el saldo del préstamo y su estado.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Continuar',
            cancelButtonText: 'Cancelar'
        }).then((step1) => {
            if (!step1.isConfirmed) return;

            // Paso 2: pedir contraseña
            Swal.fire({
                title: 'Confirma tu identidad',
                text: 'Ingresa tu contraseña para anular el pago.',
                input: 'password',
                inputPlaceholder: 'Contraseña',
                inputAttributes: {
                    autocapitalize: 'off',
                    autocomplete: 'current-password'
                },
                showCancelButton: true,
                confirmButtonText: 'Anular pago',
                cancelButtonText: 'Cancelar',
                preConfirm: (password) => {
                    if (!password) {
                        Swal.showValidationMessage('Debes ingresar tu contraseña');
                    }
                    return password;
                }
            }).then((step2) => {
                if (!step2.isConfirmed) return;

                const password = step2.value;

                if (divLoading) divLoading.style.display = 'flex';

                $.ajax({
                    url: `/admin/loan-payments/${id}`,
                    type: 'POST', // usamos POST + _method DELETE
                    data: {
                        _method: 'DELETE',
                        password: password
                    },
                    success: function (response) {
                        if (divLoading) divLoading.style.display = 'none';

                        if (tableLoanPayments) {
                            tableLoanPayments.ajax.reload(null, false);
                            // limpiar selección y refrescar UI para siguiente pago
                            $('#loan_id').val(null).trigger('change');
                            $('#amount').val('').prop('readonly', false);
                            $('#remaining_balance').val('');
                            $('#paymentForm').removeData('base-balance');

                        }

                        Swal.fire({
                            icon: 'success',
                            title: response.message || 'Pago anulado correctamente.',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                    },
                    error: function (xhr) {
                        if (divLoading) divLoading.style.display = 'none';

                        let msg = 'No se pudo anular el pago.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });
                    }
                });
            });
        });
    });

    // ============================
    //   IMPRIMIR DESDE ACCIONES
    // ============================
    // ============================
    //   IMPRIMIR TICKET (FULLSCREEN)
    // ============================
    $(document).on('click', '.printPayment', function () {
        const id = $(this).data('id');
        if (!id) return;

        let url = (window.routes && window.routes.paymentReceipt)
            ? window.routes.paymentReceipt.replace(':id', id)
            : `/admin/loan-payments/${id}/receipt`;

        const w = screen.availWidth;
        const h = screen.availHeight;

        const printWindow = window.open(
            url,
            '_blank',
            `toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=${w},height=${h},top=0,left=0`
        );

        if (printWindow) {
            printWindow.focus();
        }
    });

    function initLoanSelect2() {
        const $loan = $('#loan_id');
        const $modal = $('#paymentModal');
        if (!$loan.length || !$modal.length) return;

        // evitar doble init
        if ($loan.hasClass('select2-hidden-accessible')) {
            $loan.select2('destroy');
        }

        $loan.select2({
            theme: 'bootstrap4',
            width: '100%',
            dropdownParent: $modal, // clave para modal
            placeholder: 'Buscar por código, nombre o DNI...',
            allowClear: true,
            /* dropdownAutoWidth: false */
        });
    }

    // ✅ inicializa cuando el modal ya está visible (no show, sino shown)
    $('#paymentModal').on('shown.bs.modal', function () {
        initLoanSelect2();

        // ✅ traer préstamos actualizados (ya no saldrán los finalizados)
        refreshLoanOptions().then(function () {
            // ✅ dejarlo limpio al abrir
            $('#loan_id').val(null).trigger('change');
        });
    });

    // ✅ (opcional) destruir al cerrar
    $('#paymentModal').on('hidden.bs.modal', function () {


        // ✅ limpiar cronograma visual sí o sí
        $('#scheduleRows').html(`
        <tr>
            <td colspan="7" class="text-center text-muted py-3">
                Seleccione un préstamo…
            </td>
        </tr>
    `);

        // (opcional) resetear también amount por si quedó autocompletado por checks
        $('#amount').val('');
        const $loan = $('#loan_id');
        if ($loan.hasClass('select2-hidden-accessible')) {
            $loan.select2('destroy');
        }
    });

    function toggleCashBox() {
        const method = ($('#method').val() || '').toLowerCase();

        if (method === 'cash') {
            $('#cashBox').show();
            $('#cash_received').prop('required', true);
            calcCashChange();
        } else {
            $('#cashBox').hide();
            $('#cash_received').prop('required', false).val('');
            $('#cash_change').val('');
        }
    }

    function calcCashChange() {
        const method = ($('#method').val() || '').toLowerCase();
        if (method !== 'cash') return;

        const amount = parseFloat($('#amount').val() || 0); // cuota (o suma de cuotas)
        const exp = parseFloat($('#expense_amount').val() || 0); // otros gastos
        const totalToCollect = amount + exp;

        const received = parseFloat($('#cash_received').val() || 0);

        let change = received - totalToCollect;
        if (change < 0) change = 0;

        $('#cash_change').val(change.toFixed(2));
    }


    $('#method').on('change', toggleCashBox);
    $('#cash_received').on('input change', calcCashChange);

    // cuando cambia monto, recalcular vuelto también
    $('#amount').on('input change', function () {
        // tu lógica actual...
        calcCashChange();
    });

    $('#expense_amount').on('input change', calcCashChange);


    function money(n) { return 'S/ ' + parseFloat(n || 0).toFixed(2); }

    function badgeStatus(st) {
        st = (st || '').toLowerCase();
        if (st === 'paid') return '<span class="badge badge-success">Pagada</span>';
        if (st === 'partial') return '<span class="badge badge-warning">Parcial</span>';
        return '<span class="badge badge-secondary">Pendiente</span>';
    }

    function loadSchedules(loanId) {
        if (!loanId || !window.routes.loanSchedulesByLoan) {
            $('#scheduleRows').html('<tr><td colspan="7" class="text-center text-muted py-3">Seleccione un préstamo…</td></tr>');
            return;
        }

        const url = window.routes.loanSchedulesByLoan.replace(':id', loanId);

        $.get(url).done(function (resp) {
            if (!resp || resp.status !== 'success') {
                $('#scheduleRows').html('<tr><td colspan="7" class="text-center text-danger py-3">No se pudo cargar el cronograma.</td></tr>');
                return;
            }

            const rows = resp.data || [];
            if (!rows.length) {
                $('#scheduleRows').html('<tr><td colspan="7" class="text-center text-muted py-3">Este préstamo no tiene cronograma.</td></tr>');
                return;
            }

            let html = '';
            rows.forEach(r => {
                const isPaid = (r.status === 'paid') || (parseFloat(r.remaining) <= 0.009);
                const disabled = isPaid ? 'disabled' : '';
                const checked = isPaid ? 'checked' : '';

                html += `
        <tr class="text-center">
          <td>
            <input type="checkbox"
              class="sch-check"
              data-remaining="${r.remaining}"
              ${checked} ${disabled}
            >
          </td>
          <td>${r.installment_no}</td>
          <td>${r.due_date}</td>
          <td>${money(r.payment)}</td>
          <td>${money(r.paid_amount)}</td>
          <td><strong>${money(r.remaining)}</strong></td>
          <td>${badgeStatus(r.status)}</td>
        </tr>
      `;
            });

            $('#scheduleRows').html(html);

            // Cuando marcas checks, recalculamos "amount" (solo si payment_type=partial)
            recalcAmountFromChecks();
        }).fail(function () {
            $('#scheduleRows').html('<tr><td colspan="7" class="text-center text-danger py-3">Error cargando cronograma.</td></tr>');
        });
    }

    function recalcAmountFromChecks() {
        const type = $('#payment_type').val();
        if (type === 'full') return; // full lo controlas con saldo total

        let sum = 0;
        $('.sch-check:checked:not(:disabled)').each(function () {
            sum += parseFloat($(this).data('remaining') || 0);
        });

        // si marcaron algo, autocompleta amount
        if (sum > 0.009) {
            $('#amount').val(sum.toFixed(2)).trigger('change');
        }
    }

    // evento: al cambiar checks
    $(document).on('change', '.sch-check', function () {
        recalcAmountFromChecks();
    });



    function refreshLoanOptions() {
        const $loan = $('#loan_id');

        // limpia opciones, deja placeholder
        $loan.empty().append('<option value="">Seleccione préstamo</option>');

        return $.get(window.routes.loansAvailable).done(function (resp) {
            if (!resp || resp.status !== 'success') return;

            resp.data.forEach(l => {
                const text = `${l.loan_code} - ${l.client_name}${l.client_document ? ' - ' + l.client_document : ''}`;
                const opt = new Option(text, l.id, false, false);

                $(opt).attr('data-loan_code', l.loan_code);
                $(opt).attr('data-client_name', l.client_name);
                $(opt).attr('data-client_document', l.client_document);
                $(opt).attr('data-remaining_balance', l.remaining_balance);
                $(opt).attr('data-total_payable', l.total_payable);

                $loan.append(opt);
            });

            // refresca select2 si está activo
            if ($loan.hasClass('select2-hidden-accessible')) {
                $loan.trigger('change.select2');
            }
        });
    }



});

// ✅ Evita que Select2 cierre el modal por propagación de eventos
$(document).on('select2:opening select2:open select2:closing select2:close', '#loan_id', function (e) {
    e.stopPropagation();
});

// También por si el click cae en el container
$(document).on('mousedown', '#paymentModal .select2-container', function (e) {
    e.stopPropagation();
});