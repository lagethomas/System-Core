<?php
declare(strict_types=1);

namespace PHPMailer\PHPMailer;

/**
 * OAuthTokenProvider interface.
 * Satisfies dependencies for PHPMailer OAuth authentication.
 */
interface OAuthTokenProvider
{
    /**
     * Get OAuth2 access token.
     *
     * @return string
     */
    public function getOauth64();
}
