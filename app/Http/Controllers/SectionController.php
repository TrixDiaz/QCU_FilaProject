<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function showSections()
    {
        return Section::with('classroom.building')->get();
    }

    public function showClassroomBuildingById($id)
    {
        return \App\Models\Section::with('classroom.building')->findOrFail($id);
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
        // Validate the request
        $validated = $request->validate([
            'professor_id' => 'required|integer|exists:users,id',
            'section_id' => 'required|integer|exists:sections,id',
            'terminal_code' => 'nullable',
            'student_full_name' => 'required|string',
            'student_email' => 'required|email',
            'student_number' => 'required|integer',
            'year_section' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        // Create a new attendance record
        $attendance = \App\Models\Attendance::create($validated);

        return response()->json([
            'message' => 'Attendance recorded successfully',
            'data' => $attendance
        ], 201);
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
