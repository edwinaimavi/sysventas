{{-- resources/views/admin/reminders/partials/modal.blade.php --}}
@php
    use App\Models\Branch;
    use Illuminate\Support\Facades\Auth;

    $currentBranch = Branch::find(session('branch_id'));
    $currentBranchName = $currentBranch->name ?? 'Sin sucursal seleccionada';

    $currentUser = Auth::user();
    $currentUserName = $currentUser->name ?? 'Usuario';
@endphp

<div class="modal fade" id="reminderModal" tabindex="-1" role="dialog" aria-labelledby="reminderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0 rounded-lg overflow-hidden">

            {{-- HEADER --}}
            <div class="modal-header align-items-center"
                style="background: linear-gradient(90deg,#ffffff,#f3f6f8); border-bottom:1px solid #e6eaee;">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-light mr-3 icon_modal_reminder">
                        <i class="fas fa-bell text-secondary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="reminderModalLabel">Nuevo Recordatorio</h5>
                        <small class="text-muted">Programación rápida · completa los campos obligatorios</small>
                    </div>
                </div>

                <button type="button" class="close ml-3" data-dismiss="modal" aria-label="Close" style="opacity:.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- BODY --}}
            <div class="modal-body p-3" style="background:#f8fbfc;">
                <form id="reminderForm" autocomplete="off" class="row">
                    @csrf

                    {{-- IDs ocultos --}}
                    <input type="hidden" id="branch_id" name="branch_id" value="{{ session('branch_id') }}">
                    <input type="hidden" id="user_id" name="user_id" value="{{ auth()->id() }}">

                    {{-- LEFT: resumen / meta --}}
                    <div class="col-lg-4 mb-3">
                        <div class="card border-0 rounded-lg shadow-sm h-100">
                            <div class="card-body py-4">

                                <small class="text-muted d-block mb-1">Sucursal</small>
                                <div id="left_branch" class="font-weight-600 mb-3">
                                    {{ $currentBranchName }}
                                </div>

                                <small class="text-muted d-block mb-1">Creado por</small>
                                <div id="left_user" class="font-weight-600 mb-3">
                                    {{ $currentUserName }}
                                </div>

                                <hr>

                                <small class="text-muted d-block mb-1">Resumen</small>
                                <div class="mb-2">
                                    <small class="text-muted">Título</small>
                                    <div id="left_title" class="font-weight-600">—</div>
                                </div>

                                <div class="mb-2">
                                    <small class="text-muted">Fecha</small>
                                    <div id="left_remind_at" class="font-weight-600">—</div>
                                </div>

                                <div class="mb-2">
                                    <small class="text-muted">Prioridad</small>
                                    <div id="left_priority" class="badge badge-info py-2 px-3 mt-1">Normal</div>
                                </div>

                                <div class="mb-2">
                                    <small class="text-muted">Estado</small>
                                    <div id="left_status" class="badge badge-warning py-2 px-3 mt-1">Pendiente</div>
                                </div>

                                <hr>

                                <small class="text-muted d-block mb-1">Destino</small>
                                <div class="mb-2">
                                    <small class="text-muted">Cliente</small>
                                    <div id="left_client" class="font-weight-600">—</div>
                                </div>
                                <div class="mb-0">
                                    <small class="text-muted">Préstamo</small>
                                    <div id="left_loan" class="font-weight-600">—</div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- RIGHT: form --}}
                    <div class="col-lg-8">
                        <div class="card border-0 rounded-lg shadow-sm">
                            <div class="card-body">

                                {{-- row 1: título --}}
                                <div class="form-row">
                                    <div class="form-group col-md-12">
                                        <label for="title" class="small font-weight-bold text-secondary">
                                            TÍTULO <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-sm" id="title" name="title"
                                            placeholder="Ej: Cobro pendiente - llamar al cliente" maxlength="150">
                                        <span class="invalid-feedback" id="title-error"></span>
                                    </div>
                                </div>

                                {{-- row 2: mensaje --}}
                                <div class="form-row">
                                    <div class="form-group col-md-12">
                                        <label for="message" class="small font-weight-bold text-secondary">
                                            MENSAJE / DETALLE
                                        </label>
                                        <textarea class="form-control form-control-sm" id="message" name="message" rows="3"
                                            placeholder="Detalle del recordatorio (opcional)"></textarea>
                                        <span class="invalid-feedback" id="message-error"></span>
                                    </div>
                                </div>

                                {{-- row 3: cliente + préstamo --}}
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="client_id" class="small font-weight-bold text-secondary">
                                            CLIENTE
                                        </label>
                                        <select id="client_id" name="client_id" class="form-control form-control-sm">
                                            <option value="">— Opcional —</option>
                                            {{-- Si quieres, luego lo llenamos por AJAX o lo pasas desde backend --}}
                                        </select>
                                        <span class="invalid-feedback" id="client_id-error"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="loan_id" class="small font-weight-bold text-secondary">
                                            PRÉSTAMO
                                        </label>
                                        <select id="loan_id" name="loan_id" class="form-control form-control-sm">
                                            <option value="">— Opcional —</option>
                                            {{-- Si quieres, luego lo llenamos por AJAX o lo pasas desde backend --}}
                                        </select>
                                        <span class="invalid-feedback" id="loan_id-error"></span>
                                    </div>
                                </div>

                                {{-- row 4: tipo + prioridad --}}
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="type" class="small font-weight-bold text-secondary">
                                            TIPO
                                        </label>
                                        <select id="type" name="type" class="form-control form-control-sm">
                                            <option value="manual" selected>Manual</option>
                                            <option value="payment_due">Pago por vencer</option>
                                            <option value="payment_overdue">Pago vencido</option>
                                            <option value="loan_finish">Préstamo finaliza</option>
                                        </select>
                                        <span class="invalid-feedback" id="type-error"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="priority" class="small font-weight-bold text-secondary">
                                            PRIORIDAD
                                        </label>
                                        <select id="priority" name="priority" class="form-control form-control-sm">
                                            <option value="low">Baja</option>
                                            <option value="normal" selected>Normal</option>
                                            <option value="high">Alta</option>
                                        </select>
                                        <span class="invalid-feedback" id="priority-error"></span>
                                    </div>
                                </div>

                                {{-- row 5: fecha recordatorio + vence --}}
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="remind_at" class="small font-weight-bold text-secondary">
                                            FECHA / HORA RECORDATORIO <span class="text-danger">*</span>
                                        </label>
                                        <input type="datetime-local" class="form-control form-control-sm" id="remind_at" name="remind_at">
                                        <span class="invalid-feedback" id="remind_at-error"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="expires_at" class="small font-weight-bold text-secondary">
                                            EXPIRA (opcional)
                                        </label>
                                        <input type="datetime-local" class="form-control form-control-sm" id="expires_at" name="expires_at">
                                        <span class="invalid-feedback" id="expires_at-error"></span>
                                    </div>
                                </div>

                                {{-- row 6: canal + estado (estado lo dejamos fijo en pending por UI) --}}
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="channel" class="small font-weight-bold text-secondary">
                                            CANAL
                                        </label>
                                        <select id="channel" name="channel" class="form-control form-control-sm">
                                            <option value="system" selected>Sistema</option>
                                            <option value="email">Email</option>
                                            <option value="whatsapp">WhatsApp</option>
                                            <option value="sms">SMS</option>
                                        </select>
                                        <span class="invalid-feedback" id="channel-error"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="status" class="small font-weight-bold text-secondary">
                                            ESTADO
                                        </label>
                                        <select id="status" name="status" class="form-control form-control-sm" disabled>
                                            <option value="pending" selected>Pendiente</option>
                                            <option value="triggered">Ejecutado</option>
                                            <option value="cancelled">Cancelado</option>
                                        </select>
                                        <small class="text-muted d-block mt-1">Se asigna automáticamente al crear.</small>
                                    </div>
                                </div>

                                {{-- acciones --}}
                                <div class="form-row mt-3">
                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="button" class="btn btn-light border mr-2" data-dismiss="modal">
                                            <i class="fas fa-times mr-1"></i> Cerrar
                                        </button>
                                        <button type="submit" class="btn btn-primary" id="btnSaveReminder">
                                            <i class="fas fa-save mr-1"></i> Guardar Recordatorio
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
