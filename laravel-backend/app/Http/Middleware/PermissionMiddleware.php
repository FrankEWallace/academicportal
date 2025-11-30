<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
                'error_code' => 'UNAUTHENTICATED'
            ], 401);
        }

        $user = $request->user();
        
        // Check if user account is active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'User account is inactive',
                'error_code' => 'USER_INACTIVE'
            ], 401);
        }

        // Permission-based access control
        $hasPermission = $this->checkPermission($user, $permission);
        
        if (!$hasPermission) {
            return response()->json([
                'success' => false,
                'message' => "Access denied. Required permission: {$permission}",
                'error_code' => 'PERMISSION_DENIED',
                'user_role' => $user->role,
                'required_permission' => $permission
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if user has specific permission based on role and action
     */
    private function checkPermission($user, string $permission): bool
    {
        $rolePermissions = [
            'admin' => [
                'users.create', 'users.read', 'users.update', 'users.delete',
                'courses.create', 'courses.read', 'courses.update', 'courses.delete',
                'students.create', 'students.read', 'students.update', 'students.delete',
                'teachers.create', 'teachers.read', 'teachers.update', 'teachers.delete',
                'departments.create', 'departments.read', 'departments.update', 'departments.delete',
                'enrollments.create', 'enrollments.read', 'enrollments.update', 'enrollments.delete',
                'attendance.create', 'attendance.read', 'attendance.update', 'attendance.delete',
                'grades.create', 'grades.read', 'grades.update', 'grades.delete',
                'fees.create', 'fees.read', 'fees.update', 'fees.delete',
                'announcements.create', 'announcements.read', 'announcements.update', 'announcements.delete',
                'dashboard.admin'
            ],
            'teacher' => [
                'courses.read', 'students.read', 
                'attendance.create', 'attendance.read', 'attendance.update',
                'grades.create', 'grades.read', 'grades.update',
                'announcements.read',
                'dashboard.teacher'
            ],
            'student' => [
                'courses.read', 'enrollments.create',
                'attendance.read', 'grades.read', 'fees.read',
                'announcements.read',
                'dashboard.student',
                'profile.read', 'profile.update'
            ]
        ];

        $userPermissions = $rolePermissions[$user->role] ?? [];
        
        return in_array($permission, $userPermissions);
    }
}
