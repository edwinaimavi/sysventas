<!-- Modal Detalle de Caja -->
<div class="modal fade" id="cashDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0 rounded-lg overflow-hidden">

            <!-- HEADER -->
            <div class="modal-header"
                style="background: linear-gradient(90deg,#ffffff,#f3f6f8); border-bottom:1px solid #e6eaee;">

                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-light mr-3">
                        <i class="fas fa-eye text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0">
                            Detalle de Caja
                        </h5>
                        <small class="text-muted">
                            Movimientos detallados de ingresos y egresos
                        </small>
                    </div>
                </div>

                <button type="button" class="close ml-3" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <!-- BODY -->
            <div class="modal-body" style="background:#f8fbfc;">

                <!-- RESUMEN -->
                <div class="row mb-3">

                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 text-center">
                            <div class="card-body">
                                <small class="text-muted">Saldo Inicial</small>
                                <h5 class="text-dark" id="detail_opening">S/ 0.00</h5>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 text-center">
                            <div class="card-body">
                                <small class="text-muted">Ingresos</small>
                                <h5 class="text-success" id="detail_income">S/ 0.00</h5>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 text-center">
                            <div class="card-body">
                                <small class="text-muted">Egresos</small>
                                <h5 class="text-danger" id="detail_expense">S/ 0.00</h5>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 text-center">
                            <div class="card-body">
                                <small class="text-muted">Saldo Final</small>
                                <h5 class="text-primary font-weight-bold" id="detail_balance">S/ 0.00</h5>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- TABLA DE MOVIMIENTOS -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-2">

                        <div class="table-responsive">
                            <table class="table table-sm table-hover text-center mb-0">
                                <thead style="background:#f1f1f1;">
                                    <tr>
                                        <th>#</th>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Concepto</th>
                                        <th>Monto</th>
                                        <th>Usuario</th>
                                        <th>Observación</th>
                                    </tr>
                                </thead>
                                <tbody id="cashDetailTable">
                                    <!-- JS llena esto -->
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <div class="d-flex justify-content-end mb-2">

                    <button class="btn btn-sm btn-secondary mr-2" id="btnPrintCashDetail">
                        <i class="fas fa-print"></i> Imprimir
                    </button>

                    <button class="btn btn-sm btn-danger" id="btnPdfCashDetail">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>

                </div>

            </div>

        </div>
    </div>
</div>
