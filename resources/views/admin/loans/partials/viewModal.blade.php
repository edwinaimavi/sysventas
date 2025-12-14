<!-- View Loan Modal -->
<div class="modal fade" id="viewLoanModal" tabindex="-1" role="dialog" aria-labelledby="viewLoanModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg rounded">

            <!-- Header -->
            <div class="modal-header"
                style="background: linear-gradient(135deg,#f7f7f7,#ececec); border-bottom:1px solid #e0e0e0;">
                <h5 class="modal-title" id="viewLoanModalLabel">
                    <i class="fas fa-eye text-secondary mr-2"></i> Información del Préstamo
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body bg-white">
                <div class="container-fluid">
                    <div class="row">

                        <!-- LEFT: resumen principal -->
                        <div class="col-md-4 text-center border-right">
                            <div class="mb-3">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light mb-2"
                                    style="width: 110px; height:110px; box-shadow:0 4px 12px rgba(0,0,0,.08);">
                                    <i class="fas fa-hand-holding-usd text-secondary" style="font-size: 42px;"></i>
                                </div>
                            </div>

                            <!-- Código de préstamo -->
                            <h5 id="vl_loan_code" class="font-weight-bold text-dark mb-1">
                                PRÉSTAMO -
                            </h5>

                            <!-- Estado -->
                            <div class="mt-2">
                                <span id="vl_status_badge" class="badge badge-warning py-2 px-3">
                                    Pendiente
                                </span>
                            </div>

                            <hr class="my-3">

                            <!-- Cliente -->
                            <div class="text-left mb-2">
                                <small class="text-muted d-block">Cliente solicitante</small>
                                <div id="vl_client_name" class="font-weight-600">
                                    —
                                </div>
                            </div>

                            <!-- Garante -->
                            <div class="text-left mb-2">
                                <small class="text-muted d-block">Garante</small>
                                <div id="vl_guarantor_name" class="font-weight-600">
                                    Sin garante
                                </div>
                            </div>

                            <!-- Sucursal -->
                            <div class="text-left mb-2">
                                <small class="text-muted d-block">Sucursal</small>
                                <div id="vl_branch" class="font-weight-600">
                                    —
                                </div>
                            </div>

                            <!-- Usuario que registra -->
                            <div class="text-left mb-2">
                                <small class="text-muted d-block">Registrado por</small>
                                <div id="vl_user" class="font-weight-600">
                                    —
                                </div>
                            </div>

                        </div> <!-- /col-md-4 -->

                        <!-- RIGHT: detalles financieros + desembolsos -->
                        <div class="col-md-8">

                            <!-- Bloque: resumen financiero -->
                            <div class="row mb-2">
                                <div class="col-12">
                                    <h6 class="text-secondary mb-2">
                                        Resumen financiero
                                    </h6>

                                    <div class="row">
                                        <div class="col-sm-6 mb-2">
                                            <small class="text-muted d-block">Monto solicitado</small>
                                            <div id="vl_amount" class="h5 mb-0 font-weight-bold text-dark">
                                                S/ 0.00
                                            </div>
                                        </div>

                                        <div class="col-sm-6 mb-2">
                                            <small class="text-muted d-block">Plazo</small>
                                            <div id="vl_term_months" class="h6 mb-0 font-weight-600">
                                                0 meses
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-sm-6 mb-2">
                                            <small class="text-muted d-block">Tasa de interés anual</small>
                                            <div id="vl_interest_rate" class="font-weight-600">
                                                0.00 %
                                            </div>
                                        </div>

                                        <div class="col-sm-6 mb-2">
                                            <small class="text-muted d-block">Fecha de desembolso</small>
                                            <div id="vl_disbursement_date" class="font-weight-600">
                                                —
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-2">

                            <!-- Bloque: cuotas -->
                            <div class="row mb-2">
                                <div class="col-sm-6 mb-2">
                                    <small class="text-muted d-block">Cuota mensual</small>
                                    <div id="vl_monthly_payment" class="h6 mb-0 font-weight-bold text-dark">
                                        S/ 0.00
                                    </div>
                                </div>

                                <div class="col-sm-6 mb-2">
                                    <small class="text-muted d-block">Total a pagar</small>
                                    <div id="vl_total_payable" class="h6 mb-0 font-weight-bold text-dark">
                                        S/ 0.00
                                    </div>
                                </div>
                            </div>

                            <hr class="my-2">

                            <!-- Bloque: info adicional -->
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-secondary mb-2">Información adicional</h6>

                                    <div class="mb-2">
                                        <small class="text-muted d-block">ID Préstamo</small>
                                        <div id="vl_id" class="font-weight-600">—</div>
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted d-block">Fecha de registro</small>
                                        <div id="vl_created_at" class="font-weight-600">—</div>
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted d-block">Estado (texto)</small>
                                        <div id="vl_status_text" class="font-weight-600">Pendiente</div>
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted d-block">Notas internas</small>
                                        <div id="vl_notes" class="font-weight-600 text-muted"
                                            style="white-space:pre-line;">
                                            —
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- ========================= -->
                            <!-- BLOQUE: INCREMENTOS       -->
                            <!-- ========================= -->
                            <hr class="my-3">

                            <div id="vl_increments_section" class="mt-1 d-none">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="text-secondary mb-0">
                                        <i class="fas fa-plus-circle mr-1"></i> Historial de incrementos
                                    </h6>
                                    <small class="text-muted" id="vl_increments_summary"></small>
                                </div>

                                <!-- Contenedor scroll para las tarjetas de incrementos -->
                                <div id="vl_increments_list"
                                    style="max-height:220px; overflow-y:auto; padding-right:4px;">
                                    <!-- El JS insertará aquí las cards de cada incremento -->
                                </div>
                            </div>


                            <!-- ========================= -->
                            <!-- BLOQUE: DESEMBOLSOS       -->
                            <!-- ========================= -->
                            <hr class="my-3">

                            <div id="vl_disbursements_section" class="mt-1 d-none">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="text-secondary mb-0">
                                        <i class="fas fa-money-check-alt mr-1"></i> Desembolsos realizados
                                    </h6>
                                    <small class="text-muted" id="vl_disbursements_summary"></small>
                                </div>

                                <!-- Contenedor scroll para las tarjetas de desembolso -->
                                <div id="vl_disbursements_list"
                                    style="max-height:260px; overflow-y:auto; padding-right:4px;">
                                    <!-- El JS inserta aquí las cards de cada desembolso -->
                                </div>
                            </div>

                        </div> <!-- /col-md-8 -->

                    </div> <!-- /row -->
                </div> <!-- /container-fluid -->
            </div> <!-- /modal-body -->

        </div>
    </div>
</div>
