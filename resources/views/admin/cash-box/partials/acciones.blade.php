<div class="btn-group btn-group-sm">

    @if ($cash->status === 'open')
        {{-- 💰 Reponer dinero --}}
        <button class="btn btn-success replenishCash mr-2" data-id="{{ $cash->id }}" data-bs-toggle="tooltip"
            title="Reponer dinero">
            <i class="fas fa-coins"></i>
        </button>
        {{-- 👁 Ver detalle --}}
        <button class="btn btn-danger cashOut mr-2" data-id="{{ $cash->id }}" data-bs-toggle="tooltip"
            title="Retirar Dinero">
            <i class="fas fa-hand-holding-usd"></i>
        </button>

        {{-- 🔒 Cerrar caja --}}
        <button class="btn btn-warning closeCash mr-2" data-id="{{ $cash->id }}" data-bs-toggle="tooltip"
            title="Cerrar caja">
            <i class="fas fa-door-closed"></i>
        </button>
    @endif
  {{-- 👁 Ver detalle --}}
        <button class="btn btn-primary viewCash " data-id="{{ $cash->id }}" data-bs-toggle="tooltip"
            title="Ver detalle">
            <i class="fas fa-eye"></i>
        </button>



</div>
