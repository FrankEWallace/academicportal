<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admission Letter</title>
    <style>
        @page {
            margin: 1in;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #333;
        }
        .letterhead {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
        }
        .letterhead h1 {
            margin: 0;
            color: #2563eb;
            font-size: 24pt;
        }
        .letterhead p {
            margin: 5px 0;
            font-size: 10pt;
        }
        .date {
            text-align: right;
            margin: 20px 0;
        }
        .content {
            margin: 30px 0;
        }
        .content p {
            margin: 15px 0;
        }
        .student-details {
            background: #f3f4f6;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #2563eb;
        }
        .student-details table {
            width: 100%;
        }
        .student-details td {
            padding: 5px;
        }
        .student-details td:first-child {
            font-weight: bold;
            width: 150px;
        }
        .signature {
            margin-top: 60px;
        }
        .signature-line {
            border-top: 1px solid #333;
            width: 200px;
            margin-top: 50px;
        }
        .footer {
            text-align: center;
            font-size: 9pt;
            color: #666;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="letterhead">
        <h1>Academic Nexus University</h1>
        <p>Excellence in Education</p>
        <p>123 University Avenue | Academic City, ST 12345</p>
        <p>Tel: +1 (555) 123-4567 | Email: admissions@academicnexus.edu</p>
    </div>

    <div class="date">
        {{ $date }}
    </div>

    <div class="content">
        <p><strong>{{ $student->user->name }}</strong><br>
        {{ $student->contact_address ?? 'On File' }}</p>

        <p>Dear {{ $student->user->name }},</p>

        <p><strong>CONGRATULATIONS ON YOUR ADMISSION!</strong></p>

        <p>On behalf of Academic Nexus University, I am delighted to inform you that you have been admitted to our esteemed institution for the {{ $academic_year }} academic year.</p>

        <div class="student-details">
            <table>
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
                    <td>Admission Date:</td>
                    <td>{{ \Carbon\Carbon::parse($student->enrollment_date)->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td>Academic Year:</td>
                    <td>{{ $academic_year }}</td>
                </tr>
            </table>
        </div>

        <p>This admission is granted based on the successful completion of your application and meeting our admission requirements. We are confident that you will make a valuable contribution to our academic community.</p>

        <p><strong>Next Steps:</strong></p>
        <ul>
            <li>Complete your registration process online</li>
            <li>Pay required fees before the deadline</li>
            <li>Attend orientation program (details will be sent separately)</li>
            <li>Obtain your student ID card from the Student Affairs Office</li>
        </ul>

        <p>Should you have any questions regarding your admission or the registration process, please do not hesitate to contact the Registrar's Office.</p>

        <p>We look forward to welcoming you to Academic Nexus University!</p>

        <p>Yours sincerely,</p>

        <div class="signature">
            <div class="signature-line"></div>
            <p><strong>Dr. John Anderson</strong><br>
            Registrar<br>
            Academic Nexus University</p>
        </div>
    </div>

    <div class="footer">
        <p>This is an official document from Academic Nexus University</p>
        <p>Document generated on {{ now()->format('F d, Y H:i:s') }}</p>
    </div>
</body>
</html>
