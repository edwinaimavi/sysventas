<div class="btn-group btn-group-sm">

    @if ($cash->status === 'open')

        {{-- 💰 Reponer dinero --}}
        <button class="btn btn-success replenishCash"
            data-id="{{ $cash->id }}"
            data-bs-toggle="tooltip"
            title="Reponer dinero">
            <i class="fas fa-coins"></i>
        </button>

        {{-- 🔒 Cerrar caja --}}
        <button class="btn btn-warning closeCash"
            data-id="{{ $cash->id }}"
            data-bs-toggle="tooltip"
            title="Cerrar caja">
            <i class="fas fa-door-closed"></i>
        </button>

    @endif

    {{-- 👁 Ver detalle --}}
    <button class="btn btn-info viewCash"
        data-id="{{ $cash->id }}"
        data-bs-toggle="tooltip"
        title="Ver detalle">
        <i class="fas fa-eye"></i>
    </button>

</div>
