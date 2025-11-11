<?php

class Sanitizer
{
    public static function name(?string $value): string
    {
        if ($value === null) return '';

        $value = strip_tags($value);

        $value = trim($value);

        $value = preg_replace('/\s+/', ' ', $value);
        
        return $value;
    }

    public static function alphanumeric(?string $value): string
    {
        if ($value === null) return '';

        $value = preg_replace('/[^a-zA-Z0-9\s]/', '', $value);
        
        return trim($value);
    }

    public static function email(?string $value): string
    {
        if ($value === null) return '';
        
        return filter_var(trim($value), FILTER_SANITIZE_EMAIL);
    }

    public static function text(?string $value): string
    {
        if ($value === null) return '';
        
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    public static function integer($value): int
    {
        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    public static function float($value): float
    {
        return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }
}