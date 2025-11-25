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
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6|max:255',
            'role' => 'required|string|in:admin,student,teacher',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please check your input and try again',
                'errors' => $validator->errors()
            ], 422);
        }

        // Rate limiting should be implemented here in production
        
        $user = User::where('email', $request->email)
                   ->where('role', $request->role)
                   ->where('is_active', true)
                   ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            \Log::warning('Failed login attempt for email: ' . $request->email . ' with role: ' . $request->role);
            
            // Use consistent timing to prevent timing attacks
            if (!$user) {
                Hash::check('dummy-password', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
            }
            
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials do not match our records'
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
            'name' => 'required|string|max:255|min:2|regex:/^[a-zA-Z\s]+$/',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            'role' => 'required|string|in:student,teacher',
        ], [
            'name.regex' => 'Name can only contain letters and spaces.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please correct the following errors',
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

    /**
     * Request password reset
     */
    public function passwordResetRequest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a valid email address',
                'errors' => $validator->errors()
            ], 422);
        }

        // Always return the same response to prevent email enumeration
        try {
            $user = User::where('email', $request->email)->where('is_active', true)->first();
            
            if ($user) {
                // Generate reset token only if user exists and is active
                $token = \App\Models\PasswordResetToken::createTokenForEmail($request->email);
                
                // In a real application, send email here
                \Log::info('Password reset requested for: ' . $request->email);
            }

            // Clean up expired tokens periodically
            \App\Models\PasswordResetToken::cleanExpiredTokens();

            // Always return the same message to prevent email enumeration
            return response()->json([
                'success' => true,
                'message' => 'If an account with that email exists, a password reset link has been sent',
                'data' => [
                    'expires_in' => '1 hour'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Password reset request failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to process password reset request at this time'
            ], 500);
        }
    }

    /**
     * Reset password using token
     */
    public function passwordReset(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string|size:64', // Ensure exact token length
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verify the token first
            if (!\App\Models\PasswordResetToken::isValidToken($request->email, $request->token)) {
                \Log::warning('Invalid password reset attempt for email: ' . $request->email);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired reset token'
                ], 400);
            }

            // Find the user and ensure they're active
            $user = User::where('email', $request->email)->where('is_active', true)->first();
            
            if (!$user) {
                \Log::warning('Password reset attempted for inactive user: ' . $request->email);
                return response()->json([
                    'success' => false,
                    'message' => 'User account not found or inactive'
                ], 404);
            }

            // Check if new password is different from current password
            if (Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'New password must be different from current password'
                ], 400);
            }

            // Update the password
            $user->password = Hash::make($request->password);
            $user->save();

            // Delete the used token immediately
            \App\Models\PasswordResetToken::where('email', $request->email)
                ->where('token', $request->token)
                ->delete();

            // Revoke all existing authentication tokens for security
            $user->tokens()->delete();

            \Log::info('Password reset successful for user: ' . $request->email);

            return response()->json([
                'success' => true,
                'message' => 'Password has been reset successfully. Please log in with your new password.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Password reset failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to reset password at this time'
            ], 500);
        }
    }
}
