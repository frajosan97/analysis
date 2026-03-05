<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>

    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.5;
        }

        /* ---------- HEADER ---------- */
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3a7bd55d;
        }

        .school-name {
            font-size: 18pt;
            font-weight: bold;
            color: #3a7bd5;
            text-transform: uppercase;
        }

        .report-title {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 5px;
        }

        .exam-info {
            font-size: 11pt;
            margin-top: 4px;
        }

        .date {
            font-size: 9pt;
            color: #777;
            margin-top: 4px;
        }

        /* ---------- SECTION TITLE ---------- */
        h3 {
            margin: 25px 0 10px;
            font-size: 12pt;
            border-left: 4px solid #3a7bd5;
            padding-left: 8px;
        }

        /* ---------- TABLE ---------- */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background: #3a7bd5;
            color: #fff;
            font-weight: bold;
            padding: 7px 5px;
            border: 1px solid #ddd;
            text-align: center;
        }

        td {
            padding: 6px 5px;
            border: 1px solid #ddd;
            text-align: center;
            font-size: 9.5pt;
        }

        tr:nth-child(even) {
            background: #f8f9fa;
        }

        .left {
            text-align: left;
            text-transform: capitalize;
        }

        .nowrap {
            white-space: nowrap;
        }

        .total-row {
            background: #e9f5ff !important;
            font-weight: bold;
        }

        .subject-header {
            text-transform: capitalize;
            font-weight: bold;
            text-align: left;
            padding: 6px;
            font-size: 11pt;
        }

        /* ---------- FOOTER ---------- */
        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
            font-size: 9pt;
        }

        .signature-table td {
            border: none;
            padding: 15px 10px;
            text-align: center;
        }

        .signature-line {
            margin-top: 30px;
            border-top: 1px solid #000;
            padding-top: 5px;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="header">
        <div class="school-name">{{ $schoolName }}</div>
        <div class="report-title">PERFORMANCE ANALYSIS</div>
        <div class="exam-info">
            Form {{ $class }} | Term {{ $exam->term->term }} | {{ $exam->exam }}
        </div>
        <div class="date">
            Generated on: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <!-- OVERALL PERFORMANCE -->
    <h3>Overall Performance Summary</h3>
    <table>
        <thead>
            <tr>
                <th class="left">Stream</th>
                <th>Mean</th>
                <th>Grade</th>
                @foreach($gradingSystem as $grade)
                    <th>{{ $grade->grds_grade }}</th>
                @endforeach
                <th>Count</th>
                <th class="left">Teacher</th>
            </tr>
        </thead>
        <tbody>
            @foreach($overallDistribution['streams'] as $stream => $data)
                @if($data['entries'] > 0)
                    <tr>
                        <td class="left">{{ $stream }}</td>
                        <td>{{ number_format($data['mean_points'], 2) }}</td>
                        <td>{{ $data['grade'] }}</td>
                        @foreach($gradingSystem as $grade)
                            <td>{{ $data[$grade->grds_grade] }}</td>
                        @endforeach
                        <td>{{ $data['entries'] }}</td>
                        <td class="left nowrap">{{ $data['class_teacher'] ?? '' }}</td>
                    </tr>
                @endif
            @endforeach

            <tr class="total-row">
                <td class="left">Total / Mean</td>
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

    <!-- SUBJECT PERFORMANCE -->
    <h3>Subject-wise Performance Analysis</h3>

    @foreach($gradeDistribution as $subjectCode => $distribution)
        <table>
            <thead>
                <tr>
                    <th colspan="{{ 6 + count($gradingSystem) }}" class="subject-header">
                        {{ $subjectCode }}
                    </th>
                </tr>
                <tr>
                    <th class="left">Stream</th>
                    <th>Mean</th>
                    <th>Grade</th>
                    @foreach($gradingSystem as $grade)
                        <th>{{ $grade->grds_grade }}</th>
                    @endforeach
                    <th>Count</th>
                    <th class="left">Teacher</th>
                </tr>
            </thead>
            <tbody>
                @foreach($distribution['streams'] as $stream => $data)
                    @if($data['entries'] > 0)
                        <tr>
                            <td class="left">{{ $stream }}</td>
                            <td>{{ number_format($data['mean_points'], 2) }}</td>
                            <td>{{ $data['grade'] }}</td>
                            @foreach($gradingSystem as $grade)
                                <td>{{ $data[$grade->grds_grade] }}</td>
                            @endforeach
                            <td>{{ $data['entries'] }}</td>
                            <td class="left nowrap">{{ $data['subject_teacher'] ?? '' }}</td>
                        </tr>
                    @endif
                @endforeach

                <tr class="total-row">
                    <td class="left">Total / Mean</td>
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

    <!-- FOOTER -->
    <div class="footer">
        <table class="signature-table">
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