<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SystemSettingsService
{
    protected const CACHE_KEY_PREFIX = 'system_setting_';
    protected const CACHE_TTL = 3600; // 1 hour

    /**
     * Get a setting value by key
     */
    public function get(string $key, $default = null)
    {
        $cacheKey = self::CACHE_KEY_PREFIX . $key;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default) {
            $setting = SystemSetting::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return $setting->getTypedValue();
        });
    }

    /**
     * Set a setting value
     */
    public function set(string $key, $value, string $type = 'string', string $category = 'general'): SystemSetting
    {
        $setting = SystemSetting::firstOrNew(['key' => $key]);
        $setting->category = $category;
        $setting->type = $type;
        $setting->setTypedValue($value);
        $setting->save();

        // Clear cache
        Cache::forget(self::CACHE_KEY_PREFIX . $key);
        Cache::forget('system_settings_all');

        Log::info("System setting updated: {$key} = {$value}");

        return $setting;
    }

    /**
     * Get all settings, optionally filtered by category
     */
    public function all(?string $category = null): array
    {
        $query = SystemSetting::query();

        if ($category) {
            $query->where('category', $category);
        }

        return $query->get()->map(function ($setting) {
            return [
                'key' => $setting->key,
                'value' => $setting->getTypedValue(),
                'category' => $setting->category,
                'type' => $setting->type,
                'description' => $setting->description,
                'is_public' => $setting->is_public,
            ];
        })->keyBy('key')->toArray();
    }

    /**
     * Get public settings (accessible to students/teachers)
     */
    public function getPublicSettings(): array
    {
        return Cache::remember('system_settings_public', self::CACHE_TTL, function () {
            return SystemSetting::where('is_public', true)
                ->get()
                ->map(function ($setting) {
                    return [
                        'key' => $setting->key,
                        'value' => $setting->getTypedValue(),
                        'category' => $setting->category,
                    ];
                })
                ->keyBy('key')
                ->toArray();
        });
    }

    /**
     * Delete a setting
     */
    public function delete(string $key): bool
    {
        $setting = SystemSetting::where('key', $key)->first();

        if (!$setting) {
            return false;
        }

        $setting->delete();

        // Clear cache
        Cache::forget(self::CACHE_KEY_PREFIX . $key);
        Cache::forget('system_settings_all');

        Log::info("System setting deleted: {$key}");

        return true;
    }

    /**
     * Bulk update settings
     */
    public function bulkUpdate(array $settings): void
    {
        foreach ($settings as $key => $data) {
            $this->set(
                $key,
                $data['value'],
                $data['type'] ?? 'string',
                $data['category'] ?? 'general'
            );

            if (isset($data['description'])) {
                SystemSetting::where('key', $key)->update([
                    'description' => $data['description'],
                    'is_public' => $data['is_public'] ?? false,
                ]);
            }
        }

        Cache::forget('system_settings_all');
    }

    /**
     * Clear all settings cache
     */
    public function clearCache(): void
    {
        SystemSetting::all()->each(function ($setting) {
            Cache::forget(self::CACHE_KEY_PREFIX . $setting->key);
        });

        Cache::forget('system_settings_all');
        Cache::forget('system_settings_public');

        Log::info('System settings cache cleared');
    }

    /**
     * Initialize default settings
     */
    public function initializeDefaults(): void
    {
        $defaults = [
            // General Settings
            'app_name' => [
                'value' => 'Academic Nexus Portal',
                'type' => 'string',
                'category' => 'general',
                'description' => 'Application name',
                'is_public' => true,
            ],
            'app_timezone' => [
                'value' => 'UTC',
                'type' => 'string',
                'category' => 'general',
                'description' => 'Application timezone',
                'is_public' => false,
            ],
            'maintenance_mode' => [
                'value' => false,
                'type' => 'boolean',
                'category' => 'system',
                'description' => 'Enable maintenance mode',
                'is_public' => true,
            ],
            'maintenance_message' => [
                'value' => 'System is under maintenance. Please check back later.',
                'type' => 'string',
                'category' => 'system',
                'description' => 'Maintenance mode message',
                'is_public' => true,
            ],

            // Academic Settings
            'max_course_enrollment' => [
                'value' => 30,
                'type' => 'number',
                'category' => 'academic',
                'description' => 'Maximum students per course',
                'is_public' => false,
            ],
            'min_credits_per_semester' => [
                'value' => 12,
                'type' => 'number',
                'category' => 'academic',
                'description' => 'Minimum credits required per semester',
                'is_public' => true,
            ],
            'max_credits_per_semester' => [
                'value' => 21,
                'type' => 'number',
                'category' => 'academic',
                'description' => 'Maximum credits allowed per semester',
                'is_public' => true,
            ],
            'gpa_scale' => [
                'value' => 4.0,
                'type' => 'number',
                'category' => 'academic',
                'description' => 'GPA scale (e.g., 4.0 or 5.0)',
                'is_public' => true,
            ],

            // Email Settings
            'email_from_name' => [
                'value' => 'Academic Nexus',
                'type' => 'string',
                'category' => 'email',
                'description' => 'Email sender name',
                'is_public' => false,
            ],
            'email_from_address' => [
                'value' => 'noreply@academicnexus.edu',
                'type' => 'string',
                'category' => 'email',
                'description' => 'Email sender address',
                'is_public' => false,
            ],
            'email_notifications_enabled' => [
                'value' => true,
                'type' => 'boolean',
                'category' => 'email',
                'description' => 'Enable email notifications',
                'is_public' => false,
            ],

            // SMS Settings
            'sms_enabled' => [
                'value' => false,
                'type' => 'boolean',
                'category' => 'sms',
                'description' => 'Enable SMS notifications',
                'is_public' => false,
            ],
            'sms_provider' => [
                'value' => 'twilio',
                'type' => 'string',
                'category' => 'sms',
                'description' => 'SMS provider (twilio, nexmo, etc.)',
                'is_public' => false,
            ],

            // Feature Toggles
            'enable_online_payment' => [
                'value' => false,
                'type' => 'boolean',
                'category' => 'features',
                'description' => 'Enable online payment gateway',
                'is_public' => true,
            ],
            'enable_course_waitlist' => [
                'value' => true,
                'type' => 'boolean',
                'category' => 'features',
                'description' => 'Enable course waitlist system',
                'is_public' => true,
            ],
            'enable_student_feedback' => [
                'value' => true,
                'type' => 'boolean',
                'category' => 'features',
                'description' => 'Enable student feedback forms',
                'is_public' => true,
            ],
        ];

        foreach ($defaults as $key => $data) {
            if (!SystemSetting::where('key', $key)->exists()) {
                $setting = new SystemSetting([
                    'key' => $key,
                    'category' => $data['category'],
                    'type' => $data['type'],
                    'description' => $data['description'],
                    'is_public' => $data['is_public'],
                ]);
                $setting->setTypedValue($data['value']);
                $setting->save();
            }
        }

        $this->clearCache();
        Log::info('Default system settings initialized');
    }
}
