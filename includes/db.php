<?php
declare(strict_types=1);
/**
 * DB compatibility shim (lowercase filename).
 * Some legacy server configs or includes may reference includes/db.php (lowercase).
 * This shim delegates to the canonical includes/DB.php file.
 */
require_once __DIR__ . '/DB.php';
