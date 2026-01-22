<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student ID Card</title>
    <style>
        @page {
            size: 85.6mm 53.98mm;
            margin: 0;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            width: 85.6mm;
            height: 53.98mm;
        }
        .id-card {
            width: 100%;
            height: 100%;
            border: 2px solid #2563eb;
            box-sizing: border-box;
            position: relative;
            background: linear-gradient(135deg, #ffffff 0%, #f3f4f6 100%);
        }
        .header {
            background: #2563eb;
            color: white;
            padding: 5px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 12pt;
        }
        .header p {
            margin: 2px 0;
            font-size: 7pt;
        }
        .content {
            padding: 8px;
            display: flex;
        }
        .photo {
            width: 25mm;
            height: 30mm;
            border: 1px solid #ddd;
            margin-right: 8px;
            background: #f9fafb;
        }
        .photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .details {
            flex: 1;
            font-size: 8pt;
        }
        .details p {
            margin: 3px 0;
        }
        .details .label {
            font-weight: bold;
            color: #374151;
        }
        .qr-code {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 15mm;
            height: 15mm;
            background: #fff;
            border: 1px solid #ddd;
            font-size: 6pt;
            text-align: center;
            padding: 2px;
        }
        .validity {
            position: absolute;
            bottom: 5px;
            left: 8px;
            font-size: 6pt;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="id-card">
        <div class="header">
            <h1>Academic Nexus University</h1>
            <p>STUDENT IDENTIFICATION CARD</p>
        </div>
        <div class="content">
            <div class="photo">
                @if(isset($photo_url))
                    <img src="{{ $photo_url }}" alt="Student Photo">
                @endif
            </div>
            <div class="details">
                <p><span class="label">Name:</span> {{ $student->user->name }}</p>
                <p><span class="label">Student ID:</span> {{ $student->student_id }}</p>
                <p><span class="label">Program:</span> {{ Str::limit($student->degree_program->name ?? 'N/A', 25) }}</p>
                <p><span class="label">Department:</span> {{ Str::limit($student->department->name ?? 'N/A', 25) }}</p>
                <p><span class="label">Year:</span> {{ $student->current_year ?? 1 }}</p>
                <p><span class="label">Status:</span> {{ ucfirst($student->status ?? 'active') }}</p>
            </div>
        </div>
        <div class="qr-code">
            <p>QR Code</p>
            <p style="font-size: 5pt;">{{ Str::limit($qr_code, 20) }}</p>
        </div>
        <div class="validity">
            <p>Valid: {{ \Carbon\Carbon::parse($issue_date)->format('m/Y') }} - {{ \Carbon\Carbon::parse($expiry_date)->format('m/Y') }}</p>
        </div>
    </div>
</body>
</html>
