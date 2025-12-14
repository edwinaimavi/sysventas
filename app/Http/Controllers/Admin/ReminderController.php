<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reminder;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Client;
use App\Models\Loan;
use Illuminate\Support\Carbon;

class ReminderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.reminders.index');
    }



    public function clients()
    {
        $branchId = session('branch_id');

        $clients = Client::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        return response()->json([
            'status' => 'success',
            'data' => $clients
        ]);
    }

    public function clientLoans($clientId)
    {
        $branchId = session('branch_id');

        $loans = Loan::query()
            ->where('client_id', $clientId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('id', 'desc')
            ->get(['id', 'loan_code', 'status']);

        $statusMap = [
            'pending'    => 'Pendiente',
            'approved'   => 'Aprobado',
            'disbursed'  => 'Desembolsado',
            'finished'   => 'Finalizado',
            'cancelled'  => 'Cancelado',
        ];

        $data = $loans->map(function ($loan) use ($statusMap) {
            $statusLabel = $statusMap[$loan->status] ?? ucfirst($loan->status);

            return [
                'id'         => $loan->id,
                'loan_code'  => $loan->loan_code,
                'status'     => $loan->status,        // por si lo necesitas
                'status_label' => $statusLabel,       // ✅ español
                'text'       => "{$loan->loan_code} ({$statusLabel})", // ✅ listo para el select
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }



    public function list()
    {
        $branchId = session('branch_id');

        $reminders = Reminder::with(['client', 'loan', 'user', 'branch'])
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->orderBy('id', 'desc'); // último primero

        return DataTables::of($reminders)
            ->addIndexColumn()

            ->editColumn('title', function ($r) {
                $title = e($r->title);
                $unread = !$r->is_read ? ' <span class="badge bg-primary ms-1">Nuevo</span>' : '';

                $fired = ($r->status === 'triggered' && !$r->is_read)
                    ? ' <span class="badge bg-success ms-1">Disparado</span>'
                    : '';

                return '<span class="fw-semibold">' . $title . '</span>' . $unread . $fired;
            })

            ->addColumn('client_name', function ($r) {
                return optional($r->client)->full_name ?? '—';
            })

            ->addColumn('loan_code', function ($r) {
                return optional($r->loan)->loan_code ?? '—';
            })

            ->editColumn('remind_at', function ($r) {
                return $r->remind_at ? $r->remind_at->format('Y-m-d H:i') : '—';
            })

            ->editColumn('priority', function ($r) {
                $map = [
                    'low'    => ['badge' => 'bg-secondary', 'label' => 'Baja',   'icon' => 'bi-arrow-down'],
                    'normal' => ['badge' => 'bg-info',      'label' => 'Normal', 'icon' => 'bi-info-circle'],
                    'high'   => ['badge' => 'bg-danger',    'label' => 'Alta',   'icon' => 'bi-exclamation-triangle'],
                ];
                $p = $map[$r->priority] ?? ['badge' => 'bg-secondary', 'label' => ucfirst($r->priority), 'icon' => 'bi-question-circle'];

                return sprintf(
                    '<span class="badge %s text-light rounded-pill px-3 py-2 fs-6 shadow-sm">
                    <i class="bi %s me-1"></i> %s
                </span>',
                    $p['badge'],
                    $p['icon'],
                    $p['label']
                );
            })

            ->editColumn('type', function ($r) {
                // lo puedes mapear a algo más bonito
                $labels = [
                    'manual'          => 'Manual',
                    'payment_due'     => 'Pago por vencer',
                    'payment_overdue' => 'Pago vencido',
                    'loan_finish'     => 'Préstamo finaliza',
                ];
                $label = $labels[$r->type] ?? ucfirst(str_replace('_', ' ', $r->type));
                return '<span class="badge bg-dark text-light rounded-pill px-3 py-2 fs-6 shadow-sm">' . $label . '</span>';
            })

            ->editColumn('status', function ($r) {
                $map = [
                    'pending'   => ['badge' => 'bg-warning', 'label' => 'Pendiente',  'icon' => 'bi-hourglass-split'],
                    'triggered' => ['badge' => 'bg-success', 'label' => 'Ejecutado',  'icon' => 'bi-check-circle'],
                    'cancelled' => ['badge' => 'bg-danger',  'label' => 'Cancelado',  'icon' => 'bi-x-circle'],
                ];
                $s = $map[$r->status] ?? ['badge' => 'bg-secondary', 'label' => ucfirst($r->status), 'icon' => 'bi-question-circle'];

                return sprintf(
                    '<span class="badge %s text-light rounded-pill px-3 py-2 fs-6 shadow-sm">
                    <i class="bi %s me-1"></i> %s
                </span>',
                    $s['badge'],
                    $s['icon'],
                    $s['label']
                );
            })

            ->editColumn('channel', function ($r) {
                $labels = [
                    'system'   => 'Sistema',
                    'email'    => 'Email',
                    'whatsapp' => 'WhatsApp',
                    'sms'      => 'SMS',
                ];
                $label = $labels[$r->channel] ?? ucfirst($r->channel);
                return '<span class="badge bg-secondary text-light rounded-pill px-3 py-2 fs-6 shadow-sm">' . $label . '</span>';
            })

            ->addColumn('acciones', function ($r) {
                return view('admin.reminders.partials.acciones', ['reminder' => $r])->render();
            })

            ->rawColumns(['title', 'priority', 'type', 'status', 'channel', 'acciones'])
            ->make(true);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */


    public function store(Request $request)
    {
        $branchId = session('branch_id');

        if (!$branchId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No hay sucursal seleccionada en la sesión.',
            ], 422);
        }

        $data = $request->validate([
            'branch_id' => 'nullable|integer', // lo ignoraremos y usaremos el de sesión
            'user_id'   => 'nullable|integer', // destinatario (opcional)
            'client_id' => 'nullable|exists:clients,id',
            'loan_id'   => 'nullable|exists:loans,id',

            'title'   => 'required|string|max:150',
            'message' => 'nullable|string',

            'type'     => 'nullable|in:manual,payment_due,payment_overdue,loan_finish',
            'priority' => 'nullable|in:low,normal,high',

            'remind_at'         => 'required|date',
            'expires_at'        => 'nullable|date|after_or_equal:remind_at',
            'created_for_date'  => 'nullable|date',

            'status' => 'nullable|in:pending,triggered,cancelled', // en create debe quedar pending

            'channel'         => 'nullable|in:system,email,whatsapp,sms',
            'channel_status'  => 'nullable|in:pending,sent,failed',
            'channel_response' => 'nullable|string',
        ], [
            'title.required'    => 'El título es obligatorio.',
            'remind_at.required' => 'La fecha/hora del recordatorio es obligatoria.',
            'expires_at.after_or_equal' => 'La fecha de expiración no puede ser menor que la fecha del recordatorio.',
        ]);

        // Seguridad: branch siempre desde sesión
        $data['branch_id'] = $branchId;

        // Defaults
        $data['type'] = $data['type'] ?? 'manual';
        $data['priority'] = $data['priority'] ?? 'normal';
        $data['channel'] = $data['channel'] ?? 'system';

        // En creación: status siempre pending
        $data['status'] = 'pending';

        // Destinatario: si no mandas user_id, por defecto el usuario logueado
        $data['user_id'] = $data['user_id'] ?? Auth::id();

        // Auditoría
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        try {
            DB::beginTransaction();

            $reminder = Reminder::create($data);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Recordatorio creado correctamente.',
                'data'    => $reminder,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error creando recordatorio: " . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Error al crear el recordatorio.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }




    public function navbar()
    {
        $branchId = session('branch_id');

        $q = Reminder::with(['client', 'loan'])
            ->when($branchId, fn($qq) => $qq->where('branch_id', $branchId))
            ->whereIn('status', ['pending', 'triggered'])
            ->orderByRaw("is_read asc")
            ->orderBy('remind_at', 'asc')
            ->limit(8);

        $items = $q->get()->map(function ($r) {
            return [
                'id' => $r->id,
                'title' => $r->title,
                'priority' => $r->priority,
                'is_read' => (bool)$r->is_read,
                'remind_at' => optional($r->remind_at)->format('Y-m-d H:i'),
                'client' => optional($r->client)->full_name,
                'loan' => optional($r->loan)->loan_code,
            ];
        });

        $unread = Reminder::query()
            ->when($branchId, fn($qq) => $qq->where('branch_id', $branchId))
            ->where('is_read', 0)
            ->whereIn('status', ['pending', 'triggered'])
            ->count();

        return response()->json([
            'unread' => $unread,
            'items' => $items,
        ]);
    }

    public function markRead(Reminder $reminder)
    {
        $branchId = session('branch_id');

        if ($branchId && (int)$reminder->branch_id !== (int)$branchId) {
            return response()->json(['status' => 'error', 'message' => 'No autorizado'], 403);
        }

        $reminder->is_read = 1;
        $reminder->save();

        return response()->json(['status' => 'success']);
    }

    public function showJson(Reminder $reminder)
    {
        $branchId = session('branch_id');

        if ($branchId && (int)$reminder->branch_id !== (int)$branchId) {
            return response()->json(['status' => 'error', 'message' => 'No autorizado'], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $reminder->id,
                'title' => $reminder->title,
                'message' => $reminder->message,
                'status' => $reminder->status,
                'priority' => $reminder->priority,
                'type' => $reminder->type,
                'remind_at' => optional($reminder->remind_at)->format('Y-m-d H:i'),
                'expires_at' => optional($reminder->expires_at)->format('Y-m-d H:i'),
                'client' => optional($reminder->client)->full_name,
                'loan' => optional($reminder->loan)->loan_code,
            ]
        ]);
    }


    public function cancel(Reminder $reminder)
    {
        $branchId = session('branch_id');

        // Seguridad por sucursal
        if ($branchId && (int)$reminder->branch_id !== (int)$branchId) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado'
            ], 403);
        }

        // Solo se puede cancelar si está pendiente o triggered
        if (!in_array($reminder->status, ['pending', 'triggered'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Este recordatorio no se puede cancelar.'
            ], 409);
        }

        $reminder->status = 'cancelled';
        $reminder->cancelled_at = now();
        $reminder->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Recordatorio cancelado correctamente.'
        ]);
    }





    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
