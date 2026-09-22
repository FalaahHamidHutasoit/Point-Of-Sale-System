<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use RuntimeException;

/**
 * KAMELA Phase 4 - application-level database snapshot service.
 *
 * Backups are JSON snapshots generated only by this application and stored
 * outside the public web root. Restore never executes arbitrary SQL text.
 */
class BackupService
{
    private const FORMAT = 'KAMELA_BACKUP_V2';

    /** @var list<string> */
    private const TABLES = [
        // Parent/master tables first so restore inserts respect FK dependencies.
        'users',
        'kategori',
        'customer',
        'supplier',
        'barang',
        'penjualan',
        'detail_penjualan',
        'pembelian',
        'detail_pembelian',
        'penerimaan_barang',
        'detail_penerimaan_barang',
        'mutasi_stok',
        'demo_payments',
        'stock_opname',
        'stock_opname_detail',
        'audit_logs',
    ];

    /** @var list<string> */
    private const DELETE_ORDER = [
        // Reverse dependency order so a current-schema restore starts from a clean snapshot.
        'audit_logs',
        'demo_payments',
        'stock_opname_detail',
        'stock_opname',
        'mutasi_stok',
        'detail_penerimaan_barang',
        'penerimaan_barang',
        'detail_pembelian',
        'pembelian',
        'detail_penjualan',
        'penjualan',
        'barang',
        'kategori',
        'customer',
        'supplier',
        'users',
    ];

    private BaseConnection $db;
    private string $directory;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
        $this->directory = rtrim(WRITEPATH . 'backups', DIRECTORY_SEPARATOR);
        $this->ensureDirectory();
    }

    /**
     * @return array{filename:string,path:string,created_at:string,size:int,checksum:string,table_count:int,row_count:int}
     */
    public function create(string $reason = 'Manual backup'): array
    {
        $tables = [];
        $totalRows = 0;

        foreach (self::TABLES as $table) {
            if (!$this->db->tableExists($table)) {
                throw new RuntimeException(
                    'Schema database KAMELA belum lengkap. Tabel tidak ditemukan: ' . $table .
                    '. Sinkronkan database sebelum membuat backup.'
                );
            }

            $rows = $this->db->table($table)->get()->getResultArray();
            $tables[$table] = $rows;
            $totalRows += count($rows);
        }

        $payload = [
            'format' => self::FORMAT,
            'created_at' => date('c'),
            'reason' => mb_substr(trim($reason) ?: 'Manual backup', 0, 150),
            'database' => $this->db->getDatabase(),
            'tables' => $tables,
        ];

        $payloadJson = $this->encode($payload);
        $checksum = hash('sha256', $payloadJson);
        $document = [
            'checksum_algorithm' => 'sha256',
            'checksum' => $checksum,
            'payload' => $payload,
        ];

        $filename = 'kamela-backup-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.json';
        $path = $this->directory . DIRECTORY_SEPARATOR . $filename;
        $json = $this->encode($document, true);

        if (file_put_contents($path, $json, LOCK_EX) === false) {
            throw new RuntimeException('File backup tidak dapat ditulis.');
        }

        @chmod($path, 0600);

        return [
            'filename' => $filename,
            'path' => $path,
            'created_at' => $payload['created_at'],
            'size' => filesize($path) ?: strlen($json),
            'checksum' => $checksum,
            'table_count' => count($tables),
            'row_count' => $totalRows,
        ];
    }

    /**
     * @return list<array{filename:string,path:string,size:int,modified_at:string,valid:bool,checksum:?string,created_at:?string,reason:?string,row_count:int}>
     */
    public function list(): array
    {
        $items = [];
        foreach (glob($this->directory . DIRECTORY_SEPARATOR . 'kamela-backup-*.json') ?: [] as $path) {
            $filename = basename($path);
            try {
                $document = $this->readAndValidate($filename);
                $payload = $document['payload'];
                $rowCount = 0;
                foreach (($payload['tables'] ?? []) as $rows) {
                    if (is_array($rows)) {
                        $rowCount += count($rows);
                    }
                }
                $items[] = [
                    'filename' => $filename,
                    'path' => $path,
                    'size' => filesize($path) ?: 0,
                    'modified_at' => date('c', filemtime($path) ?: time()),
                    'valid' => true,
                    'checksum' => (string) $document['checksum'],
                    'created_at' => (string) ($payload['created_at'] ?? ''),
                    'reason' => (string) ($payload['reason'] ?? ''),
                    'row_count' => $rowCount,
                ];
            } catch (\Throwable $e) {
                $items[] = [
                    'filename' => $filename,
                    'path' => $path,
                    'size' => filesize($path) ?: 0,
                    'modified_at' => date('c', filemtime($path) ?: time()),
                    'valid' => false,
                    'checksum' => null,
                    'created_at' => null,
                    'reason' => null,
                    'row_count' => 0,
                ];
            }
        }

        usort($items, static fn(array $a, array $b): int => strcmp($b['filename'], $a['filename']));
        return $items;
    }

    public function pathForDownload(string $filename): string
    {
        $path = $this->safePath($filename);
        if (!is_file($path)) {
            throw new RuntimeException('Backup tidak ditemukan.');
        }
        // Validasi checksum sebelum file diberikan ke user.
        $this->readAndValidate($filename);
        return $path;
    }

    /**
     * Restore snapshot yang dibuat oleh aplikasi sendiri.
     *
     * @return array{tables:int,rows:int,backup_created_at:string}
     */
    public function restore(string $filename): array
    {
        $document = $this->readAndValidate($filename);
        $payload = $document['payload'];
        $tables = $payload['tables'] ?? null;

        if (!is_array($tables)) {
            throw new RuntimeException('Payload backup tidak memiliki data tabel yang valid.');
        }

        // Jangan pernah menerima nama tabel di luar whitelist, bahkan jika file dimodifikasi.
        foreach (array_keys($tables) as $table) {
            if (!in_array($table, self::TABLES, true)) {
                throw new RuntimeException('Backup mengandung tabel yang tidak diizinkan: ' . $table);
            }
        }

        // Snapshot harus lengkap untuk semua tabel KAMELA yang ada pada schema saat ini.
        // Ini sengaja menolak backup lama/parsial agar restore tidak mengosongkan tabel
        // baru (mis. pembayaran demo atau penerimaan barang) tanpa mengembalikannya.
        foreach (self::TABLES as $table) {
            if (!$this->db->tableExists($table)) {
                throw new RuntimeException(
                    'Schema database KAMELA saat ini tidak lengkap. Tabel tidak ditemukan: ' . $table . '.'
                );
            }
            if (!array_key_exists($table, $tables)) {
                throw new RuntimeException(
                    'Backup tidak lengkap untuk schema KAMELA saat ini. Tabel hilang: ' . $table .
                    '. Buat backup baru dari versi aplikasi terbaru.'
                );
            }
        }

        $restoredTables = 0;
        $restoredRows = 0;

        $this->db->transBegin();
        try {
            $this->db->query('SET FOREIGN_KEY_CHECKS = 0');

            foreach (self::DELETE_ORDER as $table) {
                if ($this->db->tableExists($table)) {
                    // DELETE adalah DML dan tetap berada di dalam transaction; TRUNCATE/emptyTable dapat implicit-commit di MySQL.
                    $this->db->query('DELETE FROM `' . $table . '`');
                }
            }

            // Insert parent -> child supaya snapshot juga tetap masuk akal saat FK dinyalakan kembali.
            foreach (self::TABLES as $table) {
                if (!array_key_exists($table, $tables) || !$this->db->tableExists($table)) {
                    continue;
                }

                $rows = $tables[$table];
                if (!is_array($rows)) {
                    throw new RuntimeException('Data tabel ' . $table . ' tidak valid.');
                }

                if ($rows !== []) {
                    foreach (array_chunk($rows, 250) as $chunk) {
                        if (!$this->db->table($table)->insertBatch($chunk)) {
                            throw new RuntimeException('Gagal mengembalikan tabel ' . $table . '.');
                        }
                    }
                }

                $restoredTables++;
                $restoredRows += count($rows);
            }

            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Database transaction restore gagal.');
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            try {
                $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
            } catch (\Throwable) {
                // Best effort, koneksi akan ditutup oleh lifecycle request.
            }
            $this->db->transRollback();
            throw $e;
        }

        return [
            'tables' => $restoredTables,
            'rows' => $restoredRows,
            'backup_created_at' => (string) ($payload['created_at'] ?? ''),
        ];
    }

    public function delete(string $filename): void
    {
        $path = $this->safePath($filename);
        if (!is_file($path)) {
            throw new RuntimeException('Backup tidak ditemukan.');
        }
        if (!unlink($path)) {
            throw new RuntimeException('Backup tidak dapat dihapus.');
        }
    }

    /**
     * @return array{checksum:string,payload:array<string,mixed>}
     */
    private function readAndValidate(string $filename): array
    {
        $path = $this->safePath($filename);
        if (!is_file($path)) {
            throw new RuntimeException('Backup tidak ditemukan.');
        }

        if ((filesize($path) ?: 0) > 100 * 1024 * 1024) {
            throw new RuntimeException('Ukuran backup melewati batas keamanan 100 MB.');
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new RuntimeException('Backup tidak dapat dibaca.');
        }

        $document = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($document) || !isset($document['checksum'], $document['payload']) || !is_array($document['payload'])) {
            throw new RuntimeException('Format backup tidak valid.');
        }

        $payload = $document['payload'];
        if (($payload['format'] ?? null) !== self::FORMAT) {
            throw new RuntimeException('Versi format backup tidak didukung.');
        }

        $expected = hash('sha256', $this->encode($payload));
        if (!hash_equals((string) $document['checksum'], $expected)) {
            throw new RuntimeException('Checksum backup tidak cocok. File mungkin rusak atau diubah.');
        }

        $tables = $payload['tables'] ?? null;
        if (!is_array($tables)) {
            throw new RuntimeException('Payload backup tidak memiliki data tabel yang valid.');
        }
        foreach (self::TABLES as $table) {
            if (!array_key_exists($table, $tables) || !is_array($tables[$table])) {
                throw new RuntimeException('Backup tidak lengkap untuk format V2. Tabel hilang/tidak valid: ' . $table . '.');
            }
        }

        return [
            'checksum' => (string) $document['checksum'],
            'payload' => $payload,
        ];
    }

    private function safePath(string $filename): string
    {
        $filename = basename($filename);
        if (!preg_match('/\Akamela-backup-\d{8}-\d{6}-[a-f0-9]{8}\.json\z/i', $filename)) {
            throw new RuntimeException('Nama file backup tidak valid.');
        }

        return $this->directory . DIRECTORY_SEPARATOR . $filename;
    }

    private function ensureDirectory(): void
    {
        if (!is_dir($this->directory) && !mkdir($this->directory, 0700, true) && !is_dir($this->directory)) {
            throw new RuntimeException('Direktori backup tidak dapat dibuat.');
        }
        @chmod($this->directory, 0700);
    }

    /** @param array<string,mixed> $data */
    private function encode(array $data, bool $pretty = false): string
    {
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR;
        if ($pretty) {
            $flags |= JSON_PRETTY_PRINT;
        }
        return json_encode($data, $flags);
    }
}
