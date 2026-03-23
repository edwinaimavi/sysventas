@php
    use App\Models\Branch;
    use Illuminate\Support\Facades\Auth;

    $currentBranch = Branch::find(session('branch_id'));
    $currentBranchName = $currentBranch->name ?? 'Sin sucursal seleccionada';

    $currentUser = Auth::user();
    $currentUserName = $currentUser->name ?? 'Usuario';
@endphp

<!-- Modal elegante para Apertura de Caja -->
<div class="modal fade" id="cashOpenModal" tabindex="-1" role="dialog" aria-labelledby="cashOpenModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0 rounded-lg overflow-hidden">

            <!-- HEADER -->
            <div class="modal-header align-items-center"
                style="background: linear-gradient(90deg,#ffffff,#f3f6f8); border-bottom:1px solid #e6eaee;">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-light mr-3">
                        <i class="fas fa-cash-register text-secondary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="cashOpenModalLabel">Apertura de Caja</h5>
                        <small class="text-muted">Registro de apertura · monto inicial y control</small>
                    </div>
                </div>

                <button type="button" class="close ml-3" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- BODY -->
            <div class="modal-body p-3" style="background:#f8fbfc;">
                <form id="cashOpenForm" autocomplete="off" class="row">
                    @csrf

                    {{-- Campos ocultos --}}
                    <input type="hidden" name="branch_id" value="{{ session('branch_id') }}">
                    <input type="hidden" name="opened_by" value="{{ $currentUser->id ?? null }}">
                    <input type="hidden" name="status" value="open">

                    <!-- LEFT: información -->
                    <div class="col-lg-4 mb-3">
                        <div class="card border-0 rounded-lg shadow-sm h-100">
                            <div class="card-body py-4">

                                {{-- Sucursal --}}
                                <small class="text-muted d-block mb-1">Sucursal</small>
                                <div class="font-weight-600 mb-3">
                                    {{ $currentBranchName }}
                                </div>

                                {{-- Usuario --}}
                                <small class="text-muted d-block mb-1">Aperturado por</small>
                                <div class="font-weight-600 mb-3">
                                    {{ $currentUserName }}
                                </div>

                                {{-- Fecha --}}
                                <small class="text-muted d-block mb-1">Fecha y hora</small>
                                <div class="font-weight-600 mb-3">
                                    {{ now()->format('d/m/Y H:i') }}
                                </div>

                                <hr>

                                {{-- Estado --}}
                                <small class="text-muted d-block mb-1">Estado de caja</small>
                                <span class="badge badge-success px-3 py-2">
                                    <i class="fas fa-lock-open mr-1"></i> ABIERTA
                                </span>

                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: formulario -->
                    <div class="col-lg-8">
                        <div class="card border-0 rounded-lg shadow-sm">
                            <div class="card-body">

                                {{-- Monto inicial --}}
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="opening_amount"
                                            class="small font-weight-bold text-secondary">
                                            MONTO INICIAL (S/) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" step="0.01"
                                            class="form-control form-control-sm"
                                            id="opening_amount"
                                            name="opening_amount"
                                            placeholder="0.00">
                                        <span class="invalid-feedback" id="opening_amount-error"></span>
                                    </div>

                                    {{-- Fecha apertura --}}
                                    <div class="form-group col-md-6">
                                        <label for="opened_at"
                                            class="small font-weight-bold text-secondary">
                                            FECHA DE APERTURA
                                        </label>
                                        <input type="datetime-local"
                                            class="form-control form-control-sm"
                                            id="opened_at"
                                            name="opened_at"
                                            value="{{ now()->format('Y-m-d\TH:i') }}">
                                        <span class="invalid-feedback" id="opened_at-error"></span>
                                    </div>
                                </div>

                                {{-- Observaciones --}}
                                <div class="form-row">
                                    <div class="form-group col-md-12">
                                        <label for="notes"
                                            class="small font-weight-bold text-secondary">
                                            OBSERVACIONES
                                        </label>
                                        <textarea class="form-control form-control-sm"
                                            id="notes"
                                            name="notes"
                                            rows="3"
                                            placeholder="Detalle del efectivo, incidencias, observaciones iniciales"></textarea>
                                        <span class="invalid-feedback" id="notes-error"></span>
                                    </div>
                                </div>

                                {{-- Acciones --}}
                                <div class="form-row mt-3">
                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="button"
                                            class="btn btn-light border mr-2"
                                            data-dismiss="modal">
                                            <i class="fas fa-times mr-1"></i> Cancelar
                                        </button>
                                        <button type="submit"
                                            class="btn btn-success"
                                            id="btnOpenCash">
                                            <i class="fas fa-door-open mr-1"></i> Aperturar Caja
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
