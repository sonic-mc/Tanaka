<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Response;
use App\Traits\AuditLogger;



class AuditLogController extends Controller
{

    use AuditLogger;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('action'), fn($q) => $q->where('action', 'like', '%' . $request->action . '%'))
            ->orderBy('timestamp', 'desc')
            ->paginate(50);
    
        $users = User::orderBy('name')->get();

        $modules = [
            'appointments',
            'audit-logs',
            'backup',
            'billing-statements',
            'care-levels',
            'discharges',
            'evaluations',
            'incident-reports',
            'invoices',
            'medications',
            'notifications',
            'patients',
            'payments',
            'prescriptions',
            'progress-reports',
            'tasks',
            'therapy-sessions',
            'user-management',
        ];
    
        return view('admin.logs.index', compact('logs', 'users', 'modules'));
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
        //
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

    
        public function export(Request $request)
        {
            $format = $request->get('format', 'csv');
    
            $logs = AuditLog::with('user')
                ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id))
                ->when($request->filled('module'), fn($q) => $q->where('module', $request->module))
                ->when($request->filled('severity'), fn($q) => $q->where('severity', $request->severity))
                ->when($request->filled('from'), fn($q) => $q->where('timestamp', '>=', $request->from))
                ->when($request->filled('to'), fn($q) => $q->where('timestamp', '<=', $request->to))
                ->orderBy('timestamp', 'desc')
                ->get();
    
            if ($format === 'csv') {
                $csv = "User,Action,Module,Severity,IP,Timestamp\n";
                foreach ($logs as $log) {
                    $csv .= '"' . ($log->user->name ?? 'System') . '",';
                    $csv .= '"' . $log->action . '",';
                    $csv .= '"' . $log->module . '",';
                    $csv .= '"' . $log->severity . '",';
                    $csv .= '"' . ($log->ip_address ?? '-') . '",';
                    $csv .= '"' . $log->timestamp . "\"\n";
                }
    
                return Response::make($csv, 200, [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="audit_logs.csv"',
                ]);
            }
    
            // PDF export placeholder
            return back()->with('error', 'PDF export not implemented yet.');
        }
    
}
