<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting value by key with an optional default.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        try {
            if (!Schema::hasTable('site_settings')) {
                return $default;
            }

            $record = static::where('key', $key)->first();

            return $record !== null ? $record->value : $default;
        } catch (\Throwable) {
            return $default;
        }
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
        );
    }

    /**
     * Check whether the public Birthday Survey is currently enabled (defaults to false / OFF).
     */
    public static function isBirthdaySurveyEnabled(): bool
    {
        $val = static::getValue('birthday_survey_enabled', '0');

        return in_array((string) $val, ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * Enable or disable the public Birthday Survey.
     */
    public static function setBirthdaySurveyEnabled(bool $enabled): void
    {
        static::setValue('birthday_survey_enabled', $enabled ? '1' : '0');
    }
}
