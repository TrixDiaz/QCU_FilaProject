<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Subject;
use App\Models\AssetGroup;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function showSubjects()
    {
        return Subject::with([
            'section.classroom.building',
            'professor'
        ])->get();
    }

    public function showClassroomBuildingById($id)
    {
        return Subject::with([
            'section.classroom.building',
            'professor'
        ])->findOrFail($id);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeAttendance(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'subject_id' => 'required|integer|exists:subjects,id',
                'terminal_number' => 'required|string|max:255',
                // 'terminal_id' => 'required|integer|exists:asset_groups,id',
                'student_full_name' => 'required|string|max:255',
                'student_email' => 'nullable|email|max:255',
                'student_number' => 'required|string|max:20',
                'remarks' => 'nullable|string|max:255',
            ]);

            // Create a new attendance record
            $attendance = \App\Models\Attendance::create($validated);

            // Load relationships for the response
            $attendance->load(['subject', 'terminal']);

            return response()->json([
                'message' => 'Attendance recorded successfully',
                'data' => $attendance
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to record attendance',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getAssetGroupsBySubject($id)
    {
        try {
            // Find the subject
            $subject = Subject::with('classroom')->findOrFail($id);

            if (!$subject || !$subject->classroom) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Subject or classroom not found'
                ], 404);
            }

            // Get asset groups for the classroom
            $assetGroups = AssetGroup::where('classroom_id', $subject->classroom->id)
                ->with(['assets'])  // Include related assets
                ->get();

            // Debug information
            \Log::info('Subject ID: ' . $id);
            \Log::info('Classroom ID: ' . $subject->classroom->id);
            \Log::info('Asset Groups Count: ' . $assetGroups->count());

            return response()->json([
                'status' => 'success',
                'data' => $assetGroups,
                'debug' => [
                    'subject_id' => $id,
                    'classroom_id' => $subject->classroom->id,
                    'count' => $assetGroups->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
