<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Session\Handlers\BaseHandler;
use CodeIgniter\Session\Handlers\FileHandler;

class Session extends BaseConfig
{
    /** @var class-string<BaseHandler> */
    public string $driver = FileHandler::class;

    /** Cookie khusus KAMELA agar tidak mudah bentrok dengan app CI lain di localhost/domain yang sama. */
    public string $cookieName = 'kamela_session';

    /**
     * Batas absolut cookie session 8 jam.
     * AuthFilter menerapkan idle timeout yang lebih pendek: 30 menit.
     */
    public int $expiration = 28800;

    public string $savePath = WRITEPATH . 'session';

    /**
     * Tidak bind session ke IP karena user bisa berpindah Wi-Fi/hotspot.
     * Validasi client dilakukan dengan fingerprint User-Agent di AuthFilter.
     */
    public bool $matchIP = false;

    /** Regenerasi periodik session ID setiap 5 menit. */
    public int $timeToUpdate = 300;

    /** Session ID lama langsung dihancurkan setelah regenerasi. */
    public bool $regenerateDestroy = true;

    public ?string $DBGroup = null;

    public int $lockRetryInterval = 100_000;

    public int $lockMaxRetries = 300;
}
