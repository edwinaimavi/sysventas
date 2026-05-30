<div class="modal fade" id="loanSimulatorModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-calculator mr-2"></i> Simulador de Préstamo
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Monto préstamo</label>
                        <input type="number" class="form-control" id="sim_amount" min="0" step="0.01"
                            value="400">
                    </div>

                    <div class="col-md-3">
                        <label>Interés % mensual</label>
                        <input type="number" class="form-control" id="sim_interest" min="0" step="0.01"
                            value="20">
                    </div>

                    <div class="col-md-3">
                        <label>N° cuotas</label>
                        <input type="number" class="form-control" id="sim_term" min="1" step="1"
                            value="6">
                    </div>

                    <div class="col-md-3">
                        <label>Fecha desembolso</label>
                        <input type="date" class="form-control" id="sim_date">
                    </div>
                </div>

                <div class="text-center mb-3">
                    <button class="btn btn-primary" id="btnGenerateSimulation">
                        <i class="fas fa-sync-alt mr-1"></i> Generar cronograma
                    </button>
                    <button class="btn btn-dark" id="btnPrintSimulation" disabled>
                        <i class="fas fa-print mr-1"></i> Imprimir
                    </button>
                </div>
                <div id="simulationSummary" class="d-none mb-4"
                    style="display:flex !important; gap:15px; flex-wrap:wrap;">

                    <!-- TOTAL -->
                    <div class="info-box shadow-sm flex-fill" style="min-width:220px;">

                        <span class="info-box-icon bg-info elevation-1">
                            <i class="fas fa-wallet"></i>
                        </span>

                        <div class="info-box-content">
                            <span class="info-box-text">Total a pagar</span>

                            <span class="info-box-number" id="sim_total_payable">
                                S/ 0.00
                            </span>
                        </div>
                    </div>

                    <!-- CUOTA -->
                    <div class="info-box shadow-sm flex-fill" style="min-width:220px;">

                        <span class="info-box-icon bg-success elevation-1">
                            <i class="fas fa-money-bill-wave"></i>
                        </span>

                        <div class="info-box-content">
                            <span class="info-box-text">Cuota mensual</span>

                            <span class="info-box-number" id="sim_monthly_payment">
                                S/ 0.00
                            </span>
                        </div>
                    </div>

                    <!-- INTERÉS -->
                    <div class="info-box shadow-sm flex-fill" style="min-width:220px;">

                        <span class="info-box-icon bg-warning elevation-1">
                            <i class="fas fa-percent"></i>
                        </span>

                        <div class="info-box-content">
                            <span class="info-box-text">Interés total</span>

                            <span class="info-box-number" id="sim_total_interest">
                                S/ 0.00
                            </span>
                        </div>
                    </div>

                </div>

                <div id="simulationPrintArea">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Capital</th>
                                    <th>Interés</th>
                                    <th>Cuota</th>
                                    <th>Saldo</th>
                                </tr>
                            </thead>
                            <tbody id="simulationTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
