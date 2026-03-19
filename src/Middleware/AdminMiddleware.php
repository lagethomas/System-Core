<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\MiddlewareInterface;
use Auth;

class AdminMiddleware implements MiddlewareInterface {
    public function handle(): bool {
        Auth::requireAdmin();
        return true;
    }
}
