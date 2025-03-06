<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            [
                'section_id' => 1,
                'professor_id' => 3,
                'name' => 'Introduction to Computing',
                'subject_code' => 'ITC101',
                'subject_units' => 3,
                'day' => 'Monday',
                'lab_time_starts_at' => '07:00:00',
                'lab_time_ends_at' => '10:00:00',
                'semester' => '1st',
                'school_year' => '2024-2025',
                'status' => 'active',
            ],
            [
                'section_id' => 1,
                'professor_id' => 3,
                'name' => 'Computer Programming 1',
                'subject_code' => 'CP101',
                'subject_units' => 3,
                'day' => 'Tuesday',
                'lab_time_starts_at' => '10:00:00',
                'lab_time_ends_at' => '13:00:00',
                'semester' => '1st',
                'school_year' => '2024-2025',
                'status' => 'active',
            ],
            [
                'section_id' => 2,
                'professor_id' => 3,
                'name' => 'Database Management Systems',
                'subject_code' => 'DBMS201',
                'subject_units' => 3,
                'day' => 'Wednesday',
                'lab_time_starts_at' => '13:00:00',
                'lab_time_ends_at' => '16:00:00',
                'semester' => '1st',
                'school_year' => '2024-2025',
                'status' => 'active',
            ],
            [
                'section_id' => 2,
                'professor_id' => 3,
                'name' => 'Web Development',
                'subject_code' => 'WD301',
                'subject_units' => 3,
                'day' => 'Thursday',
                'lab_time_starts_at' => '07:00:00',
                'lab_time_ends_at' => '10:00:00',
                'semester' => '1st',
                'school_year' => '2024-2025',
                'status' => 'active',
            ],
            [
                'section_id' => 3,
                'professor_id' => 3,
                'name' => 'Software Engineering',
                'subject_code' => 'SE401',
                'subject_units' => 3,
                'day' => 'Friday',
                'lab_time_starts_at' => '10:00:00',
                'lab_time_ends_at' => '13:00:00',
                'semester' => '1st',
                'school_year' => '2024-2025',
                'status' => 'active',
            ],
            [
                'section_id' => 3,
                'professor_id' => 3,
                'name' => 'Network Administration',
                'subject_code' => 'NA501',
                'subject_units' => 3,
                'day' => 'Monday',
                'lab_time_starts_at' => '13:00:00',
                'lab_time_ends_at' => '16:00:00',
                'semester' => '1st',
                'school_year' => '2024-2025',
                'status' => 'active',
            ],
            [
                'section_id' => 4,
                'professor_id' => 3,
                'name' => 'Cybersecurity Fundamentals',
                'subject_code' => 'CF601',
                'subject_units' => 3,
                'day' => 'Tuesday',
                'lab_time_starts_at' => '07:00:00',
                'lab_time_ends_at' => '10:00:00',
                'semester' => '1st',
                'school_year' => '2024-2025',
                'status' => 'active',
            ],
            [
                'section_id' => 4,
                'professor_id' => 3,
                'name' => 'Mobile Application Development',
                'subject_code' => 'MAD701',
                'subject_units' => 3,
                'day' => 'Wednesday',
                'lab_time_starts_at' => '10:00:00',
                'lab_time_ends_at' => '13:00:00',
                'semester' => '1st',
                'school_year' => '2024-2025',
                'status' => 'active',
            ],
            [
                'section_id' => 5,
                'professor_id' => 3,
                'name' => 'Data Structures and Algorithms',
                'subject_code' => 'DSA801',
                'subject_units' => 3,
                'day' => 'Thursday',
                'lab_time_starts_at' => '13:00:00',
                'lab_time_ends_at' => '16:00:00',
                'semester' => '1st',
                'school_year' => '2024-2025',
                'status' => 'active',
            ],
            [
                'section_id' => 5,
                'professor_id' => 3,
                'name' => 'Artificial Intelligence',
                'subject_code' => 'AI901',
                'subject_units' => 3,
                'day' => 'Friday',
                'lab_time_starts_at' => '07:00:00',
                'lab_time_ends_at' => '10:00:00',
                'semester' => '1st',
                'school_year' => '2024-2025',
                'status' => 'active',
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}
