<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class Settings
{
    public function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember(
            "setting:{$key}",
            now()->addHours(12),
            fn () => Setting::where('key', $key)->first()
        );

        if (! $setting) {
            return $default;
        }

        return $setting->typed_value;
    }

    public function set(
        string $key,
        mixed $value,
        string $type = 'string',
        string $group = 'general',
        ?string $description = null
    ): Setting {
        $storedValue = match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value,
        };

        $setting = Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storedValue,
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ]
        );

        Cache::forget("setting:{$key}");

        return $setting;
    }

    public function all(?string $group = null): array
    {
        $query = Setting::query();

        if ($group !== null) {
            $query->where('group', $group);
        }

        return $query
            ->get()
            ->mapWithKeys(function (Setting $setting) {
                return [
                    $setting->key => $setting->typed_value,
                ];
            })
            ->toArray();
    }

    public function forget(string $key): void
    {
        Cache::forget("setting:{$key}");
    }

    public function forgetAll(): void
    {
        Setting::query()
            ->pluck('key')
            ->each(
                fn ($key) => Cache::forget("setting:{$key}")
            );
    }
}