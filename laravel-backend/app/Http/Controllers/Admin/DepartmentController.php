<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    /**
     * Get all departments with statistics
     */
    public function index(Request $request)
    {
        try {
            $query = Department::with('headTeacher:id,name,email');

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Search
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $departments = $query->orderBy('name')->get();

            // Add statistics to each department
            $departments->each(function($department) {
                $department->teachers_count = User::where('role', 'teacher')
                    ->where('department_id', $department->id)
                    ->count();
                
                $department->students_count = DB::table('students')
                    ->where('department_id', $department->id)
                    ->count();
                
                $department->courses_count = DB::table('courses')
                    ->where('department_id', $department->id)
                    ->count();
            });

            return response()->json([
                'success' => true,
                'message' => 'Departments fetched successfully',
                'data' => $departments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch departments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single department
     */
    public function show($id)
    {
        try {
            $department = Department::with('headTeacher:id,name,email')->findOrFail($id);

            // Add statistics
            $department->teachers_count = User::where('role', 'teacher')
                ->where('department_id', $department->id)
                ->count();
            
            $department->students_count = DB::table('students')
                ->where('department_id', $department->id)
                ->count();
            
            $department->courses_count = DB::table('courses')
                ->where('department_id', $department->id)
                ->count();

            // Get teachers in department
            $department->teachers = User::where('role', 'teacher')
                ->where('department_id', $department->id)
                ->select('id', 'name', 'email')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Department fetched successfully',
                'data' => $department
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Department not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create new department
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:departments,code',
            'description' => 'nullable|string',
            'head_teacher_id' => 'nullable|exists:users,id',
            'established_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'budget' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'nullable|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $department = Department::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Department created successfully',
                'data' => $department
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create department',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update department
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:20|unique:departments,code,' . $id,
            'description' => 'nullable|string',
            'head_teacher_id' => 'nullable|exists:users,id',
            'established_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'budget' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'nullable|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $department = Department::findOrFail($id);
            $department->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Department updated successfully',
                'data' => $department
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update department',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete department
     */
    public function destroy($id)
    {
        try {
            $department = Department::findOrFail($id);

            // Check if department has associated records
            $teachersCount = User::where('department_id', $id)->count();
            $studentsCount = DB::table('students')->where('department_id', $id)->count();
            $coursesCount = DB::table('courses')->where('department_id', $id)->count();

            if ($teachersCount > 0 || $studentsCount > 0 || $coursesCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete department with associated teachers, students, or courses',
                    'data' => [
                        'teachers_count' => $teachersCount,
                        'students_count' => $studentsCount,
                        'courses_count' => $coursesCount
                    ]
                ], 400);
            }

            $department->delete();

            return response()->json([
                'success' => true,
                'message' => 'Department deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete department',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get department statistics
     */
    public function statistics()
    {
        try {
            $totalDepartments = Department::count();
            $activeDepartments = Department::where('status', 'active')->count();
            $inactiveDepartments = Department::where('status', 'inactive')->count();
            $departmentsWithHead = Department::whereNotNull('head_teacher_id')->count();

            // Get total budget
            $totalBudget = Department::sum('budget');

            // Get largest department by students
            $largestDept = DB::table('departments')
                ->leftJoin('students', 'departments.id', '=', 'students.department_id')
                ->select('departments.name', DB::raw('COUNT(students.id) as student_count'))
                ->groupBy('departments.id', 'departments.name')
                ->orderByDesc('student_count')
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Statistics fetched successfully',
                'data' => [
                    'total_departments' => $totalDepartments,
                    'active_departments' => $activeDepartments,
                    'inactive_departments' => $inactiveDepartments,
                    'departments_with_head' => $departmentsWithHead,
                    'total_budget' => $totalBudget,
                    'largest_department' => $largestDept ? [
                        'name' => $largestDept->name,
                        'student_count' => $largestDept->student_count
                    ] : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available teachers for department head
     */
    public function getAvailableTeachers()
    {
        try {
            $teachers = User::where('role', 'teacher')
                ->select('id', 'name', 'email', 'department_id')
                ->with('department:id,name')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Teachers fetched successfully',
                'data' => $teachers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch teachers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign head to department
     */
    public function assignHead(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'head_teacher_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $department = Department::findOrFail($id);
            
            // Verify the teacher exists and has teacher role
            $teacher = User::where('id', $request->head_teacher_id)
                ->where('role', 'teacher')
                ->firstOrFail();

            $department->head_teacher_id = $request->head_teacher_id;
            $department->save();

            return response()->json([
                'success' => true,
                'message' => 'Department head assigned successfully',
                'data' => $department->load('headTeacher:id,name,email')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign department head',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove head from department
     */
    public function removeHead($id)
    {
        try {
            $department = Department::findOrFail($id);
            $department->head_teacher_id = null;
            $department->save();

            return response()->json([
                'success' => true,
                'message' => 'Department head removed successfully',
                'data' => $department
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove department head',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
