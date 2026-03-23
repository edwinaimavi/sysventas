@php
    use App\Models\Branch;
    use Illuminate\Support\Facades\Auth;

    $currentBranch = Branch::find(session('branch_id'));
    $currentBranchName = $currentBranch->name ?? 'Sin sucursal seleccionada';

    $currentUser = Auth::user();
    $currentUserName = $currentUser->name ?? 'Usuario';
@endphp

<!-- Modal elegante para Cierre de Caja -->
<div class="modal fade" id="cashCloseModal" tabindex="-1" role="dialog"
    aria-labelledby="cashCloseModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0 rounded-lg overflow-hidden">

            <!-- HEADER -->
            <div class="modal-header align-items-center"
                style="background: linear-gradient(90deg,#ffffff,#f3f6f8); border-bottom:1px solid #e6eaee;">

                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-light mr-3">
                        <i class="fas fa-lock text-secondary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="cashCloseModalLabel">
                            Cierre de Caja
                        </h5>
                        <small class="text-muted">
                            Verificación final del efectivo en caja
                        </small>
                    </div>
                </div>

                <button type="button" class="close ml-3" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <!-- BODY -->
            <div class="modal-body p-3" style="background:#f8fbfc;">

                <form id="cashCloseForm" autocomplete="off" class="row">
                    @csrf

                    {{-- ID CAJA --}}
                    <input type="hidden" id="cash_id" name="cash_id">

                    {{-- Usuario cierre --}}
                    <input type="hidden" name="closed_by" value="{{ $currentUser->id ?? null }}">

                    <!-- LEFT: Información -->
                    <div class="col-lg-4 mb-3">
                        <div class="card border-0 rounded-lg shadow-sm h-100">
                            <div class="card-body py-4">

                                <small class="text-muted d-block mb-1">Sucursal</small>
                                <div class="font-weight-600 mb-3">
                                    {{ $currentBranchName }}
                                </div>

                                <small class="text-muted d-block mb-1">Cerrado por</small>
                                <div class="font-weight-600 mb-3">
                                    {{ $currentUserName }}
                                </div>

                                <small class="text-muted d-block mb-1">Fecha y hora</small>
                                <div class="font-weight-600 mb-3">
                                    {{ now()->format('d/m/Y H:i') }}
                                </div>

                                <hr>

                                <small class="text-muted d-block mb-1">Estado</small>
                                <span class="badge badge-danger px-3 py-2">
                                    <i class="fas fa-lock mr-1"></i> CIERRE
                                </span>

                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: Formulario -->
                    <div class="col-lg-8">
                        <div class="card border-0 rounded-lg shadow-sm">
                            <div class="card-body">

                                {{-- RESUMEN --}}
                                <div class="form-row">

                                    <div class="form-group col-md-4">
                                        <label class="small font-weight-bold text-secondary">
                                            SALDO INICIAL
                                        </label>
                                        <input type="text"
                                            class="form-control form-control-sm bg-light"
                                            id="closing_opening_amount"
                                            readonly>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label class="small font-weight-bold text-secondary">
                                            TOTAL INGRESOS
                                        </label>
                                        <input type="text"
                                            class="form-control form-control-sm bg-light"
                                            id="closing_total_income"
                                            readonly>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label class="small font-weight-bold text-secondary">
                                            TOTAL EGRESOS
                                        </label>
                                        <input type="text"
                                            class="form-control form-control-sm bg-light"
                                            id="closing_total_expense"
                                            readonly>
                                    </div>

                                </div>

                                {{-- SALDO ESPERADO --}}
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="small font-weight-bold text-secondary">
                                            SALDO ESPERADO EN CAJA
                                        </label>
                                        <input type="text"
                                            class="form-control form-control-sm bg-light font-weight-bold"
                                            id="closing_expected_balance"
                                            readonly>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="small font-weight-bold text-secondary">
                                            DINERO CONTADO (REAL) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number"
                                            step="0.01"
                                            class="form-control form-control-sm"
                                            id="closing_real_amount"
                                            name="closing_amount"
                                            placeholder="0.00">
                                        <span class="invalid-feedback" id="closing_amount-error"></span>
                                    </div>
                                </div>

                                {{-- DIFERENCIA --}}
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="small font-weight-bold text-secondary">
                                            DIFERENCIA
                                        </label>
                                        <input type="text"
                                            class="form-control form-control-sm font-weight-bold"
                                            id="closing_difference"
                                            readonly>
                                    </div>
                                </div>

                                {{-- OBSERVACIONES --}}
                                <div class="form-group">
                                    <label class="small font-weight-bold text-secondary">
                                        OBSERVACIONES DE CIERRE
                                    </label>
                                    <textarea class="form-control form-control-sm"
                                        name="closing_notes"
                                        rows="3"
                                        placeholder="Faltante, sobrante, incidencias, etc."></textarea>
                                </div>

                                {{-- ACCIONES --}}
                                <div class="form-row mt-3">
                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="button"
                                            class="btn btn-light border mr-2"
                                            data-dismiss="modal">
                                            <i class="fas fa-times mr-1"></i> Cancelar
                                        </button>

                                        <button type="submit"
                                            class="btn btn-danger"
                                            id="btnCloseCash">
                                            <i class="fas fa-lock mr-1"></i> Cerrar Caja
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
