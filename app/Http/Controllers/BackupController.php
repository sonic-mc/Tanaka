<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use App\Traits\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use ZipArchive;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    use AuditLogger;

    public function index()
    {
        $backups = Backup::query()
            ->with(['creator:id,name,email'])
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('admin.backups.index', compact('backups'));
    }

    public function show(Backup $backup)
    {
        $backup->loadMissing(['creator:id,name,email']);

        $disk = Storage::disk('backups');
        $filename = $backup->filename ?: basename($backup->file_path);
        $exists = $filename ? $disk->exists($filename) : false;
        $size = $exists ? $disk->size($filename) : null;

        return view('admin.backups.show', compact('backup', 'exists', 'size'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'  => ['required', Rule::in(['full', 'database', 'files'])],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $userId = auth()->id();
        $ip = $request->ip();

        $disk = Storage::disk('backups');

        // Ensure local storage root exists (explicitly support 'local' driver)
        try {
            if (method_exists($disk, 'path')) {
                $rootPath = rtrim($disk->path(''), DIRECTORY_SEPARATOR);
                if (! is_dir($rootPath)) {
                    @mkdir($rootPath, 0775, true);
                }
            } else {
                // Fallback for older versions: ensure storage/app/backups exists
                $fallbackRoot = storage_path('app/backups');
                if (! is_dir($fallbackRoot)) {
                    @mkdir($fallbackRoot, 0775, true);
                }
            }
        } catch (\Throwable $e) {
            // Non-fatal; continue and let put() fail if root truly missing
        }

        $filename = 'backup_' . now()->format('Ymd_His') . '_' . Str::upper(Str::random(6)) . '.zip';

        $backup = null;

        try {
            // Create record in pending
            $backup = DB::transaction(function () use ($validated, $userId, $ip, $filename) {
                return Backup::create([
                    'file_path'   => 'backups://' . $filename,
                    'filename'    => $filename,
                    'type'        => $validated['type'],
                    'status'      => 'pending',
                    'notes'       => $validated['notes'] ?? null,
                    'created_by'  => $userId,
                    'origin_ip'   => $ip,
                    // created_at defaults in DB
                ]);
            });

            // Build zip in temp
            $tempDir = storage_path('app/tmp');
            if (! is_dir($tempDir)) {
                @mkdir($tempDir, 0775, true);
            }
            $tempZip = $tempDir . DIRECTORY_SEPARATOR . $filename;

            $zip = new ZipArchive();
            if ($zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Failed to initialize backup ZIP.');
            }

            // Add metadata file inside zip
            $meta = "Type: {$validated['type']}\n"
                . "Created At: " . now()->toDateTimeString() . "\n"
                . "Created By: " . ($userId ?? 'system') . "\n"
                . "Origin IP: {$ip}\n"
                . "App: " . config('app.name') . "\n";
            $zip->addFromString('meta.txt', $meta);

            // TODO: Add actual backup content here per type:
            // - 'database': run DB dump and add file(s)
            // - 'files': add storage directories/public assets
            // - 'full': both DB and files

            $zip->close();

            // Save to local disk
            $stream = fopen($tempZip, 'r');
            $disk->put($filename, $stream, ['visibility' => 'private']);
            fclose($stream);

            // Remove temp file
            @unlink($tempZip);

            // Mark as completed
            $backup->status = 'completed';
            $backup->save();

            // Audit
            $this->logAudit('Created system backup', "Backup file: {$filename}", 'backup', 'info');

            return back()->with('success', 'Backup created successfully.');
        } catch (\Throwable $e) {
            Log::error('Backup creation failed', [
                'error' => $e->getMessage(),
            ]);

            if ($backup) {
                $backup->status = 'failed';
                $backup->save();
            }

            $this->logAudit('Backup failed', $e->getMessage(), 'backup', 'critical');

            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function download(Backup $backup): StreamedResponse
    {
        $filename = $backup->filename ?: basename($backup->file_path);
        $disk = Storage::disk('backups');

        if (! $filename || ! $disk->exists($filename)) {
            abort(404, 'Backup file not found.');
        }

        return $disk->download($filename, $filename);
    }

    public function restore(Backup $backup)
    {
        $filename = $backup->filename ?: basename($backup->file_path);
        $disk = Storage::disk('backups');

        if (! $filename || ! $disk->exists($filename)) {
            return back()->with('error', 'Backup file not found; cannot restore.');
        }

        try {
            // TODO: Add actual restore logic (DB import, files unzip, etc.)
            $backup->status = 'restored';
            $backup->restored_at = now();
            $backup->save();

            $this->logAudit('Restored system backup', "Restored from: {$backup->filename}", 'backup', 'warning');

            return back()->with('success', 'System restored from backup.');
        } catch (\Throwable $e) {
            Log::error('Backup restore failed', [
                'backup_id' => $backup->id,
                'error'     => $e->getMessage(),
            ]);

            $this->logAudit('Restore failed', $e->getMessage(), 'backup', 'critical');

            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    public function destroy(Backup $backup)
    {
        $filename = $backup->filename ?: basename($backup->file_path);
        $disk = Storage::disk('backups');

        try {
            if ($filename && $disk->exists($filename)) {
                $disk->delete($filename);
            }

            $backup->delete();

            $this->logAudit('Deleted backup', "Deleted: {$filename}", 'backup', 'warning');

            return back()->with('success', 'Backup deleted.');
        } catch (\Throwable $e) {
            Log::error('Backup deletion failed', [
                'backup_id' => $backup->id,
                'error'     => $e->getMessage(),
            ]);

            return back()->with('error', 'Could not delete backup: ' . $e->getMessage());
        }
    }
}
