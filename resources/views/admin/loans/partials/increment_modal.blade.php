<!-- Modal Incrementar Préstamo -->
<div class="modal fade" id="incrementModal" tabindex="-1" role="dialog" aria-labelledby="incrementModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg rounded">

            <div class="modal-header" style="background: linear-gradient(135deg,#f7f7f7,#ececec);">
                <h5 class="modal-title" id="incrementModalLabel">
                    <i class="fas fa-plus-circle text-warning mr-2"></i>
                    Incrementar préstamo
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" style="background:#f9fafb;">
                <form id="incrementForm">
                    @csrf
                    <input type="hidden" id="inc_loan_id" name="loan_id">

                    <div class="mb-3">
                        <small class="text-muted d-block">Préstamo</small>
                        <div id="inc_loan_code" class="font-weight-bold">—</div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block">Cliente</small>
                        <div id="inc_client_name" class="font-weight-600">—</div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block">Monto actual del préstamo</small>
                        <div id="inc_current_amount" class="font-weight-bold text-dark">S/ 0.00</div>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label for="increment_amount" class="small font-weight-bold text-secondary">
                            MONTO A INCREMENTAR (S/) <span class="text-danger">*</span>
                        </label>
                        <input type="number" step="0.01" min="0.01"
                               class="form-control form-control-sm"
                               id="increment_amount" name="increment_amount"
                               placeholder="Ej. 100.00">
                        <span class="invalid-feedback" id="increment_amount-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="inc_notes" class="small font-weight-bold text-secondary">
                            MOTIVO / NOTAS
                        </label>
                        <textarea class="form-control form-control-sm" id="inc_notes" name="notes"
                                  rows="3" placeholder="Ej: Ampliación solicitada por el cliente el día X."></textarea>
                        <span class="invalid-feedback" id="notes-error"></span>
                    </div>

                    <div class="alert alert-light border small" id="inc_new_amount_box" style="display:none;">
                        <div class="d-flex justify-content-between">
                            <span>Monto nuevo del préstamo:</span>
                            <strong id="inc_new_amount_text">S/ 0.00</strong>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="button" class="btn btn-light border mr-2"
                                data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-warning text-white" id="btnSaveIncrement">
                            <i class="fas fa-save mr-1"></i> Guardar incremento
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>
