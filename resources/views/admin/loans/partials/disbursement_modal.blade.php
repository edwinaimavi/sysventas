<!-- Modal elegante para Desembolso de Préstamo -->
<div class="modal fade" id="disbursementModal" tabindex="-1" role="dialog" aria-labelledby="disbursementModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0 rounded-lg overflow-hidden">

            <!-- HEADER -->
            <div class="modal-header align-items-center"
                style="background: linear-gradient(90deg,#ffffff,#f3f6f8); border-bottom:1px solid #e6eaee;">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-light mr-3">
                        <i class="fas fa-money-bill-wave text-success"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="disbursementModalLabel">Nuevo Desembolso</h5>
                        <small class="text-muted">
                            Registro de desembolso del préstamo
                            <span id="disb_loan_code_badge" class="badge badge-light ml-1">—</span>
                        </small>
                    </div>
                </div>

                <button type="button" class="close ml-3" data-dismiss="modal" aria-label="Close" style="opacity:.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- BODY -->
            <div class="modal-body p-3" style="background:#f8fbfc;">
                <form id="disbursementForm" enctype="multipart/form-data" autocomplete="off" class="row">
                    @csrf

                    {{-- IDs ocultos --}}
                    <input type="hidden" id="disb_loan_id" name="loan_id">
                    <input type="hidden" id="disb_branch_id" name="branch_id" value="1">
                    <input type="hidden" id="disb_user_id" name="user_id" value="1">

                    <!-- LEFT: info préstamo -->
                    <div class="col-lg-4 mb-3">
                        <div class="card border-0 rounded-lg shadow-sm h-100">
                            <div class="card-body py-4">
                                <h6 class="text-secondary mb-2">Resumen del préstamo</h6>

                                <small class="text-muted d-block mb-1">Préstamo</small>
                                <div id="disb_loan_code" class="font-weight-600 mb-2">—</div>

                                <small class="text-muted d-block mb-1">Cliente</small>
                                <div id="disb_client_name" class="font-weight-600 mb-2">—</div>

                                <div class="mb-2">
                                    <small class="text-muted d-block">Monto actual del préstamo</small>
                                    <div id="disb_loan_amount" class="font-weight-bold text-dark">S/ 0.00</div>
                                </div>

                                {{-- <small class="text-muted d-block mb-1">Monto del préstamo</small>
                                <div id="disb_loan_amount" class="font-weight-600 mb-2">S/ 0.00</div> --}}

                                <div class="mb-2">
                                    <small class="text-muted d-block">Total desembolsado</small>
                                    <div id="disb_total_disbursed" class="font-weight-600">S/ 0.00</div>
                                </div>

                                <div class="mb-2">
                                    <small class="text-muted d-block">Saldo pendiente por desembolsar</small>
                                    <div id="disb_remaining_amount" class="font-weight-600 text-primary">S/ 0.00</div>
                                </div>

                                <hr>

                                <div class="text-left mt-2">
                                    <small class="text-muted d-block">Estado del desembolso</small>
                                    <select id="disb_status" name="status" class="form-control form-control-sm mt-1">
                                        <option value="completed" selected>Completado</option>
                                        <option value="pending">Pendiente</option>
                                        <option value="reversed">Revertido</option>
                                    </select>
                                    <span class="invalid-feedback" id="status-error"></span>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: datos del desembolso -->
                    <div class="col-lg-8">
                        <div class="card border-0 rounded-lg shadow-sm">
                            <div class="card-body">

                                {{-- fila 1: fecha + monto --}}
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="disbursement_date" class="small font-weight-bold text-secondary">
                                            FECHA DE DESEMBOLSO <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control form-control-sm"
                                            id="disbursement_date" name="disbursement_date">
                                        <span class="invalid-feedback" id="disbursement_date-error"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="disb_amount" class="small font-weight-bold text-secondary">
                                            MONTO A DESEMBOLSAR (S/) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" step="0.01" class="form-control form-control-sm"
                                            id="disb_amount" name="amount" placeholder="0.00">
                                        <span class="invalid-feedback" id="amount-error"></span>
                                    </div>
                                </div>

                                {{-- fila 2: método + referencia --}}
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="method" class="small font-weight-bold text-secondary">
                                            MÉTODO DE PAGO
                                        </label>
                                        <select id="method" name="method" class="form-control form-control-sm">
                                            <option value="">Seleccione</option>
                                            <option value="efectivo">Efectivo</option>
                                            <option value="yape">Yape / Plin</option>
                                            <option value="transferencia">Transferencia</option>
                                            <option value="cheque">Cheque</option>
                                        </select>
                                        <span class="invalid-feedback" id="method-error"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="reference" class="small font-weight-bold text-secondary">
                                            REFERENCIA / N° OPERACIÓN
                                        </label>
                                        <input type="text" class="form-control form-control-sm" id="reference"
                                            name="reference" placeholder="N° operación, cuenta, etc.">
                                        <span class="invalid-feedback" id="reference-error"></span>
                                    </div>
                                </div>

                                {{-- fila 3: comprobante --}}
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="receipt_number" class="small font-weight-bold text-secondary">
                                            NÚMERO DE COMPROBANTE
                                        </label>
                                        <input type="text" class="form-control form-control-sm"
                                            id="receipt_number" name="receipt_number"
                                            placeholder="Boleta, factura, voucher, etc.">
                                        <span class="invalid-feedback" id="receipt_number-error"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="receipt_file"
                                            class="small font-weight-bold text-secondary d-flex align-items-center">
                                            ARCHIVO DEL COMPROBANTE
                                            <span class="badge badge-light border ml-2">
                                                <i class="fas fa-paperclip mr-1"></i> PDF / Imagen
                                            </span>
                                        </label>

                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="fas fa-upload"></i>
                                                </span>
                                            </div>

                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="receipt_file"
                                                    name="receipt_file" accept="image/*,application/pdf">
                                                <label class="custom-file-label" for="receipt_file">
                                                    Seleccionar archivo...
                                                </label>
                                            </div>
                                        </div>

                                        <small class="form-text text-muted mt-1">
                                            Tamaño máximo 4 MB. Formatos permitidos: PDF, JPG, JPEG, PNG.
                                        </small>

                                        <span class="invalid-feedback d-block" id="receipt_file-error"></span>
                                    </div>
                                </div>

                                {{-- fila 4: notas --}}
                                <div class="form-row">
                                    <div class="form-group col-md-12">
                                        <label for="disb_notes" class="small font-weight-bold text-secondary">
                                            NOTAS
                                        </label>
                                        <textarea class="form-control form-control-sm" id="disb_notes" name="notes" rows="3"
                                            placeholder="Notas sobre este desembolso"></textarea>
                                        <span class="invalid-feedback" id="notes-error"></span>
                                    </div>
                                </div>

                                {{-- acciones --}}
                                <div class="form-row mt-3">
                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="button" class="btn btn-light border mr-2"
                                            data-dismiss="modal">
                                            <i class="fas fa-times mr-1"></i> Cerrar
                                        </button>
                                        <button type="submit" class="btn btn-success" id="btnSaveDisbursement">
                                            <i class="fas fa-save mr-1"></i> Guardar Desembolso
                                        </button>
                                    </div>
                                </div>

                            </div> <!-- card-body -->
                        </div> <!-- card -->
                    </div> <!-- col-lg-8 -->

                </form>
            </div> <!-- modal-body -->

        </div>
    </div>
</div>
