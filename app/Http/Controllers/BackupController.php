<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\AuditLogger;
use App\Models\Backup;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;


class BackupController extends Controller
{

    use AuditLogger;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $backups = Backup::orderByDesc('created_at')->paginate(30);
        return view('admin.backups.index', compact('backups'));
    }
    
    public function store(Request $request)
    {
        // Simulate or generate a backup file path
        $filename = 'backup_' . now()->format('Ymd_His') . '.zip';
        $filePath = storage_path('app/backups/' . $filename); // or wherever your backups are stored
    
        Backup::create([
            'file_path'   => $filePath,
            'filename'    => $filename,
            'type'        => $request->type ?? 'full',
            'status'      => 'completed',
            'notes'       => $request->notes,
            'created_by'  => auth()->id(),
            'origin_ip'   => $request->ip(),
        ]);
    
        AuditLog::log('Created system backup', "Backup file: $filename", 'backup', 'info');
    
        return back()->with('success', 'Backup created successfully.');
    }
    
    
    public function restore($id)
    {
        $backup = Backup::findOrFail($id);
    
        // Restore logic here...
    
        $backup->update(['status' => 'restored']);
    
        AuditLog::log('Restored system backup', "Restored from: {$backup->filename}", 'backup', 'warning');
    
        return back()->with('success', 'System restored from backup.');
    }
    
}
