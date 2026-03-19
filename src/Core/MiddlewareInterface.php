<?php
declare(strict_types=1);

namespace App\Core;

interface MiddlewareInterface {
    /**
     * Handle the incoming request.
     * Returns true to continue, or false/redirect to stop.
     */
    public function handle(): bool;
}
