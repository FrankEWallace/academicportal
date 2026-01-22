<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Course Outline</title>
    <style>
        @page {
            margin: 1in;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #2563eb;
            font-size: 20pt;
        }
        .header p {
            margin: 5px 0;
        }
        .course-info {
            background: #eff6ff;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #2563eb;
        }
        .course-info table {
            width: 100%;
        }
        .course-info td {
            padding: 5px;
        }
        .course-info td:first-child {
            font-weight: bold;
            width: 180px;
        }
        .section {
            margin: 25px 0;
        }
        .section h3 {
            color: #2563eb;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .section ul {
            margin: 10px 0;
            padding-left: 25px;
        }
        .section li {
            margin: 5px 0;
        }
        .topics-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .topics-table th,
        .topics-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .topics-table th {
            background: #2563eb;
            color: white;
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
        <h1>Course Outline / Syllabus</h1>
        <p>Academic Nexus University</p>
    </div>

    <div class="course-info">
        <table>
            <tr>
                <td>Course Code:</td>
                <td><strong>{{ $course->course_code }}</strong></td>
            </tr>
            <tr>
                <td>Course Title:</td>
                <td><strong>{{ $course->course_name }}</strong></td>
            </tr>
            <tr>
                <td>Department:</td>
                <td>{{ $course->department->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Credits:</td>
                <td>{{ $course->credits ?? 0 }} Credit Hours</td>
            </tr>
            <tr>
                <td>Instructor:</td>
                <td>{{ $course->teacher->user->name ?? 'TBA' }}</td>
            </tr>
            <tr>
                <td>Semester:</td>
                <td>{{ $semester ?? 'Current' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Course Description</h3>
        <p>{{ $course->description ?? 'This course provides comprehensive coverage of the subject matter, combining theoretical knowledge with practical applications.' }}</p>
    </div>

    <div class="section">
        <h3>Learning Outcomes</h3>
        <p>Upon successful completion of this course, students will be able to:</p>
        <ul>
            <li>Understand core concepts and principles related to the subject area</li>
            <li>Apply theoretical knowledge to practical scenarios</li>
            <li>Analyze and evaluate complex problems in the field</li>
            <li>Demonstrate competency in required skills and techniques</li>
        </ul>
    </div>

    <div class="section">
        <h3>Prerequisites</h3>
        @if(isset($prerequisites) && count($prerequisites) > 0)
        <ul>
            @foreach($prerequisites as $prereq)
            <li>{{ $prereq->course_code }} - {{ $prereq->course_name }}</li>
            @endforeach
        </ul>
        @else
        <p>None</p>
        @endif
    </div>

    <div class="section">
        <h3>Assessment Methods</h3>
        <table class="topics-table">
            <tr>
                <th>Component</th>
                <th>Weight</th>
            </tr>
            <tr>
                <td>Continuous Assessment (CA)</td>
                <td>40%</td>
            </tr>
            <tr>
                <td>Final Examination</td>
                <td>60%</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Required Materials</h3>
        <ul>
            <li>Course textbook and reading materials (to be provided)</li>
            <li>Access to online learning resources</li>
            <li>Notebook and writing materials</li>
        </ul>
    </div>

    <div class="section">
        <h3>Course Policies</h3>
        <p><strong>Attendance:</strong> Regular attendance is mandatory. Students must maintain at least 75% attendance to be eligible for examinations.</p>
        <p><strong>Late Submissions:</strong> Late assignments will incur a penalty unless prior arrangement is made with the instructor.</p>
        <p><strong>Academic Integrity:</strong> Any form of plagiarism or academic dishonesty will result in disciplinary action.</p>
    </div>

    <div class="footer">
        <p>Academic Nexus University - Course Outline</p>
        <p>Generated on {{ now()->format('F d, Y') }}</p>
        <p>This outline is subject to modifications. Students will be notified of any changes.</p>
    </div>
</body>
</html>
