<?php
declare(strict_types=1);
/**
 * Cache compatibility shim.
 * This file exists to maintain backward compatibility for any legacy code or
 * server deployments that may reference includes/cache.php directly.
 * The actual implementation lives in includes/helpers/Cache.php.
 */
require_once __DIR__ . '/helpers/Cache.php';
