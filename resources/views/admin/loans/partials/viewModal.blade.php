<!-- View Loan Modal (COMPACTO) -->
<div class="modal fade" id="viewLoanModal" tabindex="-1" role="dialog" aria-labelledby="viewLoanModalLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg rounded-lg">

            <!-- Header -->
            <div class="modal-header py-2"
                style="background: linear-gradient(135deg,#f7f7f7,#ececec); border-bottom:1px solid #e0e0e0;">
                <h5 class="modal-title font-weight-bold mb-0" id="viewLoanModalLabel" style="font-size:1.05rem;">
                    <i class="fas fa-eye text-secondary mr-2"></i> Información del Préstamo
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body bg-white px-3 py-2">
                <div class="container-fluid px-0">
                    <div class="row no-gutters">

                        <!-- LEFT -->
                        <div class="col-md-4 text-center border-right pr-3">

                            <div class="mb-2">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light"
                                    style="width:92px;height:92px;box-shadow:0 3px 10px rgba(0,0,0,.08);">
                                    <i class="fas fa-hand-holding-usd text-secondary" style="font-size:34px;"></i>
                                </div>
                            </div>

                            <div id="vl_loan_code" class="font-weight-bold text-dark mb-1" style="font-size:1.05rem;">
                                PRÉSTAMO -
                            </div>

                            <div class="mb-2">
                                <span id="vl_status_badge" class="badge badge-warning px-3 py-1"
                                    style="font-size:.85rem;">
                                    Pendiente
                                </span>
                            </div>

                            <div class="mb-2 d-none" id="vl_refinance_badge_wrap">
                                <span class="badge badge-danger px-3 py-1" style="font-size:.85rem;">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Refinanciado
                                </span>
                            </div>

                            <hr class="my-2">

                            <div class="text-left mb-2">
                                <small class="text-muted d-block">Cliente solicitante</small>
                                <div id="vl_client_name" class="font-weight-bold" style="font-size:.95rem;">—</div>
                            </div>

                            <div class="text-left mb-2">
                                <small class="text-muted d-block">Garante</small>
                                <div id="vl_guarantor_name" class="font-weight-bold" style="font-size:.95rem;">
                                    Sin garante
                                </div>
                            </div>

                            <div class="text-left mb-2">
                                <small class="text-muted d-block">Sucursal</small>
                                <div id="vl_branch" class="font-weight-bold" style="font-size:.95rem;">—</div>
                            </div>

                            <div class="text-left mb-1">
                                <small class="text-muted d-block">Registrado por</small>
                                <div id="vl_user" class="font-weight-bold" style="font-size:.95rem;">—</div>
                            </div>

                        </div>

                        <!-- RIGHT -->
                        <div class="col-md-8 pl-3">

                            <!-- RESUMEN FINANCIERO -->
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="text-secondary font-weight-bold" style="font-size:.95rem;">
                                    Resumen financiero
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-sm-6">
                                    <small class="text-muted">Monto solicitado</small>
                                    <div id="vl_amount" class="font-weight-bold" style="font-size:1.05rem;">S/ 0.00
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <small class="text-muted">Plazo</small>
                                    <div id="vl_term_months" class="font-weight-bold" style="font-size:1rem;">0 meses
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-sm-6">
                                    <small class="text-muted">Tasa de interés</small>
                                    <div id="vl_interest_rate" class="font-weight-bold" style="font-size:.95rem;">0.00 %
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <small class="text-muted">Fecha de desembolso</small>
                                    <div id="vl_disbursement_date" class="font-weight-bold" style="font-size:.95rem;">—
                                    </div>
                                </div>
                            </div>

                            <hr class="my-2">

                            <!-- CUOTAS -->
                            <div class="row mb-2">
                                <div class="col-sm-6">
                                    <small class="text-muted">Cuota mensual</small>
                                    <div id="vl_monthly_payment" class="font-weight-bold" style="font-size:1rem;">S/
                                        0.00</div>
                                </div>
                                <div class="col-sm-6">
                                    <small class="text-muted">Total a pagar</small>
                                    <div id="vl_total_payable" class="font-weight-bold" style="font-size:1rem;">S/ 0.00
                                    </div>
                                </div>
                            </div>

                            <hr class="my-2">

                            <!-- INFO ADICIONAL -->
                            <div class="text-secondary font-weight-bold mb-2" style="font-size:.95rem;">
                                Información adicional
                            </div>

                            <div class="row mb-2">
                                <div class="col-sm-4">
                                    <small class="text-muted">ID Préstamo</small>
                                    <div id="vl_id" class="font-weight-bold" style="font-size:.9rem;">—</div>
                                </div>
                                <div class="col-sm-4">
                                    <small class="text-muted">Fecha registro</small>
                                    <div id="vl_created_at" class="font-weight-bold" style="font-size:.9rem;">—</div>
                                </div>
                                <div class="col-sm-4">
                                    <small class="text-muted">Estado</small>
                                    <div id="vl_status_text" class="font-weight-bold" style="font-size:.9rem;">—
                                    </div>
                                </div>
                            </div>

                            <div class="mb-2">
                                <small class="text-muted">Notas internas</small>
                                <div id="vl_notes" class="text-muted"
                                    style="white-space:pre-line; font-size:.9rem;">—</div>
                            </div>

                            <!-- INCREMENTOS -->
                            <hr class="my-2">
                            <div id="vl_increments_section" class="d-none">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="text-secondary font-weight-bold" style="font-size:.95rem;">
                                        <i class="fas fa-plus-circle mr-1"></i> Incrementos
                                    </div>
                                    <small class="text-muted" id="vl_increments_summary"></small>
                                </div>
                                <div id="vl_increments_list"
                                    style="max-height:200px;overflow-y:auto; padding-right:4px;"></div>
                            </div>

                            <!-- REFINANCIAMIENTOS -->
                            <hr class="my-2">
                            <div id="vl_refinances_section" class="d-none">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="text-secondary font-weight-bold" style="font-size:.95rem;">
                                        <i class="fas fa-sync-alt mr-1"></i> Refinanciamientos
                                    </div>
                                    <small class="text-muted" id="vl_refinances_summary"></small>
                                </div>
                                <div id="vl_refinances_list"
                                    style="max-height:200px;overflow-y:auto; padding-right:4px;"></div>
                            </div>

                            <!-- DESEMBOLSOS -->
                            <hr class="my-2">
                            <div id="vl_disbursements_section" class="d-none">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="text-secondary font-weight-bold" style="font-size:.95rem;">
                                        <i class="fas fa-money-check-alt mr-1"></i> Desembolsos
                                    </div>
                                    <small class="text-muted" id="vl_disbursements_summary"></small>
                                </div>
                                <div id="vl_disbursements_list"
                                    style="max-height:240px;overflow-y:auto; padding-right:4px;"></div>
                            </div>


                            <!-- CRONOGRAMA -->
                            <hr class="my-2">
                            <div id="vl_schedule_section" class="d-none">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="text-secondary font-weight-bold" style="font-size:.95rem;">
                                        <i class="fas fa-table mr-1"></i> Cronograma de pagos
                                    </div>
                                    <small class="text-muted" id="vl_schedule_summary"></small>
                                </div>

                                <div class="table-responsive" style="max-height:260px; overflow:auto;">
                                    <table class="table table-sm table-bordered mb-0" style="font-size:.85rem;">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center">MES</th>
                                                <th>VENC.</th>
                                                <th class="text-right">CAPITAL</th>
                                                <th class="text-right">INTERÉS</th>
                                                <th class="text-right">AMORT.</th>
                                                <th class="text-right">CUOTA</th>
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
    </div>
</div>
