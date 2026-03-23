let kpiFilter = null;
$(function () {

    let table = $('#tableAdvancedReport').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.routes.advancedData,
            data: function (d) {
                d.date_from = $('#adv_date_from').val();
                d.date_to = $('#adv_date_to').val();
                d.branch_id = $('#adv_branch').val();
                d.kpi_filter = kpiFilter; // 👈 NUEVO
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },

            { data: 'loan_date', name: 'l.created_at' },
            { data: 'due_date', name: 'l.due_date' },
            { data: 'client', name: 'c.full_name' },
            { data: 'code', name: 'l.loan_code' },


            { data: 'amount', name: 'l.amount' },

            { data: 'total_paid', orderable: false, searchable: false },
            { data: 'total_capital', orderable: false, searchable: false },
            { data: 'total_interest', orderable: false, searchable: false },
            { data: 'total_expenses', orderable: false, searchable: false },
            { data: 'balance', orderable: false, searchable: false },

            { data: 'status', orderable: false, searchable: false },

            {
                data: 'actions',
                orderable: false,
                searchable: false
            }



        ],
        drawCallback: function (settings) {

            let api = this.api();

            function parseNumber(value) {
                return parseFloat(value.replace('S/ ', '').replace(',', '')) || 0;
            }

            let totalAmount = 0;
            let totalPaid = 0;
            let totalCapital = 0;
            let totalInterest = 0;
            let totalExpenses = 0;
            let totalBalance = 0;

            api.rows().every(function () {
                let d = this.data();

                totalAmount += parseNumber(d.amount);
                totalPaid += parseNumber(d.total_paid);
                totalCapital += parseNumber(d.total_capital);
                totalInterest += parseNumber(d.total_interest);
                totalExpenses += parseNumber(d.total_expenses);
                totalBalance += parseNumber(d.balance);
            });

            $('#total_amount').html('S/ ' + totalAmount.toFixed(2));
            $('#total_paid').html('S/ ' + totalPaid.toFixed(2));
            $('#total_capital').html('S/ ' + totalCapital.toFixed(2));
            $('#total_interest').html('S/ ' + totalInterest.toFixed(2));
            $('#total_expenses').html('S/ ' + totalExpenses.toFixed(2));
            $('#total_balance').html('S/ ' + totalBalance.toFixed(2));
        }


    });

    $(document).on('click', '.btn-kpi-detail', function () {

        let type = $(this).data('type');

        kpiFilter = type; // 👈 guardamos el filtro

        $('#tableAdvancedReport').DataTable().ajax.reload();

    });

    $('#btnAdvancedFilter').click(function () {
        table.ajax.reload();
        loadKpis(); // 👈 AGREGA ESTO
    });

});

function loadKpis() {

    $.get(window.routes.advancedKpis, {
        date_from: $('#adv_date_from').val(),
        date_to: $('#adv_date_to').val()
    }, function (res) {

        console.log(res);

        $('#adv_total_loans').text('S/ ' + parseFloat(res.total_loans).toFixed(2));
        $('#adv_total_paid').text('S/ ' + parseFloat(res.total_paid).toFixed(2));
        $('#adv_total_pending').text('S/ ' + parseFloat(res.total_pending).toFixed(2));
        $('#adv_total_overdue').text('S/ ' + parseFloat(res.total_overdue).toFixed(2));

    }).fail(function (err) {
        console.error("ERROR KPI:", err);
    });

}

$(function () {
    loadKpis(); // 👈 SOLO AQUÍ
});


function loadBranches() {
    $.get('/admin/reports/advanced/branches', function (res) {

        let select = $('#adv_branch');
        select.html('<option value="">Todas</option>');

        res.forEach(b => {
            select.append(`<option value="${b.id}">${b.name}</option>`);
        });

    });
}

$(function () {
    loadBranches(); // 👈 IMPORTANTE
});

$('#btnAdvancedPdf').click(function () {

    let params = $.param({
        date_from: $('#adv_date_from').val(),
        date_to: $('#adv_date_to').val(),
        branch_id: $('#adv_branch').val(),
        kpi_filter: kpiFilter
    });

    window.open('/admin/reports/advanced/pdf?' + params, '_blank');
});


$('#btnAdvancedExcel').click(function () {

    let params = $.param({
        date_from: $('#adv_date_from').val(),
        date_to: $('#adv_date_to').val(),
        branch_id: $('#adv_branch').val(),
        kpi_filter: kpiFilter
    });

    window.open('/admin/reports/advanced/excel?' + params, '_blank');
});