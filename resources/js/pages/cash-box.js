
let tableCash;
const cashForm = document.getElementById('cashForm');
const btnOpenCash = document.getElementById('btnOpenCash');

/* ===============================
   INIT
================================ */
document.addEventListener('DOMContentLoaded', function () {
    initCashTable();
    initCashEvents();


    // ===============================
    // APERTURA DE CAJA
    // ===============================
    $('#cashOpenForm').on('submit', function (e) {

        e.preventDefault();
        divLoading.style.display = "flex";

        const $form = $(this);
        const formData = new FormData(this);

        $.ajax({
            url: window.routes.cashStore,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,

            success: function (response) {

                divLoading.style.display = "none";

                if (!response.success) {
                    Swal.fire({
                        icon: 'warning',
                        title: response.message || 'No se pudo abrir la caja',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    return;
                }

                $('#cashOpenModal').modal('hide');

                // 🔄 si luego hay DataTable
                if (typeof tableCash !== 'undefined') {
                    tableCash.ajax.reload(null, false);
                }

                Swal.fire({
                    title: response.message,
                    icon: "success",
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            },

            error: function (xhr) {

                divLoading.style.display = "none";

                // limpiar errores anteriores
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                if (xhr.status === 422) {

                    if (xhr.responseJSON?.message) {
                        Swal.fire({
                            icon: 'warning',
                            title: xhr.responseJSON.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        return;
                    }

                    const errors = xhr.responseJSON.errors || {};

                    $.each(errors, function (key, messages) {
                        const input = $('#' + key);
                        input.addClass('is-invalid');
                        $('#' + key + '-error').text(messages[0]);
                    });

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Ocurrió un error inesperado',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3500
                    });
                }
            }
        });
    });


    $('#cashOpenModal').on('hidden.bs.modal', function () {

        const form = $('#cashOpenForm')[0];

        // resetear formulario
        form.reset();

        // limpiar errores visuales
        $('#cashOpenForm .is-invalid').removeClass('is-invalid');
        $('#cashOpenForm .invalid-feedback').text('');

        // volver a setear fecha actual
        $('#opened_at').val(
            new Date().toISOString().slice(0, 16)
        );
    });


    $(document).on('click', '.closeCash', function () {

        const cashId = $(this).data('id');
        const url = window.routes.cashSummary.replace(':id', cashId);

        divLoading.style.display = "flex";

        $.get(url, function (response) {

            divLoading.style.display = "none";

            if (!response.success) return;

            const data = response.data;

            // guardar id
            $('#cash_id').val(data.id);

            // llenar campos
            $('#closing_opening_amount').val('S/ ' + parseFloat(data.opening_amount).toFixed(2));
            $('#closing_total_income').val('S/ ' + parseFloat(data.total_income).toFixed(2));
            $('#closing_total_expense').val('S/ ' + parseFloat(data.total_expense).toFixed(2));
            $('#closing_expected_balance').val('S/ ' + parseFloat(data.expected_balance).toFixed(2));

            $('#closing_real_amount').val('');
            $('#closing_difference').val('');

            $('#cashCloseModal').modal('show');

        }).fail(function () {
            divLoading.style.display = "none";

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo cargar la información de la caja'
            });
        });

    });


    $('#closing_real_amount').on('input', function () {

        let real = parseFloat($(this).val()) || 0;

        let expected = $('#closing_expected_balance')
            .val()
            .replace('S/', '')
            .trim();

        expected = parseFloat(expected) || 0;

        let diff = real - expected;

        let inputDiff = $('#closing_difference');

        inputDiff.val('S/ ' + diff.toFixed(2));

        inputDiff.removeClass('text-success text-danger');

        if (diff > 0) {
            inputDiff.addClass('text-success'); // sobra dinero
        } else if (diff < 0) {
            inputDiff.addClass('text-danger'); // falta dinero
        }
    });

    function initCashTable() {

        tableCash = $('#tableCash').DataTable({
            processing: true,
            serverSide: true,
            ajax: window.routes.cashList,

            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'id', name: 'id' },
                { data: 'branch', name: 'branch.name' },
                { data: 'opened_at', name: 'opened_at' },
                { data: 'opening_amount', name: 'opening_amount' },
                { data: 'ingresos', orderable: false, searchable: false },
                { data: 'egresos', orderable: false, searchable: false },
                { data: 'saldo_final', orderable: false, searchable: false },
                { data: 'status_badge', orderable: false, searchable: false },
                { data: 'actions', orderable: false, searchable: false }
            ],

            responsive: true,
            autoWidth: false,

            dom: `
            <'row mb-3'
                <'col-sm-12 col-md-6 text-start'l>
                <'col-sm-12 col-md-6 text-end'f>
            >
            <'row'<'col-sm-12'tr>>
            <'row mt-3'
                <'col-sm-12 col-md-5'i>
                <'col-sm-12 col-md-7 d-flex justify-content-center justify-content-md-end'p>
            >
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

                document.querySelectorAll('[data-bs-toggle="tooltip"]')
                    .forEach(el => new bootstrap.Tooltip(el));
            }
        });

    }


    // ===============================
    // ABRIR MODAL REPOSICIÓN
    // ===============================
    $(document).on('click', '.replenishCash', function () {

        const cashId = $(this).data('id');

        $('#replenish_cash_id').val(cashId);
        $('#replenish_amount').val('');

        $('#cashReplenishModal').modal('show');
    });



    // ===============================
    // REGISTRAR REPOSICIÓN
    // ===============================
    $('#cashReplenishForm').on('submit', function (e) {

        e.preventDefault();
        divLoading.style.display = "flex";

        const formData = new FormData(this);

        $.ajax({
            url: window.routes.cashReplenish,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,

            success: function (response) {

                divLoading.style.display = "none";

                if (!response.success) {
                    Swal.fire({
                        icon: 'warning',
                        title: response.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    return;
                }

                $('#cashReplenishModal').modal('hide');
                tableCash.ajax.reload(null, false);

                Swal.fire({
                    icon: 'success',
                    title: response.message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            },

            error: function (xhr) {

                divLoading.style.display = "none";

                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                if (xhr.status === 422) {

                    const errors = xhr.responseJSON.errors || {};

                    $.each(errors, function (key, messages) {
                        $('#' + key).addClass('is-invalid');
                        $('#' + key + '-error').text(messages[0]);
                    });

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Error inesperado'
                    });
                }
            }
        });
    });




});




/* ===============================
   EVENTS
================================ */
function initCashEvents() {

    if (cashForm) {
        cashForm.addEventListener('submit', function (e) {
            e.preventDefault();
            openCash();
        });
    }

}



