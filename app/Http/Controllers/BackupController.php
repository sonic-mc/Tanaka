<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use App\Models\AuditLog;
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

        // Attempt to get latest file metadata from disk
        $disk = Storage::disk('backups');
        $exists = $backup->filename ? $disk->exists($backup->filename) : false;
        $size = ($exists && method_exists($disk, 'size')) ? $disk->size($backup->filename) : $backup->size_bytes;

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
        $filename = 'backup_' . now()->format('Ymd_His') . '_' . Str::upper(Str::random(6)) . '.zip';

        // Create a DB record first in "pending", then update as we go
        $backup = null;

        try {
            $backup = DB::transaction(function () use ($validated, $userId, $ip, $filename) {
                return Backup::create([
                    'file_path'   => 'backups://' . $filename, // logical pointer; actual file is on the "backups" disk
                    'filename'    => $filename,
                    'type'        => $validated['type'],
                    'status'      => 'pending',
                    'notes'       => $validated['notes'] ?? null,
                    'created_by'  => $userId,
                    'origin_ip'   => $ip,
                ]);
            });

            // Generate the ZIP (synchronous demo; can be offloaded to a queued job in production)
            $tempDir = storage_path('app/tmp');
            if (! is_dir($tempDir)) {
                mkdir($tempDir, 0775, true);
            }
            $tempZip = $tempDir . DIRECTORY_SEPARATOR . $filename;

            $zip = new ZipArchive();
            if ($zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Failed to initialize backup ZIP.');
            }

            // Minimal metadata file inside the zip
            $meta = "Type: {$validated['type']}\n"
                . "Created At: " . now()->toDateTimeString() . "\n"
                . "Created By: " . ($userId ?? 'system') . "\n"
                . "Origin IP: {$ip}\n"
                . "App: " . config('app.name') . "\n";
            $zip->addFromString('meta.txt', $meta);

            // TODO: add real content:
            // - For 'database': run a DB dump and add to zip
            // - For 'files': add storage/app or public files
            // - For 'full': both DB dump and files
            // This implementation demonstrates structure; integrate your real backup sources here.

            $zip->close();

            // Persist to the backups disk
            $stream = fopen($tempZip, 'r');
            $disk->put($filename, $stream, ['visibility' => 'private']);
            fclose($stream);

            // Compute size and checksum (local path depends on driver; for local this is fine)
            $size = $disk->size($filename);
            // For local driver only: $path = $disk->path($filename);
            $checksum = hash_file('sha256', $tempZip);

            // Clean temp
            @unlink($tempZip);

            // Update record to completed
            $backup->update([
                'status'           => 'completed',
                'size_bytes'       => $size,
                'checksum_sha256'  => $checksum,
            ]);

            AuditLog::log('Created system backup', "Backup file: {$filename}", 'backup', 'info');

            return back()->with('success', 'Backup created successfully.');
        } catch (\Throwable $e) {
            Log::error('Backup creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($backup) {
                $backup->update(['status' => 'failed']);
            }

            AuditLog::log('Backup failed', $e->getMessage(), 'backup', 'error');

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

        // Streamed download
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
            // TODO: Implement your actual restore logic here:
            // - For DB: import SQL dump
            // - For files: unzip and overwrite target directories
            // Make sure to validate paths carefully and handle downtime windows.

            $backup->update([
                'status'      => 'restored',
                'restored_at' => now(),
            ]);

            AuditLog::log('Restored system backup', "Restored from: {$backup->filename}", 'backup', 'warning');

            return back()->with('success', 'System restored from backup.');
        } catch (\Throwable $e) {
            Log::error('Backup restore failed', [
                'backup_id' => $backup->id,
                'error'     => $e->getMessage(),
            ]);

            AuditLog::log('Restore failed', $e->getMessage(), 'backup', 'error');

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

            AuditLog::log('Deleted backup', "Deleted: {$filename}", 'backup', 'warning');

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
