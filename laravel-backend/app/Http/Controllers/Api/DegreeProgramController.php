<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DegreeProgram;
use App\Models\ProgramRequirement;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DegreeProgramController extends Controller
{
    /**
     * Get all degree programs
     */
    public function index(Request $request)
    {
        try {
            $query = DegreeProgram::query();

            if ($request->has('department')) {
                $query->where('department', $request->department);
            }

            if ($request->has('level')) {
                $query->where('level', $request->level);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $programs = $query->get();

            return response()->json([
                'success' => true,
                'data' => $programs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching degree programs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new degree program
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'program_code' => 'required|string|unique:degree_programs',
                'program_name' => 'required|string',
                'department' => 'required|string',
                'level' => 'required|in:undergraduate,graduate,postgraduate',
                'duration_years' => 'required|integer|min:1',
                'total_credits_required' => 'required|integer|min:1',
                'minimum_cgpa' => 'nullable|numeric|min:0|max:5.00',
                'description' => 'nullable|string',
                'status' => 'nullable|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $program = DegreeProgram::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Degree program created successfully',
                'data' => $program
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating degree program',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific degree program with requirements
     */
    public function show($id)
    {
        try {
            $program = DegreeProgram::findOrFail($id);
            
            $requirements = ProgramRequirement::with('course')
                ->where('degree_program_id', $id)
                ->get()
                ->groupBy('requirement_type');

            return response()->json([
                'success' => true,
                'data' => [
                    'program' => $program,
                    'requirements' => $requirements,
                    'statistics' => [
                        'total_courses' => ProgramRequirement::where('degree_program_id', $id)->count(),
                        'core_courses' => ProgramRequirement::where('degree_program_id', $id)->where('requirement_type', 'core')->count(),
                        'major_courses' => ProgramRequirement::where('degree_program_id', $id)->where('requirement_type', 'major')->count(),
                        'elective_courses' => ProgramRequirement::where('degree_program_id', $id)->where('requirement_type', 'elective')->count()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching degree program',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a degree program
     */
    public function update(Request $request, $id)
    {
        try {
            $program = DegreeProgram::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'program_code' => 'sometimes|string|unique:degree_programs,program_code,' . $id,
                'program_name' => 'sometimes|string',
                'department' => 'sometimes|string',
                'level' => 'sometimes|in:undergraduate,graduate,postgraduate',
                'duration_years' => 'sometimes|integer|min:1',
                'total_credits_required' => 'sometimes|integer|min:1',
                'minimum_cgpa' => 'nullable|numeric|min:0|max:5.00',
                'description' => 'nullable|string',
                'status' => 'sometimes|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $program->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Degree program updated successfully',
                'data' => $program
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating degree program',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a degree program
     */
    public function destroy($id)
    {
        try {
            $program = DegreeProgram::findOrFail($id);
            $program->delete();

            return response()->json([
                'success' => true,
                'message' => 'Degree program deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting degree program',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a course requirement to a program
     */
    public function addRequirement(Request $request, $programId)
    {
        try {
            $program = DegreeProgram::findOrFail($programId);

            $validator = Validator::make($request->all(), [
                'course_id' => 'required|exists:courses,id',
                'requirement_type' => 'required|in:core,major,minor,elective,general_education',
                'semester_recommended' => 'nullable|integer|min:1',
                'is_mandatory' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $requirement = ProgramRequirement::create([
                'degree_program_id' => $programId,
                'course_id' => $request->course_id,
                'requirement_type' => $request->requirement_type,
                'semester_recommended' => $request->semester_recommended,
                'is_mandatory' => $request->is_mandatory ?? ($request->requirement_type === 'core')
            ]);

            $requirement->load('course');

            return response()->json([
                'success' => true,
                'message' => 'Course requirement added successfully',
                'data' => $requirement
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding course requirement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a course requirement from a program
     */
    public function removeRequirement($programId, $requirementId)
    {
        try {
            $requirement = ProgramRequirement::where('id', $requirementId)
                ->where('degree_program_id', $programId)
                ->firstOrFail();

            $requirement->delete();

            return response()->json([
                'success' => true,
                'message' => 'Course requirement removed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing course requirement',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
