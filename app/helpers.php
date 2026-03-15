<?php

use App\Models\SiteContent;

if (! function_exists('content')) {
    /**
     * Get editable site content by key. Returns default from config if not set in DB.
     */
    function content(string $key): string
    {
        return SiteContent::getForView($key);
    }
}

if (! function_exists('ordinal')) {
    /**
     * Return ordinal suffix for a number (e.g. 1 => "1st", 2 => "2nd").
     */
    function ordinal(int $n): string
    {
        $v = $n % 100;
        if ($v >= 11 && $v <= 13) {
            return $n . 'th';
        }
        $s = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
        return $n . ($s[$n % 10] ?? 'th');
    }
}

if (! function_exists('site_content_image')) {
    /**
     * Get public URL for an image content key (e.g. home.hero.image).
     */
    function site_content_image(string $key): ?string
    {
        return SiteContent::imageUrl($key);
    }
}
