<?php
declare(strict_types=1);
/**
 * SaaSFlow Cache Helper
 *
 * Suporta dois backends:
 *   1. Redis  - se a extensão phpredis estiver instalada e REDIS_HOST estiver no .env
 *   2. Arquivo - fallback automático; armazena em logs/cache/
 *
 * Uso:
 *   Cache::get('platform_settings')
 *   Cache::set('platform_settings', $data, 300)
 *   Cache::delete('platform_settings')
 *   Cache::flush()
 */

class Cache {
    /** @var Redis|null */
    private static $redis = null;
    private static bool $redisAvailable = false;
    private static bool $initialized = false;
    private static string $cacheDir = '';

    /** Inicializa o backend de cache uma única vez. */
    private static function init(): void {
        if (self::$initialized) return;
        self::$initialized = true;

        // Diretório de cache em filesystem (fallback)
        self::$cacheDir = dirname(__DIR__, 2) . '/logs/cache';
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0750, true);
        }

        // Tentar conectar ao Redis se disponível
        if (extension_loaded('redis') && defined('REDIS_HOST') && !empty(REDIS_HOST)) {
            try {
                $r = new Redis();
                $connected = $r->connect(
                    REDIS_HOST,
                    defined('REDIS_PORT') ? (int)REDIS_PORT : 6379,
                    2.0 // timeout de 2s para não bloquear
                );
                if ($connected) {
                    if (defined('REDIS_PASSWORD') && !empty(REDIS_PASSWORD)) {
                        $r->auth(REDIS_PASSWORD);
                    }
                    if (defined('REDIS_DB')) {
                        $r->select((int)REDIS_DB);
                    }
                    $r->setOption(Redis::OPT_PREFIX, 'saasflow:');
                    self::$redis = $r;
                    self::$redisAvailable = true;
                }
            } catch (Throwable $e) {
                // Redis indisponível; silencioso — usa fallback de arquivo
            }
        }
    }

    /**
     * Lê um valor do cache.
     * @return mixed|null  null se expirado ou ausente
     */
    public static function get(string $key): mixed {
        self::init();

        if (self::$redisAvailable) {
            $val = self::$redis->get($key);
            if ($val === false) return null;
            return unserialize($val);
        }

        // Arquivo
        $file = self::filePath($key);
        if (!file_exists($file)) return null;

        $raw = file_get_contents($file);
        $data = unserialize($raw);

        if (!is_array($data) || !isset($data['expires_at'])) return null;
        if ($data['expires_at'] !== 0 && time() > $data['expires_at']) {
            @unlink($file);
            return null;
        }

        return $data['value'];
    }

    /**
     * Grava um valor no cache.
     * @param int $ttl  Segundos até expirar. 0 = nunca expira.
     */
    public static function set(string $key, mixed $value, int $ttl = 300): bool {
        self::init();

        if (self::$redisAvailable) {
            $serialized = serialize($value);
            if ($ttl > 0) {
                return (bool)self::$redis->setex($key, $ttl, $serialized);
            }
            return (bool)self::$redis->set($key, $serialized);
        }

        // Arquivo
        $data = [
            'expires_at' => $ttl > 0 ? time() + $ttl : 0,
            'value'      => $value,
        ];
        return (bool)file_put_contents(self::filePath($key), serialize($data), LOCK_EX);
    }

    /**
     * Remove uma chave do cache.
     */
    public static function delete(string $key): void {
        self::init();

        if (self::$redisAvailable) {
            self::$redis->del($key);
            return;
        }

        $file = self::filePath($key);
        if (file_exists($file)) @unlink($file);
    }

    /**
     * Limpa todo o cache do SaaSFlow.
     */
    public static function flush(): void {
        self::init();

        if (self::$redisAvailable) {
            // Flushes apenas as keys com o prefixo 'saasflow:'
            $keys = self::$redis->keys('*');
            if (!empty($keys)) self::$redis->del($keys);
            return;
        }

        // Arquivo
        $files = glob(self::$cacheDir . '/*.cache');
        if ($files) {
            foreach ($files as $f) @unlink($f);
        }
    }

    /** Retorna o caminho do arquivo de cache para uma chave. */
    private static function filePath(string $key): string {
        return self::$cacheDir . '/' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $key) . '.cache';
    }

    /** Retorna true se está usando Redis. */
    public static function isRedis(): bool {
        self::init();
        return self::$redisAvailable;
    }
}
