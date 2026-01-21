<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemSettingsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SystemSettingsController extends Controller
{
    protected SystemSettingsService $settingsService;

    public function __construct(SystemSettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Get all system settings
     */
    public function index(Request $request): JsonResponse
    {
        $category = $request->query('category');
        
        $settings = $this->settingsService->all($category);

        return response()->json([
            'success' => true,
            'message' => 'System settings retrieved successfully',
            'data' => ['settings' => $settings],
        ]);
    }

    /**
     * Get public settings (for students/teachers)
     */
    public function publicSettings(): JsonResponse
    {
        $settings = $this->settingsService->getPublicSettings();

        return response()->json([
            'success' => true,
            'data' => ['settings' => $settings],
        ]);
    }

    /**
     * Get a single setting by key
     */
    public function show(string $key): JsonResponse
    {
        $value = $this->settingsService->get($key);

        if ($value === null) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'key' => $key,
                'value' => $value,
            ],
        ]);
    }

    /**
     * Update a single setting
     */
    public function update(Request $request, string $key): JsonResponse
    {
        $request->validate([
            'value' => 'required',
            'type' => 'sometimes|string|in:string,number,boolean,json,date',
            'category' => 'sometimes|string',
            'description' => 'sometimes|string',
            'is_public' => 'sometimes|boolean',
        ]);

        $setting = $this->settingsService->set(
            $key,
            $request->input('value'),
            $request->input('type', 'string'),
            $request->input('category', 'general')
        );

        if ($request->has('description')) {
            $setting->update(['description' => $request->input('description')]);
        }

        if ($request->has('is_public')) {
            $setting->update(['is_public' => $request->boolean('is_public')]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Setting updated successfully',
            'data' => ['setting' => $setting],
        ]);
    }

    /**
     * Bulk update settings
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.value' => 'required',
            'settings.*.type' => 'sometimes|string|in:string,number,boolean,json,date',
            'settings.*.category' => 'sometimes|string',
        ]);

        $this->settingsService->bulkUpdate($request->input('settings'));

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
        ]);
    }

    /**
     * Delete a setting
     */
    public function destroy(string $key): JsonResponse
    {
        $deleted = $this->settingsService->delete($key);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Setting deleted successfully',
        ]);
    }

    /**
     * Initialize default settings
     */
    public function initializeDefaults(): JsonResponse
    {
        $this->settingsService->initializeDefaults();

        return response()->json([
            'success' => true,
            'message' => 'Default settings initialized successfully',
        ]);
    }

    /**
     * Clear settings cache
     */
    public function clearCache(): JsonResponse
    {
        $this->settingsService->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Settings cache cleared successfully',
        ]);
    }

    /**
     * Toggle maintenance mode
     */
    public function toggleMaintenanceMode(Request $request): JsonResponse
    {
        $request->validate([
            'enabled' => 'required|boolean',
            'message' => 'sometimes|string',
        ]);

        $this->settingsService->set('maintenance_mode', $request->boolean('enabled'), 'boolean', 'system');

        if ($request->has('message')) {
            $this->settingsService->set('maintenance_message', $request->input('message'), 'string', 'system');
        }

        return response()->json([
            'success' => true,
            'message' => 'Maintenance mode updated successfully',
            'data' => [
                'enabled' => $request->boolean('enabled'),
            ],
        ]);
    }
}

