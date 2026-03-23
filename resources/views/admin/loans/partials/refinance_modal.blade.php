<div class="modal fade" id="refinanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">

            <div class="modal-header"
                style="background: linear-gradient(135deg,#f7f7f7,#ececec); border-bottom:1px solid #e0e0e0;">
                <h5 class="modal-title fw-semibold">
                    <i class="fas fa-sync-alt"></i> Refinanciar préstamo
                    <span class="badge bg-dark ms-2" id="rf_loan_code">—</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body bg-light">
                <form id="refinanceForm">
                    @csrf
                    <input type="hidden" id="rf_loan_id" name="loan_id">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Fecha refinanciamiento</label>
                            <input type="date" class="form-control" name="refinance_date" id="rf_refinance_date">
                            <small class="text-danger" id="refinance_date-error"></small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Nuevo plazo (meses)</label>
                            <input type="number" class="form-control" name="new_term_months" id="rf_new_term_months"
                                min="1" value="1">
                            <small class="text-danger" id="new_term_months-error"></small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Nueva fecha vencimiento</label>
                            <input type="date" class="form-control" name="new_due_date" id="rf_new_due_date">
                            <small class="text-danger" id="new_due_date-error"></small>

                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Saldo a refinanciar (base)</label>
                            <input type="number" step="0.01" class="form-control" name="base_balance"
                                id="rf_base_balance" readonly>

                            <small class="text-muted">Sugerido: saldo pendiente actual</small>
                            <small class="text-danger d-block" id="base_balance-error"></small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Interés % (admin)</label>
                            <input type="number" step="0.01" class="form-control" name="interest_rate"
                                id="rf_interest_rate" value="20">
                            <small class="text-danger" id="interest_rate-error"></small>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small fw-semibold">Notas (opcional)</label>
                            <textarea class="form-control" rows="2" name="notes" id="rf_notes"></textarea>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <div><strong>Saldo pendiente actual:</strong> <span id="rf_remaining">—</span></div>
                        <div><strong>Vence:</strong> <span id="rf_due_date">—</span> <span id="rf_overdue_badge"></span>
                        </div>
                    </div>

                </form>
            </div>

            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-light border" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
                <button type="button" class="btn btn-dark" id="btnSaveRefinance">
                    <i class="fas fa-save"></i> Guardar refinanciamiento
                </button>
            </div>

        </div>
    </div>
</div>
