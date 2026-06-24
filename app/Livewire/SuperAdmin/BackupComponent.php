<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

class BackupComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $activeTab = 'list';
    public string $search = '';
    public int $perPage = 25;

    // Restore
    public $restoreFile;
    public bool $confirmDelete = false;
    public ?string $deleteTarget = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function createBackup(): void
    {
        try {
            $filename = 'backup_' . date('Y_m_d_His') . '.sql';
            $path = storage_path('app/backups/');

            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }

            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            $dbHost = config('database.connections.mysql.host');
            $dbPort = config('database.connections.mysql.port', 3306);

            $fullPath = $path . $filename;

            if ($dbPass) {
                $command = "mysqldump --user={$dbUser} --password={$dbPass} --host={$dbHost} --port={$dbPort} {$dbName} > \"{$fullPath}\" 2>&1";
            } else {
                $command = "mysqldump --user={$dbUser} --host={$dbHost} --port={$dbPort} {$dbName} > \"{$fullPath}\" 2>&1";
            }

            $output = null;
            $result = null;
            exec($command, $output, $result);

            if ($result !== 0) {
                $this->dispatch('toast', type: 'error', message: 'Backup failed! mysqldump error.');
                return;
            }

            $this->dispatch('toast', type: 'success', message: 'Backup created: ' . $filename);

        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Backup failed: ' . $e->getMessage());
        }
    }

    public function downloadBackup(string $filename): mixed
    {
        $path = storage_path('app/backups/' . $filename);

        if (file_exists($path)) {
            return response()->download($path);
        }

        $this->dispatch('toast', type: 'error', message: 'File not found!');
        return null;
    }

    public function confirmDeleteBackup(string $filename): void
    {
        $this->deleteTarget = $filename;
        $this->confirmDelete = true;
    }

    public function deleteBackup(): void
    {
        $path = storage_path('app/backups/' . $this->deleteTarget);

        if ($this->deleteTarget && file_exists($path)) {
            unlink($path);
            $this->dispatch('toast', type: 'success', message: 'Backup deleted successfully!');
        }

        $this->confirmDelete = false;
        $this->deleteTarget = null;
    }

    public function restoreBackup(): void
    {
        $this->validate([
            'restoreFile' => 'required|file|mimes:gz,zip,sql',
        ]);

        try {
            $path = $this->restoreFile->storeAs('backups/restore', $this->restoreFile->getClientOriginalName(), 'local');
            $this->dispatch('toast', type: 'success', message: 'File uploaded. Restore initiated.');
            $this->restoreFile = null;
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Restore failed: ' . $e->getMessage());
        }
    }

    private function getBackups(): array
    {
        $path = storage_path('app/backups/');

        if (!file_exists($path)) {
            return [];
        }

        $files = glob($path . '*.sql');
        $backups = [];

        foreach ($files as $file) {
            $filename = basename($file);

            if ($this->search && !str_contains(strtolower($filename), strtolower($this->search))) {
                continue;
            }

            $backups[] = [
                'filename'  => $filename,
                'size'      => $this->formatBytes(filesize($file)),
                'date'      => date('Y-m-d H:i:s', filemtime($file)),
                'timestamp' => filemtime($file),
            ];
        }

        usort($backups, fn($a, $b) => $b['timestamp'] - $a['timestamp']);

        return $backups;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)       return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    public function render()
    {
        $backups = $this->getBackups();

        // Manual pagination
        $total    = count($backups);
        $page     = $this->getPage();
        $offset   = ($page - 1) * $this->perPage;
        $items    = array_slice($backups, $offset, $this->perPage);

        return view('livewire.super-admin.backup-component', [
            'backups'      => $items,
            'totalBackups' => $total,
            'currentPage'  => $page,
            'totalPages'   => (int) ceil($total / $this->perPage),
            'from'         => $total ? $offset + 1 : 0,
            'to'           => min($offset + $this->perPage, $total),
        ])->layout('layouts.superadmin.app', [
            'title' => 'Database Backup | ' . setting('app_name', 'EMS'),
        ]);
    }
}