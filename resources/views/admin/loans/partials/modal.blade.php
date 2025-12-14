@php
    use App\Models\Branch;
    use Illuminate\Support\Facades\Auth;

    $currentBranch = Branch::find(session('branch_id'));
    $currentBranchName = $currentBranch->name ?? 'Sin sucursal seleccionada';

    $currentUser = Auth::user();
    $currentUserName = $currentUser->name ?? 'Usuario';
@endphp
<!-- Modal elegante para Préstamo (Bootstrap 4) -->
<div class="modal fade" id="loanModal" tabindex="-1" role="dialog" aria-labelledby="loanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0 rounded-lg overflow-hidden">

            <!-- HEADER -->
            <div class="modal-header align-items-center"
                style="background: linear-gradient(90deg,#ffffff,#f3f6f8); border-bottom:1px solid #e6eaee;">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-light mr-3 icon_modal_loan">
                        <i class="fas fa-hand-holding-usd text-secondary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="loanModalLabel">Nuevo Préstamo</h5>
                        <small class="text-muted">Registro de préstamo · completa los campos obligatorios</small>
                    </div>
                </div>

                <button type="button" class="close ml-3" data-dismiss="modal" aria-label="Close" style="opacity:.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- BODY -->
            <div class="modal-body p-3" style="background:#f8fbfc;">
                <form id="loanForm" autocomplete="off" class="row">
                    @csrf

                    {{-- IDs ocultos (puedes llenarlos desde el backend o JS) --}}
                    <input type="hidden" id="branch_id" name="branch_id" value="1">
                    <input type="hidden" id="user_id" name="user_id" value="1">

                    <!-- LEFT: resumen / meta -->
                    <div class="col-lg-4 mb-3">
                        <div class="card border-0 rounded-lg shadow-sm h-100">
                            <div class="card-body py-4">

                                {{-- Cliente --}}
                                <small class="text-muted d-block mb-1">Cliente solicitante</small>
                                <div id="left_client_name" class="font-weight-600 mb-3">
                                    No seleccionado
                                </div>

                                {{-- Garante --}}
                                <small class="text-muted d-block mb-1">Garante</small>
                                <div id="left_guarantor_name" class="font-weight-600 mb-3">
                                    Opcional
                                </div>

                                {{-- Sucursal --}}
                                <small class="text-muted d-block mb-1">Sucursal</small>
                                <div id="left_branch_name" class="font-weight-600 mb-3">
                                    {{ $currentBranchName }}
                                </div>

                                {{-- Usuario --}}
                                <small class="text-muted d-block mb-1">Registrado por</small>
                                <div id="left_user_name" class="font-weight-600 mb-3">
                                    {{ $currentUserName }}
                                </div>


                                <hr>

                                {{-- Estado del préstamo --}}
                                <div class="text-left mt-2">
                                    <small class="text-muted d-block">Estado del préstamo</small>
                                    <div>
                                        <select id="status" name="status" class="form-control form-control-sm mt-1">
                                            <option value="pending" selected>Pendiente</option>
                                            <option value="approved">Aprobado</option>
                                            <option value="rejected">Rechazado</option>
                                            <option value="canceled">Cancelado</option>
                                            <option value="disbursed">Desembolsado</option> <!-- 👈 agregado -->
                                        </select>
                                        <span class="invalid-feedback" id="status-error"></span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: campos del préstamo -->
                    <div class="col-lg-8">
                        <div class="card border-0 rounded-lg shadow-sm">
                            <div class="card-body">

                                {{-- row 1: código + fecha desembolso --}}
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="loan_code" class="small font-weight-bold text-secondary">
                                            CÓDIGO DEL PRÉSTAMO <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-sm" id="loan_code"
                                            name="loan_code" readonly>
                                        <span class="invalid-feedback" id="loan_code-error"></span>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="disbursement_date" class="small font-weight-bold text-secondary">
                                            FECHA DE PRESTAMO
                                        </label>
                                        <input type="date" class="form-control form-control-sm"
                                            id="disbursement_date" name="disbursement_date">
                                        <span class="invalid-feedback" id="disbursement_date-error"></span>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="due_date" class="small font-weight-bold text-secondary">
                                            FECHA DE VENCIMIENTO
                                        </label>
                                        <input type="date" class="form-control form-control-sm" id="due_date"
                                            name="due_date">
                                        <span class="invalid-feedback" id="due_date-error"></span>
                                    </div>
                                </div>

                                {{-- row 2: cliente + garante --}}
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="client_id" class="small font-weight-bold text-secondary">
                                            CLIENTE SOLICITANTE <span class="text-danger">*</span>
                                        </label>
                                        <select id="client_id" name="client_id" class="form-control form-control-sm">
                                            <option value="">Seleccione cliente</option>
                                            @foreach ($clients as $client)
                                                <option value="{{ $client->id }}">{{ $client->full_name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="invalid-feedback" id="client_id-error"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="guarantor_id" class="small font-weight-bold text-secondary">
                                            GARANTE (opcional)
                                        </label>
                                        <select id="guarantor_id" name="guarantor_id"
                                            class="form-control form-control-sm">
                                            <option value="">Sin garante</option>
                                            @foreach ($guarantors as $guarantor)
                                                <option value="{{ $guarantor->id }}">{{ $guarantor->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="invalid-feedback" id="guarantor_id-error"></span>
                                    </div>
                                </div>

                                {{-- row 3: monto + plazo --}}
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="amount" class="small font-weight-bold text-secondary">
                                            MONTO SOLICITADO (S/) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" step="0.01" class="form-control form-control-sm"
                                            id="amount" name="amount" placeholder="0.00">
                                        <span class="invalid-feedback" id="amount-error"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="term_months" class="small font-weight-bold text-secondary">
                                            PLAZO (MESES) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control form-control-sm" id="term_months"
                                            name="term_months" placeholder="12">
                                        <span class="invalid-feedback" id="term_months-error"></span>
                                    </div>
                                </div>

                                {{-- row 4: tasa + cuota --}}
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="interest_rate" class="small font-weight-bold text-secondary">
                                            INTERÉS (%) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" step="0.01" class="form-control form-control-sm"
                                            id="interest_rate" name="interest_rate" placeholder="Ej: 24.50">
                                        <span class="invalid-feedback" id="interest_rate-error"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="monthly_payment" class="small font-weight-bold text-secondary">
                                            CUOTA MENSUAL (S/)
                                        </label>
                                        <input type="number" step="0.01" class="form-control form-control-sm"
                                            id="monthly_payment" name="monthly_payment"
                                            placeholder="Se puede calcular" readonly>
                                        <span class="invalid-feedback" id="monthly_payment-error"></span>
                                    </div>
                                </div>

                                {{-- row 5: total a pagar --}}
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="total_payable" class="small font-weight-bold text-secondary">
                                            TOTAL A PAGAR (S/)
                                        </label>
                                        <input type="number" step="0.01" class="form-control form-control-sm"
                                            id="total_payable" name="total_payable" placeholder="Se puede calcular"
                                            readonly>
                                        <span class="invalid-feedback" id="total_payable-error"></span>
                                    </div>
                                </div>

                                {{-- row 6: notas --}}
                                <div class="form-row">
                                    <div class="form-group col-md-12">
                                        <label for="notes" class="small font-weight-bold text-secondary">
                                            NOTAS INTERNAS
                                        </label>
                                        <textarea class="form-control form-control-sm" id="notes" name="notes" rows="3"
                                            placeholder="Detalle condiciones especiales, observaciones, etc."></textarea>
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
                                        <button type="submit" class="btn btn-primary" id="btnSaveLoan">
                                            <i class="fas fa-save mr-1"></i> Guardar Préstamo
                                        </button>
                                    </div>
                                </div>

                            </div> {{-- card-body --}}
                        </div> {{-- card --}}
                    </div> {{-- col-lg-8 --}}

                </form>
            </div> {{-- modal-body --}}

        </div>
    </div>
</div>
