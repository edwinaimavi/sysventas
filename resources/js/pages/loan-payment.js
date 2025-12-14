var divLoading = document.getElementById('divLoading');
let tableLoanPayments;

document.addEventListener("DOMContentLoaded", function () {
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
        const id = $form.attr('data-id'); // para futuro EDIT

        let url = window.routes.storePayment;   // route('loan-payments.store')
        let method = 'POST';

        const formData = new FormData(this); // porque hay archivo
        // ============================
        //  VALIDACIÓN TIPO vs MONTO
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

        if (base > 0 && amount > 0) {
            const diff = Math.abs(base - amount);

            // pago parcial cubriendo el 100% del saldo
            if (type === 'partial' && diff <= 0.009) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tipo de pago incorrecto',
                    text: 'El monto ingresado cubre el 100% del saldo pendiente. Debes marcar el pago como "Pago total".'
                });
                return;
            }

            // pago total que NO cubre todo el saldo
            if (type === 'full' && diff > 0.009) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Monto insuficiente para pago total',
                    text: 'Para un pago total, el monto debe ser igual al saldo pendiente actual.'
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
            },
            error: function (xhr) {
                if (divLoading) divLoading.style.display = 'none';

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
                    console.error('Error al guardar pago', xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'Ocurrió un error al guardar el pago.'
                    });
                }
            }
        });
    });


    // ============================
    //   LIMPIAR MODAL AL CERRAR
    // ============================
    $('#paymentModal').on('hidden.bs.modal', function () {
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
    });


    // ============================
    //   CAMBIO DE PRÉSTAMO
    // ============================
    // Cuando cambia el préstamo seleccionado
    $('#loan_id').on('change', function () {
        const $opt = $(this).find('option:selected');

        const loanCode = $opt.data('loan_code') || '—';
        const clientName = $opt.data('client_name') || '—';
        const totalPayable = parseFloat($opt.data('total_payable') || 0);
        const remainingDb = parseFloat($opt.data('remaining_balance') || 0);

        // saldo pendiente actual desde BD
        const baseBalance = remainingDb > 0 ? remainingDb : totalPayable;

        // Guardamos el saldo base para cálculos
        $('#paymentForm').data('base-balance', baseBalance);

        // Resumen izquierdo
        $('#left_loan_code').text(loanCode);
        $('#left_client_name').text(clientName);
        $('#left_total_payable').text('S/ ' + totalPayable.toFixed(2));

        // ⭐ Saldo pendiente actual (antes de este pago)
        $('#left_current_balance').text('S/ ' + baseBalance.toFixed(2));

        // ⭐ Al inicio, el saldo luego del pago es igual (todavía no escribe monto)
        $('#left_remaining_balance').text('S/ ' + baseBalance.toFixed(2));
        $('#remaining_balance').val(baseBalance.toFixed(2));

        // Tipo de pago
        const paymentType = $('#payment_type').val();

        if (paymentType === 'full') {
            // Pago total -> monto = saldo pendiente, saldo luego del pago = 0
            $('#amount').val(baseBalance.toFixed(2)).prop('readonly', true);
            $('#left_remaining_balance').text('S/ 0.00');
            $('#remaining_balance').val('0.00');
        } else {
            // Pago parcial -> usuario escribe monto
            $('#amount').val('').prop('readonly', false);
        }
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

});
