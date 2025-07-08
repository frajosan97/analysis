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
            text-transform: capitalize;
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
            text-transform: capitalize;
            padding: 20px 20px 20px 0;
            border: 0px;
        }

        .footer tr:nth-child(even) {
            background-color: none;
        }

        .custom-table {
            text-align: left;
            text-transform: capitalize;
            width: 10%;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="school-name">{{ $schoolName }}</div>
        <div class="report-title">PERFORMANCE ANALYSIS</div>
        <div class="exam-info">
            Form {{ $class }} Term {{ $exam->term->term }} {{ $exam->exam }}
        </div>
        <div class="date">Generated on: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <!-- Overall Performance Summary -->
    <h3>Overall Performance Summary</h3>
    <table>
        <thead>
            <tr>
                <th class="custom-table">Stream</th>
                <th class="custom-table">Mean Points</th>
                <th class="custom-table">Grade</th>
                @foreach($gradingSystem as $grade)
                <th>{{ $grade->grds_grade }}</th>
                @endforeach
                <th class="custom-table">Count</th>
                <th class="custom-table">Teacher</th>
            </tr>
        </thead>
        <tbody>
            @foreach($overallDistribution['streams'] as $stream => $data)
            <tr>
                <td style="text-align: left; text-transform: capitalize;">{{ $stream }}</td>
                <td>{{ number_format($data['mean_points'], 2) }}</td>
                <td>{{ $data['grade'] }}</td>
                @foreach($gradingSystem as $grade)
                <td>{{ $data[$grade->grds_grade] }}</td>
                @endforeach
                <td>{{ $data['entries'] }}</td>
                <td></td>
            </tr>
            @endforeach
            <tr class="total-col">
                <td style="text-align: left; text-transform: capitalize;">Total/Mean</td>
                <td>{{ number_format($overallDistribution['total']['mean_points'], 2) }}</td>
                <td>{{ $overallDistribution['total']['grade'] }}</td>
                @foreach($gradingSystem as $grade)
                <td>{{ $overallDistribution['total'][$grade->grds_grade] }}</td>
                @endforeach
                <td>{{ $overallDistribution['total']['entries'] }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <!-- Subject-wise Performance -->
    <h3 style="margin-top: 20px;">Subject-wise Performance Analysis</h3>
    @foreach($gradeDistribution as $subjectCode => $distribution)
    <table>
        <thead>
            <tr>
                <th colspan="18" style="text-align: left; text-transform: capitalize;">
                    {{ $subjectCode }}
                </th>
            </tr>
            <tr>
                <th class="custom-table">Stream</th>
                <th class="custom-table">Mean Points</th>
                <th class="custom-table">Grade</th>
                @foreach($gradingSystem as $grade)
                <th>{{ $grade->grds_grade }}</th>
                @endforeach
                <th class="custom-table">Count</th>
                <th class="custom-table">Teacher</th>
            </tr>
        </thead>
        <tbody>
            @foreach($distribution['streams'] as $stream => $data)
            <tr>
                <td style="text-align: left; text-transform: capitalize;">{{ $stream }}</td>
                <td>{{ number_format($data['mean_points'], 2) }}</td>
                <td>{{ $data['grade'] }}</td>
                @foreach($gradingSystem as $grade)
                <td>{{ $data[$grade->grds_grade] }}</td>
                @endforeach
                <td>{{ $data['entries'] }}</td>
                <td></td>
            </tr>
            @endforeach
            <tr class="total-col">
                <td style="text-align: left; text-transform: capitalize;">Total/Mean</td>
                <td>{{ number_format($distribution['total']['mean_points'], 2) }}</td>
                <td>{{ $distribution['total']['grade'] }}</td>
                @foreach($gradingSystem as $grade)
                <td>{{ $distribution['total'][$grade->grds_grade] }}</td>
                @endforeach
                <td>{{ $distribution['total']['entries'] }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    @endforeach

    <!-- Grading System Reference -->
    <h3 style="margin-top: 20px;">Grading System</h3>
    <table>
        <thead>
            <tr>
                <th style="text-align: left; text-transform: capitalize;">Grade</th>
                <th>Points</th>
                <th>Min Mark</th>
                <th>Max Mark</th>
                <th style="text-align: left; text-transform: capitalize;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gradingSystem as $grade)
            <tr>
                <td style="text-align: left; text-transform: capitalize;" class="grade-{{ $grade->grds_grade }}">{{ $grade->grds_grade }}</td>
                <td>{{ $grade->grds_point }}</td>
                <td>{{ $grade->grds_min }}</td>
                <td>{{ $grade->grds_max }}</td>
                <td style="text-align: left; text-transform: capitalize;">{{ $grade->grds_rem }} / {{ $grade->grds_lugha }}</td>
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