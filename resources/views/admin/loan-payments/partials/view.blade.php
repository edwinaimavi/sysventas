<!-- View Payment Modal -->
<div class="modal fade" id="viewPaymentModal" tabindex="-1" role="dialog" aria-labelledby="viewPaymentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg rounded">

            <!-- Header -->
            <div class="modal-header"
                style="background: linear-gradient(135deg,#f7f7f7,#ececec); border-bottom:1px solid #e0e0e0;">
                <h5 class="modal-title" id="viewPaymentModalLabel">
                    <i class="fas fa-eye text-secondary mr-2"></i> Información del Pago
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body bg-white">
                <div class="container-fluid">
                    <div class="row">

                        <!-- LEFT: Datos generales -->
                        <div class="col-md-4 text-center border-right">
                            <div class="mb-3">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light mb-2"
                                    style="width: 110px; height:110px; box-shadow:0 4px 12px rgba(0,0,0,.08);">
                                    <i class="fas fa-cash-register text-secondary" style="font-size: 42px;"></i>
                                </div>
                            </div>

                            <!-- Código de pago -->
                            <h5 id="vp_payment_code" class="font-weight-bold text-dark mb-1">
                                PAGO -
                            </h5>

                            <!-- Estado -->
                            <div class="mt-2">
                                <span id="vp_status_badge" class="badge badge-warning py-2 px-3">
                                    Pendiente
                                </span>
                            </div>

                            <hr class="my-3">

                            <!-- Préstamo -->
                            <div class="text-left mb-2">
                                <small class="text-muted d-block">Código del préstamo</small>
                                <div id="vp_loan_code" class="font-weight-600">—</div>
                            </div>

                            <!-- Cliente -->
                            <div class="text-left mb-2">
                                <small class="text-muted d-block">Cliente</small>
                                <div id="vp_client_name" class="font-weight-600">—</div>
                            </div>

                            <!-- Sucursal -->
                            <div class="text-left mb-2">
                                <small class="text-muted d-block">Sucursal</small>
                                <div id="vp_branch" class="font-weight-600">—</div>
                            </div>

                            <!-- Usuario -->
                            <div class="text-left mb-2">
                                <small class="text-muted d-block">Registrado por</small>
                                <div id="vp_user" class="font-weight-600">—</div>
                            </div>

                        </div>

                        <!-- RIGHT: detalles del pago -->
                        <div class="col-md-8">

                            <h6 class="text-secondary mb-2">Datos del pago</h6>

                            <div class="row">
                                <div class="col-sm-6 mb-2">
                                    <small class="text-muted d-block">Fecha de pago</small>
                                    <div id="vp_payment_date" class="font-weight-600">—</div>
                                </div>

                                <div class="col-sm-6 mb-2">
                                    <small class="text-muted d-block">Monto pagado</small>
                                    <div id="vp_amount" class="h5 font-weight-bold text-dark">S/ 0.00</div>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-sm-4 mb-2">
                                    <small class="text-muted d-block">Capital</small>
                                    <div id="vp_capital" class="font-weight-600">S/ 0.00</div>
                                </div>

                                <div class="col-sm-4 mb-2">
                                    <small class="text-muted d-block">Interés</small>
                                    <div id="vp_interest" class="font-weight-600">S/ 0.00</div>
                                </div>

                                <div class="col-sm-4 mb-2">
                                    <small class="text-muted d-block">Mora</small>
                                    <div id="vp_late_fee" class="font-weight-600">S/ 0.00</div>
                                </div>
                            </div>

                            <hr class="my-2">

                            <!-- Método y referencia -->
                            <div class="row">
                                <div class="col-sm-6 mb-2">
                                    <small class="text-muted d-block">Método</small>
                                    <div id="vp_method" class="font-weight-600">—</div>
                                </div>

                                <div class="col-sm-6 mb-2">
                                    <small class="text-muted d-block">Referencia</small>
                                    <div id="vp_reference" class="font-weight-600">—</div>
                                </div>
                            </div>

                            <!-- Comprobante -->
                            <div class="mb-2 mt-2">
                                <small class="text-muted d-block">Comprobante</small>
                                <div id="vp_receipt_file" class="font-weight-600">—</div>
                            </div>

                            <hr class="my-2">

                            <!-- Info adicional -->
                            <h6 class="text-secondary mb-2">Información adicional</h6>

                            <div class="mb-2">
                                <small class="text-muted d-block">Estado (texto)</small>
                                <div id="vp_status_text" class="font-weight-600">Pendiente</div>
                            </div>

                            <div class="mb-2">
                                <small class="text-muted d-block">Notas</small>
                                <div id="vp_notes" class="font-weight-600 text-muted" style="white-space:pre-line;">—
                                </div>
                            </div>

                        </div> <!-- /col-md-8 -->

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
