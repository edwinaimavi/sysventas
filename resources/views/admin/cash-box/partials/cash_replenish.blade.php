<!-- Modal Reposición de Caja -->
<div class="modal fade" id="cashReplenishModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0 rounded-lg overflow-hidden">

            <!-- HEADER -->
            <div class="modal-header"
                style="background: linear-gradient(90deg,#ffffff,#f3f6f8); border-bottom:1px solid #e6eaee;">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-light mr-3">
                        <i class="fas fa-coins text-success"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0">
                            Reposición de Caja
                        </h5>
                        <small class="text-muted">
                            Ingreso adicional de efectivo
                        </small>
                    </div>
                </div>

                <button type="button" class="close ml-3" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <!-- BODY -->
            <div class="modal-body" style="background:#f8fbfc;">
                <form id="cashReplenishForm" class="row">

                    @csrf

                    <input type="hidden" id="replenish_cash_id" name="cash_box_id">

                    <!-- Información lateral -->
                    <div class="col-lg-4 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">

                                <small class="text-muted d-block mb-1">Tipo de movimiento</small>
                                <div class="font-weight-bold text-success mb-3">
                                    Ingreso de capital
                                </div>

                                <small class="text-muted d-block mb-1">Fecha y hora</small>
                                <div class="font-weight-bold">
                                    {{ now()->format('d/m/Y H:i') }}
                                </div>

                                <hr>

                                <span class="badge badge-success px-3 py-2">
                                    <i class="fas fa-arrow-up mr-1"></i> INGRESO
                                </span>

                            </div>
                        </div>
                    </div>

                    <!-- Formulario -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">

                                <div class="form-group">
                                    <label class="small font-weight-bold text-secondary">
                                        MONTO A INGRESAR (S/) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number"
                                        step="0.01"
                                        class="form-control form-control-sm"
                                        name="amount"
                                        id="replenish_amount"
                                        placeholder="0.00">
                                    <span class="invalid-feedback" id="replenish_amount-error"></span>
                                </div>

                                <div class="form-group">
                                    <label class="small font-weight-bold text-secondary">
                                        OBSERVACIÓN
                                    </label>
                                    <textarea class="form-control form-control-sm"
                                        name="notes"
                                        rows="3"
                                        placeholder="Detalle del ingreso (ej: dinero enviado desde oficina central)"></textarea>
                                </div>

                                <div class="text-right mt-3">
                                    <button type="button"
                                        class="btn btn-light border mr-2"
                                        data-dismiss="modal">
                                        Cancelar
                                    </button>

                                    <button type="submit"
                                        class="btn btn-success">
                                        <i class="fas fa-plus-circle mr-1"></i>
                                        Registrar ingreso
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>
