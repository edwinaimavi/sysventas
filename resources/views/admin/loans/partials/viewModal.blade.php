<!-- =========================================
     VIEW LOAN MODAL PRO
========================================= -->
<div class="modal fade" id="viewLoanModal" tabindex="-1" role="dialog" aria-labelledby="viewLoanModalLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">

        <div class="modal-content border-0 shadow-lg rounded-lg overflow-hidden">

            <!-- HEADER -->
            <div class="modal-header py-3 px-4" style="background:linear-gradient(135deg,#1f2937,#111827);">

                <div class="d-flex align-items-center">

                    <div class="rounded-circle bg-white d-flex align-items-center justify-content-center mr-3"
                        style="width:50px;height:50px;">

                        <i class="fas fa-file-invoice-dollar text-dark" style="font-size:20px;"></i>
                    </div>

                    <div>
                        <h5 class="modal-title text-white font-weight-bold mb-0" id="viewLoanModalLabel">
                            Información del Préstamo
                        </h5>

                        <small class="text-light">
                            Detalles financieros y cronograma
                        </small>
                    </div>

                </div>

                <button type="button" class="close text-white opacity-100" data-dismiss="modal">
                    <span>&times;</span>
                </button>

            </div>

            <!-- BODY -->
            <div class="modal-body bg-light px-4 py-3">

                <div class="container-fluid px-0">

                    <div class="row">

                        <!-- =========================================
                             LEFT PANEL
                        ========================================= -->
                        <div class="col-lg-4 mb-3">

                            <div class="card border-0 shadow-sm h-100">

                                <div class="card-body text-center">

                                    <!-- ICON -->
                                    <div class="mb-3">

                                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle"
                                            style="
                                                width:100px;
                                                height:100px;
                                                background:linear-gradient(135deg,#111827,#374151);
                                                box-shadow:0 8px 25px rgba(0,0,0,.15);
                                            ">

                                            <i class="fas fa-hand-holding-usd text-white" style="font-size:38px;"></i>

                                        </div>

                                    </div>

                                    <!-- CODE -->
                                    <div id="vl_loan_code" class="font-weight-bold text-dark mb-2"
                                        style="font-size:1.2rem;">
                                        PRÉSTAMO -
                                    </div>

                                    <!-- STATUS -->
                                    <div class="mb-2">
                                        <span id="vl_status_badge" class="badge badge-warning px-4 py-2 shadow-sm"
                                            style="font-size:.85rem;">
                                            Pendiente
                                        </span>
                                    </div>

                                    <!-- REFINANCE -->
                                    <div class="mb-3 d-none" id="vl_refinance_badge_wrap">

                                        <span class="badge badge-danger px-3 py-2 shadow-sm" style="font-size:.82rem;">

                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Refinanciado

                                        </span>

                                    </div>

                                    <hr>

                                    <!-- CLIENT -->
                                    <div class="text-left mb-3">

                                        <small class="text-muted d-block">
                                            Cliente solicitante
                                        </small>

                                        <div id="vl_client_name" class="font-weight-bold text-dark"
                                            style="font-size:1rem;">
                                            —
                                        </div>

                                    </div>

                                    <!-- GUARANTOR -->
                                    <div class="text-left mb-3">

                                        <small class="text-muted d-block">
                                            Garante
                                        </small>

                                        <div id="vl_guarantor_name" class="font-weight-bold" style="font-size:.95rem;">

                                            Sin garante

                                        </div>

                                    </div>

                                    <!-- BRANCH -->
                                    <div class="text-left mb-3">

                                        <small class="text-muted d-block">
                                            Sucursal
                                        </small>

                                        <div id="vl_branch" class="font-weight-bold" style="font-size:.95rem;">

                                            —

                                        </div>

                                    </div>

                                    <!-- USER -->
                                    <div class="text-left">

                                        <small class="text-muted d-block">
                                            Registrado por
                                        </small>

                                        <div id="vl_user" class="font-weight-bold" style="font-size:.95rem;">

                                            —

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- =========================================
                             RIGHT PANEL
                        ========================================= -->
                        <div class="col-lg-8">

                            <!-- RESUMEN -->
                            <div class="card border-0 shadow-sm mb-3">

                                <div class="card-header bg-white border-0">

                                    <div class="font-weight-bold text-dark">
                                        <i class="fas fa-chart-line mr-2 text-primary"></i>
                                        Resumen Financiero
                                    </div>

                                </div>

                                <div class="card-body">

                                    <div class="row">

                                        <div class="col-md-6 mb-3">

                                            <small class="text-muted">
                                                Monto solicitado
                                            </small>

                                            <div id="vl_amount" class="font-weight-bold text-success"
                                                style="font-size:1.2rem;">

                                                S/ 0.00

                                            </div>

                                        </div>

                                        <div class="col-md-6 mb-3">

                                            <small class="text-muted">
                                                Plazo
                                            </small>

                                            <div id="vl_term_months" class="font-weight-bold" style="font-size:1rem;">

                                                0 meses

                                            </div>

                                        </div>

                                        <div class="col-md-6 mb-3">

                                            <small class="text-muted">
                                                Tasa de interés
                                            </small>

                                            <div id="vl_interest_rate" class="font-weight-bold">

                                                0%

                                            </div>

                                        </div>

                                        <div class="col-md-6 mb-3">

                                            <small class="text-muted">
                                                Fecha desembolso
                                            </small>

                                            <div id="vl_disbursement_date" class="font-weight-bold">

                                                —

                                            </div>

                                        </div>

                                        <div class="col-md-6 mb-2">

                                            <small class="text-muted">
                                                Cuota mensual
                                            </small>

                                            <div id="vl_monthly_payment" class="font-weight-bold text-primary"
                                                style="font-size:1rem;">

                                                S/ 0.00

                                            </div>

                                        </div>

                                        <div class="col-md-6 mb-2">

                                            <small class="text-muted">
                                                Total a pagar
                                            </small>

                                            <div id="vl_total_payable" class="font-weight-bold text-danger"
                                                style="font-size:1rem;">

                                                S/ 0.00

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <!-- INFO -->
                            <div class="card border-0 shadow-sm mb-3">

                                <div class="card-header bg-white border-0">

                                    <div class="font-weight-bold text-dark">
                                        <i class="fas fa-info-circle mr-2 text-secondary"></i>
                                        Información adicional
                                    </div>

                                </div>

                                <div class="card-body">

                                    <div class="row">

                                        <div class="col-md-4 mb-3">

                                            <small class="text-muted">
                                                ID Préstamo
                                            </small>

                                            <div id="vl_id" class="font-weight-bold">
                                                —
                                            </div>

                                        </div>

                                        <div class="col-md-4 mb-3">

                                            <small class="text-muted">
                                                Fecha registro
                                            </small>

                                            <div id="vl_created_at" class="font-weight-bold">
                                                —
                                            </div>

                                        </div>

                                        <div class="col-md-4 mb-3">

                                            <small class="text-muted">
                                                Estado
                                            </small>

                                            <div id="vl_status_text" class="font-weight-bold">
                                                —
                                            </div>

                                        </div>

                                    </div>

                                    <div>

                                        <small class="text-muted">
                                            Notas internas
                                        </small>

                                        <div id="vl_notes" class="text-muted mt-1" style="white-space:pre-line;">

                                            —

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <!-- CRONOGRAMA -->
                            <div id="vl_schedule_section" class="card border-0 shadow-sm d-none">

                                <div
                                    class="card-header bg-white border-0 d-flex justify-content-between align-items-center">

                                    <div class="font-weight-bold text-dark">

                                        <i class="fas fa-table mr-2 text-success"></i>
                                        Cronograma de pagos

                                    </div>

                                    <small id="vl_schedule_summary" class="text-muted">
                                    </small>

                                </div>

                                <div class="card-body p-0">

                                    <div class="table-responsive" style="max-height:350px;overflow:auto;">

                                        <table class="table table-hover table-bordered mb-0">

                                            <thead class="bg-dark text-white">

                                                <tr>

                                                    <th class="text-center">
                                                        MES
                                                    </th>

                                                    <th>
                                                        VENC.
                                                    </th>

                                                    <th class="text-right">
                                                        CAPITAL
                                                    </th>

                                                    <th class="text-right">
                                                        INTERÉS
                                                    </th>

                                                    <th class="text-right">
                                                        AMORT.
                                                    </th>

                                                    <th class="text-right">
                                                        CUOTA
                                                    </th>

                                                </tr>

                                            </thead>

                                            <tbody id="vl_schedule_tbody"></tbody>

                                        </table>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- FOOTER -->
            <div class="modal-footer bg-white">

                <a href="#" id="btnPrintLoan" target="_blank" class="btn btn-danger">

                    <i class="fas fa-file-pdf mr-1"></i>
                    Imprimir PDF

                </a>

                <button type="button" class="btn btn-secondary" data-dismiss="modal">

                    Cerrar

                </button>

            </div>

        </div>

    </div>

</div>
