<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Course Registration Form</title>
    <style>
        @page {
            margin: 0.75in;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #2563eb;
            font-size: 20pt;
        }
        .header h2 {
            margin: 10px 0 0 0;
            font-size: 14pt;
            color: #666;
        }
        .student-info {
            background: #f3f4f6;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #2563eb;
        }
        .student-info table {
            width: 100%;
        }
        .student-info td {
            padding: 5px;
        }
        .student-info td:first-child {
            font-weight: bold;
            width: 150px;
        }
        .courses-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .courses-table th,
        .courses-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .courses-table th {
            background: #2563eb;
            color: white;
            font-weight: bold;
        }
        .courses-table tr:nth-child(even) {
            background: #f9fafb;
        }
        .summary {
            background: #eff6ff;
            padding: 15px;
            margin: 20px 0;
            border: 1px solid #2563eb;
        }
        .summary table {
            width: 100%;
        }
        .summary td {
            padding: 5px;
        }
        .summary td:first-child {
            font-weight: bold;
        }
        .signatures {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
        }
        .footer {
            text-align: center;
            font-size: 9pt;
            color: #666;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Academic Nexus University</h1>
        <h2>Course Registration Form</h2>
        <p>{{ $semester }} - {{ $registration_date }}</p>
    </div>

    <div class="student-info">
        <table>
            <tr>
                <td>Student Name:</td>
                <td>{{ $student->user->name }}</td>
            </tr>
            <tr>
                <td>Student ID:</td>
                <td>{{ $student->student_id }}</td>
            </tr>
            <tr>
                <td>Program:</td>
                <td>{{ $student->degree_program->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Department:</td>
                <td>{{ $student->department->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Current Year:</td>
                <td>Year {{ $student->current_year ?? 1 }}</td>
            </tr>
            <tr>
                <td>Registration Date:</td>
                <td>{{ $registration_date }}</td>
            </tr>
        </table>
    </div>

    <h3>Registered Courses</h3>
    <table class="courses-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Course Code</th>
                <th>Course Title</th>
                <th>Credits</th>
                <th>Instructor</th>
                <th>Department</th>
            </tr>
        </thead>
        <tbody>
            @foreach($enrollments as $index => $enrollment)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $enrollment->course->course_code }}</td>
                <td>{{ $enrollment->course->course_name }}</td>
                <td>{{ $enrollment->course->credits ?? 0 }}</td>
                <td>{{ $enrollment->course->teacher->user->name ?? 'TBA' }}</td>
                <td>{{ $enrollment->course->department->name ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td>Total Courses:</td>
                <td>{{ count($enrollments) }}</td>
            </tr>
            <tr>
                <td>Total Credits:</td>
                <td>{{ $total_credits }}</td>
            </tr>
            <tr>
                <td>Registration Status:</td>
                <td><strong>CONFIRMED</strong></td>
            </tr>
        </table>
    </div>

    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line">
                <p><strong>Student Signature</strong></p>
                <p>Date: _________________</p>
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                <p><strong>Academic Advisor Signature</strong></p>
                <p>Date: _________________</p>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>This is an official document from Academic Nexus University</p>
        <p>Generated on {{ now()->format('F d, Y H:i:s') }}</p>
    </div>
</body>
</html>
