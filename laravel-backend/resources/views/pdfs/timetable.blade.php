<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Class Timetable</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0.5in;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #2563eb;
            font-size: 18pt;
        }
        .header p {
            margin: 5px 0;
        }
        .student-info {
            margin-bottom: 15px;
            font-size: 9pt;
        }
        .student-info span {
            margin-right: 20px;
        }
        .student-info strong {
            color: #2563eb;
        }
        .timetable {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .timetable th,
        .timetable td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            font-size: 8pt;
        }
        .timetable th {
            background: #2563eb;
            color: white;
            font-weight: bold;
        }
        .timetable th.day {
            background: #1e40af;
            width: 12%;
        }
        .class-slot {
            background: #eff6ff;
            border-left: 3px solid #2563eb;
            text-align: left;
            padding: 6px;
        }
        .class-slot .course-code {
            font-weight: bold;
            color: #2563eb;
        }
        .class-slot .course-name {
            font-size: 7pt;
            margin: 2px 0;
        }
        .class-slot .instructor {
            font-size: 7pt;
            color: #666;
        }
        .class-slot .room {
            font-size: 7pt;
            color: #059669;
            font-weight: bold;
        }
        .empty-slot {
            background: #f9fafb;
            color: #9ca3af;
        }
        .footer {
            text-align: center;
            font-size: 8pt;
            color: #666;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        .legend {
            margin-top: 15px;
            font-size: 8pt;
            padding: 10px;
            background: #f9fafb;
            border: 1px solid #ddd;
        }
        .legend p {
            margin: 3px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Class Timetable</h1>
        <p>{{ $semester }}</p>
    </div>

    <div class="student-info">
        <span><strong>Name:</strong> {{ $student->user->name }}</span>
        <span><strong>ID:</strong> {{ $student->student_id }}</span>
        <span><strong>Department:</strong> {{ $student->department->name ?? 'N/A' }}</span>
        <span><strong>Generated:</strong> {{ $generated_date }}</span>
    </div>

    <table class="timetable">
        <thead>
            <tr>
                <th>Time</th>
                <th class="day">Monday</th>
                <th class="day">Tuesday</th>
                <th class="day">Wednesday</th>
                <th class="day">Thursday</th>
                <th class="day">Friday</th>
                <th class="day">Saturday</th>
            </tr>
        </thead>
        <tbody>
            @php
                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                $timeSlots = [
                    '08:00 - 09:00',
                    '09:00 - 10:00',
                    '10:00 - 11:00',
                    '11:00 - 12:00',
                    '12:00 - 13:00',
                    '13:00 - 14:00',
                    '14:00 - 15:00',
                    '15:00 - 16:00',
                    '16:00 - 17:00',
                ];
            @endphp

            @foreach($timeSlots as $timeSlot)
            <tr>
                <td style="background: #f3f4f6; font-weight: bold;">{{ $timeSlot }}</td>
                @foreach($days as $day)
                    @php
                        $class = null;
                        if (isset($timetable[$day])) {
                            foreach ($timetable[$day] as $entry) {
                                $startTime = \Carbon\Carbon::parse($entry->start_time)->format('H:i');
                                $endTime = \Carbon\Carbon::parse($entry->end_time)->format('H:i');
                                $slotStart = explode(' - ', $timeSlot)[0];
                                
                                if ($startTime <= $slotStart && $endTime > $slotStart) {
                                    $class = $entry;
                                    break;
                                }
                            }
                        }
                    @endphp
                    
                    @if($class)
                    <td class="class-slot">
                        <div class="course-code">{{ $class->course->course_code }}</div>
                        <div class="course-name">{{ Str::limit($class->course->course_name, 30) }}</div>
                        <div class="instructor">{{ $class->teacher->user->name ?? 'TBA' }}</div>
                        <div class="room">{{ $class->room }}</div>
                    </td>
                    @else
                    <td class="empty-slot">-</td>
                    @endif
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="legend">
        <p><strong>Legend:</strong></p>
        <p>• Please arrive 5 minutes before class starts</p>
        <p>• Room changes will be communicated via email</p>
        <p>• This timetable is subject to change - check online portal for updates</p>
    </div>

    <div class="footer">
        <p>Academic Nexus University - Class Timetable</p>
        <p>Generated on {{ now()->format('F d, Y H:i:s') }}</p>
    </div>
</body>
</html>
