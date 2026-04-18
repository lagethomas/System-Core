<?php
declare(strict_types=1);
/**
 * Auth compatibility shim (lowercase filename).
 * Some legacy server configs may reference includes/auth.php (lowercase).
 * This shim delegates to the canonical includes/helpers/Auth.php.
 */
require_once __DIR__ . '/helpers/Auth.php';
