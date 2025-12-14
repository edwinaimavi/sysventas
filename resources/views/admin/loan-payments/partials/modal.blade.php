{{-- Modal elegante para Pago de Préstamo (Bootstrap 4) --}}
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0 rounded-lg overflow-hidden">

            {{-- HEADER --}}
            <div class="modal-header align-items-center"
                style="background: linear-gradient(90deg,#ffffff,#f3f6f8); border-bottom:1px solid #e6eaee;">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-light mr-3 icon_modal_payment">
                        <i class="fas fa-cash-register text-secondary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="paymentModalLabel">Nuevo Pago</h5>
                        <small class="text-muted">Registro de pago · completa los campos obligatorios</small>
                    </div>
                </div>

                <button type="button" class="close ml-3" data-dismiss="modal" aria-label="Close" style="opacity:.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- BODY --}}
            <div class="modal-body p-3" style="background:#f8fbfc;">
                <form id="paymentForm" autocomplete="off" class="row" enctype="multipart/form-data">
                    @csrf

                    {{-- IDs ocultos (puedes llenarlos desde el backend o JS) --}}
                    <input type="hidden" id="branch_id" name="branch_id" value="1"
                        data-branch_name="{{ auth()->user()->branch->name ?? 'Sucursal' }}">

                    <input type="hidden" id="user_id" name="user_id" value="{{ auth()->id() }}"
                        data-user_name="{{ auth()->user()->name ?? 'Usuario' }}">


                    {{-- LEFT: resumen / meta --}}
                    <div class="col-lg-4 mb-3">
                        <div class="card border-0 rounded-lg shadow-sm h-100">
                            <div class="card-body py-4">

                                {{-- Préstamo --}}
                                <small class="text-muted d-block mb-1">Préstamo</small>
                                <div id="left_loan_code" class="font-weight-600 mb-2">
                                    No seleccionado
                                </div>

                                {{-- Total a pagar del préstamo --}}
                                <small class="text-muted d-block mb-1">Total a pagar del préstamo</small>
                                <div id="left_total_payable" class="font-weight-600 mb-3">
                                    S/ 0.00
                                </div>

                                {{-- ⭐ Saldo pendiente actual (antes de este pago) --}}
                                <small class="text-muted d-block mb-1">Saldo pendiente actual</small>
                                <div id="left_current_balance" class="font-weight-600 mb-3 px-2 py-1"
                                    style="background:#fff3cd; color:#856404; border-radius:6px; display:inline-block;">
                                    S/ 0.00
                                </div>

                                <small class="text-muted d-block mb-1">Saldo estimado luego del pago</small>
                                <div id="left_remaining_balance" class="font-weight-600">
                                    S/ 0.00
                                </div>

                                <hr>

                                {{-- Cliente --}}
                                <small class="text-muted d-block mb-1">Cliente</small>
                                <div id="left_client_name" class="font-weight-600 mb-3">
                                    —
                                </div>

                                {{-- Sucursal --}}
                                <small class="text-muted d-block mb-1">Sucursal</small>
                                <div id="left_branch_name" class="font-weight-600 mb-3">
                                    Sucursal
                                </div>

                                {{-- Usuario --}}
                                <small class="text-muted d-block mb-1">Registrado por</small>
                                <div id="left_user_name" class="font-weight-600 mb-3">
                                    Usuario
                                </div>

                                {{-- Saldo luego del pago (referencial) --}}
                              


                                <hr>

                                {{-- Estado del pago --}}
                                <div class="text-left mt-2">
                                    <small class="text-muted d-block">Estado del pago</small>
                                    <div>
                                        <select id="status" name="status" class="form-control form-control-sm mt-1">
                                            <option value="completed" selected>Completado</option>
                                            <option value="pending">Pendiente</option>
                                            <option value="reversed">Revertido</option>
                                        </select>
                                        <span class="invalid-feedback" id="status-error"></span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card border-0 rounded-lg shadow-sm">
                            <div class="card-body">

                                {{-- row 1: código pago + fecha --}}
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="payment_code" class="small font-weight-bold text-secondary">
                                            CÓDIGO DEL PAGO <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-sm" id="payment_code"
                                            name="payment_code" readonly>
                                        <span class="invalid-feedback" id="payment_code-error"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="payment_date" class="small font-weight-bold text-secondary">
                                            FECHA DEL PAGO <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control form-control-sm" id="payment_date"
                                            name="payment_date">
                                        <span class="invalid-feedback" id="payment_date-error"></span>
                                    </div>
                                </div>

                                {{-- row 2: préstamo + monto --}}
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="loan_id" class="small font-weight-bold text-secondary">
                                            PRÉSTAMO <span class="text-danger">*</span>
                                        </label>
                                        <select id="loan_id" name="loan_id" class="form-control form-control-sm">
                                            <option value="">Seleccione préstamo</option>
                                            @isset($loans)
                                                @foreach ($loans as $loan)
                                                    <option value="{{ $loan->id }}"
                                                        data-loan_code="{{ $loan->loan_code }}"
                                                        data-client_name="{{ optional($loan->client)->full_name }}"
                                                        data-remaining_balance="{{ $loan->remaining_balance_calc ?? $loan->total_payable }}"
                                                        data-total_payable="{{ $loan->total_payable ?? 0 }}">
                                                        {{ $loan->loan_code }} - {{ optional($loan->client)->full_name }}
                                                    </option>
                                                @endforeach
                                            @endisset
                                        </select>

                                        <span class="invalid-feedback" id="loan_id-error"></span>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="amount" class="small font-weight-bold text-secondary">
                                            MONTO PAGADO (S/) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" step="0.01" class="form-control form-control-sm"
                                            id="amount" name="amount" placeholder="0.00">
                                        <span class="invalid-feedback" id="amount-error"></span>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="payment_type" class="small font-weight-bold text-secondary">
                                            TIPO DE PAGO
                                        </label>
                                        <select id="payment_type" name="payment_type"
                                            class="form-control form-control-sm">
                                            <option value="partial" selected>Pago parcial / Amortización</option>
                                            <option value="full">Pago total</option>
                                        </select>
                                        <span class="invalid-feedback" id="payment_type-error"></span>
                                    </div>
                                </div>

                                {{-- row 3: desglose capital / interés / mora --}}
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="capital" class="small font-weight-bold text-secondary">
                                            CAPITAL (S/)
                                        </label>
                                        <input type="hidden" step="0.01" class="form-control form-control-sm"
                                            id="capital" name="capital" placeholder="0.00">
                                        <span class="invalid-feedback" id="capital-error"></span>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="interest" class="small font-weight-bold text-secondary">
                                            INTERÉS (S/)
                                        </label>
                                        <input type="hidden" step="0.01" class="form-control form-control-sm"
                                            id="interest" name="interest" placeholder="0.00">
                                        <span class="invalid-feedback" id="interest-error"></span>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="late_fee" class="small font-weight-bold text-secondary">
                                            MORA (S/)
                                        </label>
                                        <input type="hidden" step="0.01" class="form-control form-control-sm"
                                            id="late_fee" name="late_fee" placeholder="0.00">
                                        <span class="invalid-feedback" id="late_fee-error"></span>
                                    </div>
                                </div>

                                {{-- row 4: método + referencia --}}
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="method" class="small font-weight-bold text-secondary">
                                            MÉTODO DE PAGO
                                        </label>
                                        <select id="method" name="method" class="form-control form-control-sm">
                                            <option value="">Seleccione método</option>
                                            <option value="cash">Efectivo</option>
                                            <option value="bank_transfer">Transferencia bancaria</option>
                                            <option value="yape">Yape</option>
                                            <option value="plin">Plin</option>
                                            <option value="other">Otro</option>
                                        </select>
                                        <span class="invalid-feedback" id="method-error"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="reference" class="small font-weight-bold text-secondary">
                                            REFERENCIA / N° OPERACIÓN
                                        </label>
                                        <input type="text" class="form-control form-control-sm" id="reference"
                                            name="reference" placeholder="N° operación, código de transacción, etc.">
                                        <span class="invalid-feedback" id="reference-error"></span>
                                    </div>
                                </div>

                                {{-- row 5: comprobante (número + archivo) --}}
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="receipt_number" class="small font-weight-bold text-secondary">
                                            N° COMPROBANTE
                                        </label>
                                        <input type="text" class="form-control form-control-sm"
                                            id="receipt_number" name="receipt_number"
                                            placeholder="Serie y número, si aplica">
                                        <span class="invalid-feedback" id="receipt_number-error"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="small font-weight-bold text-secondary d-block">
                                            ARCHIVO DEL COMPROBANTE
                                        </label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="receipt_file"
                                                name="receipt_file">
                                            <label class="custom-file-label" for="receipt_file">Seleccionar
                                                archivo...</label>
                                        </div>
                                        <span class="invalid-feedback d-block" id="receipt_file-error"></span>
                                    </div>
                                </div>

                                {{-- row 6: saldo restante --}}
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="remaining_balance" class="small font-weight-bold text-secondary">
                                            SALDO LUEGO DEL PAGO (S/)
                                        </label>
                                        <input type="number" step="0.01" class="form-control form-control-sm"
                                            id="remaining_balance" name="remaining_balance"
                                            placeholder="Se puede calcular" readonly>
                                        <span class="invalid-feedback" id="remaining_balance-error"></span>
                                    </div>
                                </div>

                                {{-- row 7: notas --}}
                                <div class="form-row">
                                    <div class="form-group col-md-12">
                                        <label for="notes" class="small font-weight-bold text-secondary">
                                            NOTAS INTERNAS
                                        </label>
                                        <textarea class="form-control form-control-sm" id="notes" name="notes" rows="3"
                                            placeholder="Detalle observaciones del pago, acuerdos, aclaraciones, etc."></textarea>
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
                                        <button type="submit" class="btn btn-primary" id="btnSavePayment">
                                            <i class="fas fa-save mr-1"></i> Guardar Pago
                                        </button>
                                    </div>
                                </div>

                            </div>{{-- card-body --}}
                        </div>{{-- card --}}
                    </div>{{-- col-lg-8 --}}

                </form>
            </div>{{-- modal-body --}}

        </div>
    </div>
</div>
