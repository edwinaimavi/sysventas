/* ======================================================
   REPORTES - PRÉSTAMOS
   ====================================================== */

var divLoading = document.getElementById('divLoading');
let tableReportLoans;

document.addEventListener('DOMContentLoaded', function () {

    initCashBook(); // 👈 NUEVO

    // ============================
    // CSRF PARA AJAX
    // ============================
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // ============================
    // DATATABLE PRÉSTAMOS
    // ============================
    initLoansReportTable();

    // ============================
    // APLICAR FILTROS
    // ============================
    $('#btnApplyFilters').on('click', function () {
        if (tableReportLoans) {
            tableReportLoans.ajax.reload();
        }
    });

    // ============================
    // EXPORTACIONES
    // ============================
    $('#btnLoansPdf').on('click', function (e) {
        e.preventDefault();
        window.open(
            buildUrl(window.routes.commercialPdf, getReportParams()),
            '_blank'
        );
    });

    $('#btnLoansExcel').on('click', function (e) {
        e.preventDefault();
        window.location.href = buildUrl(window.routes.loansExcel, getReportParams());
    });
});


/* ======================================================
   INIT DATATABLE
   ====================================================== */
function initLoansReportTable() {

    tableReportLoans = $('#tableReportLoans').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        destroy: true,

        ajax: {
            url: window.routes.reportsLoans,
            type: 'GET',
            data: function (d) {
                const p = getReportParams();
                d.date_from = p.date_from;
                d.date_to = p.date_to;
                d.branch_id = p.branch_id;
                d.client_id = p.client_id; // ✅
            },
            error: function (xhr) {
                console.error('Error DataTable Reportes', xhr.responseText);
            }
        },

        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'loan_code', name: 'loan_code' },
            { data: 'client', name: 'client.full_name' },
            { data: 'created_at', name: 'created_at' },
            { data: 'amount', name: 'amount' },
            { data: 'total_payable', name: 'total_payable' },

            // ✅ NUEVO (préstamos)
            { data: 'paid_total', orderable: false, searchable: false },
            { data: 'capital_total', orderable: false, searchable: false },
            { data: 'interest_total', orderable: false, searchable: false },
            { data: 'installments_paid', orderable: false, searchable: false },
            { data: 'remaining', orderable: false, searchable: false },

            { data: 'status', orderable: false, searchable: false }
        ],


        dom: `
            <'row mb-2'
                <'col-sm-12 col-md-6'l>
                <'col-sm-12 col-md-6 text-end'f>
            >
            <'row'
                <'col-sm-12'tr>
            >
            <'row mt-2'
                <'col-sm-12 col-md-5'i>
                <'col-sm-12 col-md-7 d-flex justify-content-end'p>
            >
        `,

        language: {
            url: "/vendor/datatables/js/i18n/es-ES.json"
        },

        preDrawCallback: function () {
            divLoading && divLoading.classList.remove('d-none');
        },

        drawCallback: function () {
            divLoading && divLoading.classList.add('d-none');
        }
    });
}


/* ======================================================
   HELPERS
   ====================================================== */
function getReportParams() {
    return {
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        branch_id: $('#branch_id').val(),
        client_id: $('#client_id').val(), // ✅ nuevo
    };
}

function buildUrl(url, params) {
    const q = new URLSearchParams(params).toString();
    return q ? `${url}?${q}` : url;
}
/*  */

let dtPayments = null;

/* $('a[data-toggle="tab"][href="#panel-payments"]').on('shown.bs.tab', function () {
    loadOperationsReport();
    if (dtPayments) return;

    dtPayments = $('#tableReportPayments').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.routes.reportsPayments,
            data: function (d) {
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
                d.branch_id = $('#branch_id').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'payment_code' },
            { data: 'loan_code' },
            { data: 'client_name' },
            { data: 'payment_date' },
            { data: 'amount' },
            { data: 'method' },
            { data: 'status' },
        ]
    });
}); */

function loadRecovery() {
    $.get(window.routes.reportsRecovery, {
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        branch_id: $('#branch_id').val(),
        client_id: $('#client_id').val(), // ✅
    }).done(resp => {
        if (!resp || resp.status !== 'success') return;
        $('#kpi_total_disbursed').text('S/ ' + Number(resp.data.total_disbursed).toFixed(2));
        $('#kpi_total_paid').text('S/ ' + Number(resp.data.total_paid).toFixed(2));
        $('#kpi_recovery_rate').text(resp.data.recovery_rate + '%');
    });
}

$('a[data-toggle="tab"][href="#panel-recovery"]').on('shown.bs.tab', loadCashFlowReport);
$('#btnApplyFilters').on('click', function () {
    if (dtPayments) dtPayments.ajax.reload();
    loadRecovery();
});

function loadOperationsReport() {

    const data = {
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        branch_id: $('#branch_id').val(),
    };

    $.get(window.routes.reportsOperations, data, function (res) {

        const montoCobrado = parseFloat(res.monto_cobrado) || 0;
        const gastosExtras = parseFloat(res.gastos_adicionales) || 0;
        const vuelto = parseFloat(res.vuelto_cliente) || 0;

        const ingresos = montoCobrado + gastosExtras;

        const salidas =
            parseFloat(res.capital_revolvente || 0) +
            parseFloat(res.capital_cuotas || 0);

        // 🔥 USAR EL SALDO QUE YA CALCULÓ EL BACKEND
        const saldo = parseFloat(res.saldo_caja) || 0;


        // INGRESOS
        $('#op_monto_cobrado').text(formatMoney(res.monto_cobrado));
        $('#op_capital_recuperado').text(formatMoney(res.capital_recuperado));
        $('#op_intereses_cobrados').text(formatMoney(res.intereses_cobrados));
        $('#op_gastos_adicionales').text(formatMoney(res.gastos_adicionales));


        // SALIDAS

        $('#op_capital_revolvente').text(formatMoney(res.capital_revolvente));
        $('#op_capital_cuotas').text(formatMoney(res.capital_cuotas));

        // RESUMEN
        $('#op_total_ingresos').text(formatMoney(ingresos));
        $('#op_total_salidas').text(formatMoney(salidas));
        $('#op_saldo_caja').text(formatMoney(saldo));
    });
}

function loadCashFlowReport() {

    $.get(window.routes.reportsOperations, {
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        branch_id: $('#branch_id').val(),
        client_id: $('#client_id').val(),
    }, function (res) {

        const apertura = parseFloat(res.monto_apertura) || 0;
        const cobrado = parseFloat(res.monto_cobrado) || 0;
        const reposicion = parseFloat(res.reposicion_caja) || 0;
        const vuelto = parseFloat(res.vuelto_cliente) || 0;

        const gastosExtras = parseFloat(res.gastos_adicionales) || 0;
        const ingresos = apertura + reposicion + cobrado + gastosExtras;



        const revolvente = parseFloat(res.capital_revolvente) || 0;
        const cuotas = parseFloat(res.capital_cuotas) || 0;
        const otras = 0;

        const salidas = revolvente + cuotas + otras;

        const saldo = ingresos - salidas;

        // PINTAR
        $('#cash_apertura').text(formatMoney(apertura));
        $('#cash_cobrado').text(formatMoney(cobrado));
        $('#cash_reposicion').text(formatMoney(reposicion));

        $('#cash_gastos_extras').text(formatMoney(gastosExtras));

        $('#cash_total_ingresos').text(formatMoney(ingresos));

        $('#cash_vuelto_cliente').text(formatMoney(vuelto));
        $('#cash_capital_revolvente').text(formatMoney(revolvente));
        $('#cash_capital_cuotas').text(formatMoney(cuotas));
        $('#cash_otras_salidas').text(formatMoney(otras));
        $('#cash_total_salidas').text(formatMoney(salidas));

        $('#cash_saldo_final').text(formatMoney(saldo));
    });
}





// BOTÓN FILTROS
$('#btnApplyFilters').on('click', function () {
    loadOperationsReport();
    loadCashFlowReport();
});

// FORMATO MONEDA
function formatMoney(value) {
    return 'S/ ' + Number(value).toLocaleString('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}


function loadCommercialReport() {

    $.get(window.routes.reportsCommercial, getReportParams(), function (res) {

        $('#com_rev_capital').text(formatMoney(res.revolvente.capital));
        $('#com_rev_interest').text(formatMoney(res.revolvente.interest));

        $('#com_cuo_capital').text(formatMoney(res.cuotas.capital));
        $('#com_cuo_interest').text(formatMoney(res.cuotas.interest));

        $('#com_ven_capital').text(formatMoney(res.vencido.capital));
        $('#com_ven_interest').text(formatMoney(res.vencido.interest));
    });
}

$('a[data-toggle="tab"][href="#panel-loans"]').on('shown.bs.tab', loadCommercialReport);
$('#btnApplyFilters').on('click', loadCommercialReport);


// ===============================
// REPORTE DE OPERACIONES
// ===============================
$('#btnOperationsRefresh').on('click', function () {

    const params = {
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        branch_id: $('#branch_id').val(),
        client_id: $('#client_id').val()
    };

    $.get(window.routes.reportsOperations, params, function (res) {

        const apertura = parseFloat(res.monto_apertura) || 0;
        const reposicion = parseFloat(res.reposicion_caja) || 0;
        const montoCobrado = parseFloat(res.monto_cobrado) || 0;
        const capitalRecuperado = parseFloat(res.capital_recuperado) || 0;
        const intereses = parseFloat(res.intereses_cobrados) || 0;
        const vueltoCliente = parseFloat(res.vuelto_cliente) || 0;
        const gastosExtras = parseFloat(res.gastos_adicionales) || 0;

        // ✅ INGRESOS REALES DE CAJA
        const totalIngresos = apertura + reposicion + montoCobrado + gastosExtras;


        $('#op_monto_cobrado').text('S/ ' + montoCobrado.toFixed(2));
        $('#op_capital_recuperado').text('S/ ' + capitalRecuperado.toFixed(2));
        $('#op_intereses_cobrados').text('S/ ' + intereses.toFixed(2));
        $('#op_gastos_adicionales').text('S/ ' + gastosExtras.toFixed(2));


        // ===== SALIDAS =====
        const capitalRevolvente = parseFloat(res.capital_revolvente) || 0;
        const capitalCuotas = parseFloat(res.capital_cuotas) || 0;

        const totalSalidas = capitalRevolvente + capitalCuotas;

        $('#op_vuelto_cliente').text('S/ ' + vueltoCliente.toFixed(2));
        $('#op_capital_revolvente').text('S/ ' + capitalRevolvente.toFixed(2));
        $('#op_capital_cuotas').text('S/ ' + capitalCuotas.toFixed(2));

        // ===== RESUMEN =====
        $('#op_total_ingresos').text('S/ ' + totalIngresos.toFixed(2));
        $('#op_total_salidas').text('S/ ' + totalSalidas.toFixed(2));

        const saldoCaja = totalIngresos - totalSalidas;

        $('#op_saldo_caja').text('S/ ' + saldoCaja.toFixed(2));

        // ===== COLOR SEGÚN SALDO =====
        const saldoBox = $('#op_saldo_caja').closest('.small-box');
        saldoBox.removeClass('bg-success bg-danger');

        if (res.saldo_caja < 0) {
            saldoBox.addClass('bg-danger');
        } else {
            saldoBox.addClass('bg-success');
        }

    }).fail(function () {
        alert('Error al cargar el reporte de operaciones');
    });

});

$('#btnOperationsPdf').on('click', function () {

    const params = $.param({
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        branch_id: $('#branch_id').val(),
        client_id: $('#client_id').val()
    });

    const url = window.routes.reportsOperationsPdf + '?' + params;

    window.open(url, '_blank');
});

$('#btnCashPdf').on('click', function (e) {
    e.preventDefault();

    const params = $.param({
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        branch_id: $('#branch_id').val(),
        client_id: $('#client_id').val()
    });

    window.open(window.routes.reportsCashPdf + '?' + params, '_blank');
});


/* ======================================================
   MODAL DETALLE DE REPORTES
====================================================== */

let tableReportDetail = null;

// Helper: texto de filtros activos
function buildDetailContext() {
    const from = $('#date_from').val() || '—';
    const to = $('#date_to').val() || '—';

    const branchText = $('#branch_id option:selected').text() || 'Todas';
    const clientText = $('#client_id option:selected').text() || 'Todos';

    return `Desde ${from} hasta ${to} · Sucursal: ${branchText} · Cliente: ${clientText}`;
}

// Inicializar DataTable del modal
function initDetailTable(type) {

    if (tableReportDetail) {
        tableReportDetail.destroy();
        $('#tableReportDetail tbody').empty();
        $('#tableReportDetail thead').empty();
        $('#tableReportDetail tfoot').empty();
    }

    let columns = [];
    let headerHtml = `<tr>`;
    let footerHtml = `<tr class="table-success font-weight-bold">`;

    // ===== COLUMNAS BASE =====
    const baseHeaders = [
        '#', 'Fecha', 'Tipo', 'Concepto', 'Cliente', 'Préstamo', 'Monto'
    ];

    baseHeaders.forEach((h, i) => {
        headerHtml += `<th>${h}</th>`;
        if (i < 6) {
            footerHtml += `<th></th>`;
        } else {
            footerHtml += `<th class="text-right"></th>`;
        }
    });

    headerHtml += `</tr>`;

    columns = [
        { data: 'index' },
        { data: 'date' },
        { data: 'type' },
        { data: 'concept' },
        { data: 'client' },
        { data: 'loan' },
        {
            data: 'amount',
            className: 'text-right',
            render: d => formatMoney(d)
        }
    ];

    // ===== SOLO INGRESOS TIENE CAPITAL / INTERÉS / GASTOS =====
    if (type === 'ingresos' || type === 'cash_in') {


        headerHtml = headerHtml.replace('</tr>', `
            <th class="text-right">Capital</th>
            <th class="text-right">Interés</th>
            <th class="text-right">Gastos</th>
        </tr>`);

        footerHtml += `
            <th class="text-right"></th>
            <th class="text-right"></th>
            <th class="text-right"></th>
        `;

        columns.push(
            {
                data: 'capital',
                className: 'text-right',
                render: d => formatMoney(d)
            },
            {
                data: 'interest',
                className: 'text-right',
                render: d => formatMoney(d)
            },
            {
                data: 'expenses',
                className: 'text-right',
                render: d => formatMoney(d)
            }
        );
    }

    footerHtml += `</tr>`;

    $('#tableReportDetail thead').html(headerHtml);
    $('#tableReportDetail tfoot').html(footerHtml);

    tableReportDetail = $('#tableReportDetail').DataTable({
        processing: true,
        serverSide: false,
        paging: true,
        searching: false,
        ordering: false,
        autoWidth: false,

        ajax: {
            url: window.routes.reportsDetails,
            type: 'GET',
            data: function (d) {
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
                d.branch_id = $('#branch_id').val();
                d.client_id = $('#client_id').val();
                d.detail_type = type;
            },
            dataSrc: function (res) {
                return res.detail || [];
            }
        },

        columns: columns,

        language: {
            url: "/vendor/datatables/js/i18n/es-ES.json"
        },

        footerCallback: function (row, data) {

            let totalAmount = 0;
            let totalCapital = 0;
            let totalInterest = 0;
            let totalExpenses = 0;

            data.forEach(item => {
                totalAmount += parseFloat(item.amount || 0);
                totalCapital += parseFloat(item.capital || 0);
                totalInterest += parseFloat(item.interest || 0);
                totalExpenses += parseFloat(item.expenses || 0);
            });

            const api = this.api();

            api.column(6).footer().innerHTML = formatMoney(totalAmount);

            if (type === 'ingresos') {
                api.column(7).footer().innerHTML = formatMoney(totalCapital);
                api.column(8).footer().innerHTML = formatMoney(totalInterest);
                api.column(9).footer().innerHTML = formatMoney(totalExpenses);
            }
        }
    });
}



// Abrir modal genérico
function openDetailModal(type, title) {

    $('#modalReportDetailTitle').text(title);
    $('#modalReportDetailContext').text(buildDetailContext());

    $('#modalReportDetail').modal('show');

    initDetailTable(type);
}

// ===============================
// BOTONES → MODAL DETALLE
// ===============================

// INGRESOS
$('#btnDetailIngresos').on('click', function () {
    openDetailModal(
        'ingresos',
        'Detalle de ingresos (pagos recibidos)'
    );
});

// SALIDAS
$('#btnDetailSalidas').on('click', function () {
    openDetailModal(
        'salidas',
        'Detalle de salidas (préstamos entregados)'
    );
});

// APERTURA
$('#btnDetailApertura').on('click', function () {
    openDetailModal(
        'cash_in',
        'Detalle completo de ingresos en caja'
    );
});


// TOTAL SALIDAS (CUADRE)
$('#btnDetailCashOut').on('click', function () {
    openDetailModal(
        'cash_out',
        'Detalle de salidas de caja'
    );
});

let tableCashBook;

function initCashBook() {

    tableCashBook = $('#tableCashBook').DataTable({

        processing: true,
        serverSide: false,

        ajax: {
            url: window.routes.reportsCashbook,
            data: function (d) {
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
                d.branch_id = $('#branch_id').val();
            }
        },

        columns: [
            { data: 'fecha' },
            { data: 'concepto' },
            {
                data: 'ingreso',
                render: d => d > 0 ? formatMoney(d) : ''
            },
            {
                data: 'salida',
                render: d => d > 0 ? formatMoney(d) : ''
            },
            {
                data: 'saldo',
                render: d => formatMoney(d)
            }
        ]

    });
}

