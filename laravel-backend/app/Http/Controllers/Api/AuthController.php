<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Login user and return access token
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:admin,student,teacher',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)
                   ->where('role', $request->role)
                   ->where('is_active', true)
                   ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Revoke all existing tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Load role-specific data only if relationship exists
        $userData = $user;
        if (in_array($user->role, ['student', 'teacher'])) {
            $userData = $user->load($user->role);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $userData,
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    /**
     * Logout user (Revoke token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get authenticated user with detailed information
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        // Load role-specific data only if relationship exists
        $userData = $user;
        if (in_array($user->role, ['student', 'teacher'])) {
            $userData = $user->load($user->role);
        }
        
        // Get user permissions based on role
        $permissions = $this->getUserPermissions($user->role);
        
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $userData,
                'permissions' => $permissions,
                'token_info' => [
                    'created_at' => $user->currentAccessToken()->created_at,
                    'last_used_at' => $user->currentAccessToken()->last_used_at,
                ]
            ]
        ]);
    }

    /**
     * Get permissions for a specific role
     */
    private function getUserPermissions(string $role): array
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
                'dashboard.student'
            ]
        ];

        return $rolePermissions[$role] ?? [];
    }

    /**
     * Register new user
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:student,teacher',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'is_active' => true,
            ]);

            // Create role-specific record
            if ($request->role === 'student') {
                // Get default department or create one
                $defaultDepartment = \App\Models\Department::firstOrCreate([
                    'name' => 'General Studies'
                ], [
                    'code' => 'GEN',
                    'description' => 'Default department for new students'
                ]);

                $user->student()->create([
                    'student_id' => 'STU' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                    'department_id' => $defaultDepartment->id,
                    'batch' => date('Y'),
                    'status' => 'enrolled',
                    'admission_date' => now(),
                ]);
            } elseif ($request->role === 'teacher') {
                // Get default department or create one
                $defaultDepartment = \App\Models\Department::firstOrCreate([
                    'name' => 'General Studies'
                ], [
                    'code' => 'GEN',
                    'description' => 'Default department for new teachers'
                ]);

                $user->teacher()->create([
                    'employee_id' => 'EMP' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                    'department_id' => $defaultDepartment->id,
                    'designation' => 'Lecturer',
                    'qualification' => 'To be updated',
                    'joining_date' => now(),
                ]);
            }

            // Create token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Load role-specific data
            $userData = $user->load($user->role);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $userData,
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }
}
