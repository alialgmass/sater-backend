<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Backup Tenants Command
 * 
 * Creates backups of all tenant databases and file storage.
 * Each tenant gets a separate backup file for easy restoration.
 */
class BackupTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:backup 
                            {--tenant= : Backup specific tenant by ID}
                            {--only-db : Backup only databases, skip files}
                            {--only-files : Backup only files, skip databases}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup all tenant databases and file storage';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting tenant backup process...');
        
        $tenantId = $this->option('tenant');
        $onlyDb = $this->option('only-db');
        $onlyFiles = $this->option('only-files');
        
        if ($tenantId) {
            $tenants = Tenant::where('id', $tenantId)->get();
            if ($tenants->isEmpty()) {
                $this->error("Tenant {$tenantId} not found.");
                return self::FAILURE;
            }
        } else {
            $tenants = Tenant::active()->get();
        }
        
        $this->info("Found {$tenants->count()} tenant(s) to backup.");
        
        $successCount = 0;
        $failCount = 0;
        
        foreach ($tenants as $tenant) {
            try {
                $this->newLine();
                $this->info("Backing up tenant: {$tenant->store_name} ({$tenant->id})");
                
                // Backup database
                if (!$onlyFiles) {
                    $this->backupTenantDatabase($tenant);
                }
                
                // Backup files
                if (!$onlyDb) {
                    $this->backupTenantFiles($tenant);
                }
                
                $successCount++;
                $this->info("✓ Successfully backed up tenant: {$tenant->store_name}");
                
            } catch (\Exception $e) {
                $failCount++;
                $this->error("✗ Failed to backup tenant {$tenant->store_name}: {$e->getMessage()}");
                
                \Log::error('Tenant backup failed', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        $this->newLine();
        $this->info("Backup completed!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Successful', $successCount],
                ['Failed', $failCount],
            ]
        );
        
        return $failCount > 0 ? self::FAILURE : self::SUCCESS;
    }
    
    /**
     * Backup tenant database.
     */
    protected function backupTenantDatabase(Tenant $tenant): void
    {
        $this->info("  → Backing up database...");
        
        // Get tenant database name
        $databaseName = "tenant_{$tenant->id}";
        
        // Create backup directory
        $backupDir = storage_path("app/backups/tenants/{$tenant->id}/databases");
        File::ensureDirectoryExists($backupDir);
        
        // Generate backup filename with timestamp
        $filename = $databaseName . '_' . now()->format('Y-m-d_His') . '.sql';
        $backupPath = $backupDir . '/' . $filename;
        
        // Perform database dump
        $this->performDatabaseDump($databaseName, $backupPath);
        
        // Compress the backup
        $compressedPath = $this->compressBackup($backupPath);
        
        // Upload to remote storage (if configured)
        $this->uploadToRemoteStorage($compressedPath, "tenants/{$tenant->id}/databases/");
        
        $this->info("  ✓ Database backed up: {$filename}");
    }
    
    /**
     * Perform database dump using mysqldump or pg_dump.
     */
    protected function performDatabaseDump(string $database, string $backupPath): void
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");
        
        if ($config['driver'] === 'mysql') {
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s --port=%s %s > %s',
                escapeshellarg($config['username']),
                escapeshellarg($config['password']),
                escapeshellarg($config['host']),
                escapeshellarg($config['port']),
                escapeshellarg($database),
                escapeshellarg($backupPath)
            );
            
            exec($command, $output, $exitCode);
            
            if ($exitCode !== 0) {
                throw new \Exception("mysqldump failed with exit code {$exitCode}");
            }
        } elseif ($config['driver'] === 'pgsql') {
            $command = sprintf(
                'PGPASSWORD=%s pg_dump --host=%s --port=%s --username=%s %s > %s',
                escapeshellarg($config['password']),
                escapeshellarg($config['host']),
                escapeshellarg($config['port']),
                escapeshellarg($config['username']),
                escapeshellarg($database),
                escapeshellarg($backupPath)
            );
            
            exec($command, $output, $exitCode);
            
            if ($exitCode !== 0) {
                throw new \Exception("pg_dump failed with exit code {$exitCode}");
            }
        } else {
            throw new \Exception("Unsupported database driver: {$config['driver']}");
        }
    }
    
    /**
     * Backup tenant files.
     */
    protected function backupTenantFiles(Tenant $tenant): void
    {
        $this->info("  → Backing up files...");
        
        $tenantStoragePath = storage_path("app/tenants/{$tenant->id}");
        
        if (!File::exists($tenantStoragePath)) {
            $this->warn("  ⚠ No files found for tenant {$tenant->store_name}");
            return;
        }
        
        // Create backup directory
        $backupDir = storage_path("app/backups/tenants/{$tenant->id}/files");
        File::ensureDirectoryExists($backupDir);
        
        // Generate backup filename
        $filename = 'files_' . now()->format('Y-m-d_His') . '.zip';
        $backupPath = $backupDir . '/' . $filename;
        
        // Create ZIP archive
        $this->createZipArchive($tenantStoragePath, $backupPath);
        
        // Upload to remote storage
        $this->uploadToRemoteStorage($backupPath, "tenants/{$tenant->id}/files/");
        
        $this->info("  ✓ Files backed up: {$filename}");
    }
    
    /**
     * Create ZIP archive of directory.
     */
    protected function createZipArchive(string $source, string $destination): void
    {
        if (!extension_loaded('zip')) {
            throw new \Exception('ZIP extension is not loaded');
        }
        
        $zip = new \ZipArchive();
        
        if (!$zip->open($destination, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            throw new \Exception("Failed to create ZIP archive: {$destination}");
        }
        
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($source) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        
        $zip->close();
    }
    
    /**
     * Compress backup file with gzip.
     */
    protected function compressBackup(string $filePath): string
    {
        if (!extension_loaded('zlib')) {
            return $filePath; // Skip compression if zlib not available
        }
        
        $compressedPath = $filePath . '.gz';
        
        $context = stream_context_create();
        $infile = fopen($filePath, 'rb');
        $outfile = fopen($compressedPath, 'wb', false, null, $context);
        
        stream_copy_to_stream($infile, $outfile);
        
        fclose($infile);
        fclose($outfile);
        
        // Remove uncompressed file
        unlink($filePath);
        
        return $compressedPath;
    }
    
    /**
     * Upload backup to remote storage (S3, etc.).
     */
    protected function uploadToRemoteStorage(string $filePath, string $prefix): void
    {
        if (!config('filesystems.disks.s3.key')) {
            $this->warn("  ⚠ S3 not configured, keeping backup locally");
            return;
        }
        
        $filename = basename($filePath);
        $remotePath = $prefix . $filename;
        
        Storage::disk('s3')->put(
            $remotePath,
            File::get($filePath),
            'public'
        );
        
        $this->info("  ✓ Uploaded to S3: {$remotePath}");
    }
}
