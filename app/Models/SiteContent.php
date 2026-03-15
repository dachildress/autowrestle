<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteContent extends Model
{
    protected $table = 'site_content';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['key', 'type', 'value'];

    /**
     * Get content value for a key. Returns DB value if set, otherwise config default.
     */
    public static function get(string $key): mixed
    {
        $sections = config('site_content.sections', []);
        $config = $sections[$key] ?? null;
        $default = $config['default'] ?? '';

        $row = static::find($key);
        if ($row && $row->value !== null && $row->value !== '') {
            return $row->value;
        }

        return $default;
    }

    /**
     * Get content for use in Blade: {{ content('home.hero.title') }}
     */
    public static function getForView(string $key): string
    {
        $value = static::get($key);
        if ($value === null) {
            return '';
        }
        return (string) $value;
    }

    /**
     * Get URL for an image content key (for use in img src).
     */
    public static function imageUrl(string $key): ?string
    {
        $path = static::get($key);
        if (!$path) {
            return null;
        }
        return \Illuminate\Support\Facades\Storage::url($path);
    }
}
