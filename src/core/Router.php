<?php

namespace MotoristaCheck\Core;

class Router
{
    public static function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    public static function back(): never
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? APP_URL;
        header('Location: ' . $ref);
        exit;
    }

    public static function url(string $path = ''): string
    {
        return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
    }

    public static function asset(string $path): string
    {
        return self::url('public/assets/' . ltrim($path, '/'));
    }

    public static function current(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }
}
