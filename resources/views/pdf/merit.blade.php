<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
            white-space: nowrap;
        }

        /* For table cells specifically */
        td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3a7bd5;
        }

        .school-name {
            font-size: 18pt;
            font-weight: bold;
            color: #3a7bd5;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .report-title {
            font-size: 14pt;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .exam-info {
            font-size: 11pt;
            margin-bottom: 10px;
        }

        .date {
            font-size: 10pt;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th {
            background-color: #3a7bd5;
            color: white;
            font-weight: bold;
            padding: 8px 5px;
            text-align: center;
            border: 1px solid #ddd;
        }

        td {
            padding: 6px 5px;
            border: 1px solid #ddd;
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .position-col {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .total-col {
            background-color: #e9f5ff;
            font-weight: bold;
        }

        .subject-score {
            min-width: 40px;
        }

        .footer {
            margin-top: 15px;
            text-align: left;
            font-size: 9pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }

        .signature-line {
            width: 200px;
            height: 1em;
            display: inline-block;
            margin: 20px 10px 0;
        }

        .highlight {
            background-color: #fffacd;
        }

        .dropped {
            color: #999;
            text-decoration: line-through;
        }

        .footer td {
            text-align: left;
            padding: 10px 20px;
            border: 0px;
        }

        .footer tr:nth-child(even) {
            background-color: none;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="school-name">{{ $schoolName }}</div>
        <div class="report-title">MERIT LIST</div>
        <div class="exam-info">
            Form {{ $class }} Term {{ $exam->term->term }} {{ $exam->exam }}
        </div>
        <div class="date">Generated on: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Pos</th>
                <th>Adm No.</th>
                <th>Student Name</th>
                <th>Class</th>
                <th>KCPE</th>
                @foreach($subjects as $key => $value)
                <th>{{ $value->systemSubject->sub_short_name }}</th>
                @endforeach
                <th>Count</th>
                <th>Marks</th>
                <th>Points</th>
                <th>Avg</th>
                <th>Grade</th>
                <th>Postn</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
            <tr @if($index < 3) class="highlight" @endif>
                <td class="position-col">{{ $index + 1 }}</td>
                <td style="text-align: left;">{{ $student['adm'] }}</td>
                <td style="text-align: left;">{{ $student['name'] }}</td>
                <td style="text-align: left;">{{ $student['class'] }}</td>
                <td style="text-align: left;">{{ $student['kcpe'] ?? '-' }}</td>
                @foreach($subjects as $subject)
                <td>
                    {{ $student['scores'][$subject->sch_sub_code] ?? '--' }}
                </td>
                @endforeach
                <td class="total-col">{{ $student['re_subC'] }}</td>
                <td class="total-col">{{ $student['re_tt'] }}</td>
                <td class="total-col">{{ $student['re_pnt'] }}</td>
                <td class="total-col">{{ $student['re_avgpnt'] }}</td>
                <td class="total-col">{{ $student['re_grade'] }}</td>
                <td class="total-col">{{ $student['re_fRank'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <table>
            <tr>
                <td>
                    <div class="signature-line">Prepared By:__________________________________</div>
                </td>
                <td>
                    <div class="signature-line">Approved By:__________________________________</div>
                </td>
                <td>
                    <div class="signature-line">Approved By:__________________________________</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="signature-line">Signature:__________________________________</div>
                </td>
                <td>
                    <div class="signature-line">Signature:____________________________________</div>
                </td>
                <td>
                    <div class="signature-line">Signature:____________________________________</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="signature-line">Class Teacher</div>
                </td>
                <td>
                    <div class="signature-line">HOD Exams</div>
                </td>
                <td>
                    <div class="signature-line">Principal</div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>