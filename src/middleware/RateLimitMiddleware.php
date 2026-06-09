<?php

namespace MotoristaCheck\Middleware;

use MotoristaCheck\Helpers\Security;

class RateLimitMiddleware
{
    public static function check(string $key, int $maxAttempts = 5, int $timeWindow = 300): bool
    {
        return Security::limitSession($key, $maxAttempts, $timeWindow);
    }

    public static function checkOrDie(string $key, int $maxAttempts = 5, int $timeWindow = 300): void
    {
        if (!self::check($key, $maxAttempts, $timeWindow)) {
            http_response_code(429);
            die(json_encode(['error' => 'Muitas tentativas. Aguarde alguns minutos.']));
        }
    }
}
