<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Exam Timetable</title>
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
            border-bottom: 2px solid #dc2626;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #dc2626;
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
            color: #dc2626;
        }
        .exam-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .exam-table th,
        .exam-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .exam-table th {
            background: #dc2626;
            color: white;
            font-weight: bold;
        }
        .exam-table tr:nth-child(even) {
            background: #fef2f2;
        }
        .exam-table .course-code {
            font-weight: bold;
            color: #dc2626;
        }
        .exam-table .venue {
            color: #059669;
            font-weight: bold;
        }
        .empty-message {
            text-align: center;
            padding: 40px;
            background: #fef2f2;
            border: 2px dashed #dc2626;
            margin: 20px 0;
        }
        .empty-message h3 {
            color: #dc2626;
            margin: 0 0 10px 0;
        }
        .instructions {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            font-size: 9pt;
        }
        .instructions h4 {
            margin: 0 0 10px 0;
            color: #f59e0b;
        }
        .instructions ul {
            margin: 5px 0;
            padding-left: 20px;
        }
        .instructions li {
            margin: 3px 0;
        }
        .footer {
            text-align: center;
            font-size: 8pt;
            color: #666;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Examination Timetable</h1>
        <p>{{ $semester }}</p>
    </div>

    <div class="student-info">
        <span><strong>Name:</strong> {{ $student->user->name }}</span>
        <span><strong>ID:</strong> {{ $student->student_id }}</span>
        <span><strong>Department:</strong> {{ $student->department->name ?? 'N/A' }}</span>
        <span><strong>Generated:</strong> {{ $generated_date }}</span>
    </div>

    @if(count($exams) > 0)
    <table class="exam-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Venue</th>
                <th>Duration</th>
                <th>Seat No.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($exams as $exam)
            <tr>
                <td>{{ \Carbon\Carbon::parse($exam->exam_date)->format('l, F d, Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($exam->start_time)->format('h:i A') }}</td>
                <td class="course-code">{{ $exam->course->course_code }}</td>
                <td>{{ $exam->course->course_name }}</td>
                <td class="venue">{{ $exam->venue }}</td>
                <td>{{ $exam->duration }} mins</td>
                <td>{{ $exam->seat_number ?? 'TBA' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-message">
        <h3>Exam Schedule Not Yet Available</h3>
        <p>The examination timetable for this semester has not been published yet.</p>
        <p>Please check back later or contact the Examinations Office for more information.</p>
    </div>
    @endif

    <div class="instructions">
        <h4>Examination Instructions:</h4>
        <ul>
            <li>Students must arrive at the exam venue <strong>30 minutes before</strong> the scheduled start time</li>
            <li>Bring your <strong>Student ID Card</strong> and <strong>Exam Admission Card</strong></li>
            <li>Mobile phones and electronic devices are <strong>strictly prohibited</strong> in the exam hall</li>
            <li>Only <strong>blue or black ink pens</strong> are permitted for writing</li>
            <li>Students arriving <strong>more than 30 minutes late</strong> will not be admitted</li>
            <li>No student is allowed to leave the exam hall during the <strong>first 30 minutes</strong></li>
            <li>Follow all instructions from invigilators</li>
            <li>Any form of <strong>malpractice will result in immediate disqualification</strong></li>
        </ul>
    </div>

    <div class="footer">
        <p>Academic Nexus University - Examinations Office</p>
        <p>Generated on {{ now()->format('F d, Y H:i:s') }}</p>
        <p style="color: #dc2626;"><strong>This timetable is subject to change - check online portal for updates</strong></p>
    </div>
</body>
</html>
