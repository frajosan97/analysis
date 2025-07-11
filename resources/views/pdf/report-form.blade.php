<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Forms - {{ $schoolInfo['name'] }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            color: #000;
            line-height: 1.6;
        }

        .page-break {
            page-break-after: always;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .image {
            height: 80px;
            object-fit: contain;
        }

        .logo-container {
            width: 20%;
            vertical-align: top;
        }

        .passport-container {
            width: 20%;
            text-align: right;
            vertical-align: top;
        }

        .school-info-container {
            width: 60%;
            text-align: center;
            vertical-align: top;
        }

        .school-name {
            font-size: 24px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .school-moto {
            font-style: italic;
            text-transform: capitalize;
            color: red;
        }

        .student-name {
            font-size: 20px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .student-other-info {
            text-transform: capitalize;
        }

        .student-stats {
            background-color: #f8f9fa;
            padding: 10px;
        }

        .stats-title {
            font-weight: 600;
        }

        .results-table tr,
        .results-table th,
        .results-table td {
            padding: 3px;
            border: 1px solid rgba(47, 48, 48, 0.38);
        }

        .marks {
            text-align: center;
        }
    </style>
</head>

<body>
    @foreach($students as $student)
    <div class="report-card">
        <!-- Header Section -->
        <table class="table table-bordered">
            <tr>
                <td class="logo-container">
                    <img class="image" src="https://kathekaboys.edupulse.co.ke/public/assets/images/logos/1690291517.png" alt="School Logo">
                </td>

                <td class="school-info-container">
                    <div class="school-name">{{ $schoolInfo['name'] }}</div>
                    <div class="school-moto">{{ $schoolInfo['moto'] }}</div>
                    <div class="school-address">{{ $schoolInfo['address'] }}</div>
                    <div class="school-address">Printed on: Thursday 20/02/2022 11:00 AM</div>
                </td>

                <td class="passport-container">
                    <img class="image" src="https://kathekaboys.edupulse.co.ke/public/assets/images/profiles/{{ $student->stud_pass }}" alt="Student Photo">
                </td>
            </tr>
        </table>

        <table class="table table-bordered">
            <tr>
                <td colspan="3" class="text-center p-2">
                    <div class="student-name">
                        {{ $student->stud_lname }} {{ $student->stud_fname }} {{ $student->stud_oname }}
                    </div>
                    <div class="student-other-info">
                        Gender: {{ ucfirst($student->stud_gender) }} |
                        Admission: {{ $student->stud_adm }} |
                        Age:
                    </div>
                </td>
            </tr>
        </table>

        <table class="table table-bordered">
            <tr>
                <td class="student-stats">
                    <div class="stats-column">
                        <div class="stats-title">ACADEMIC PERFORMANCE</div>
                        <div>Total Marks: {{ $student->results[0]->re_tt }}</div>
                        <div>Mean Marks: {{ $student->results[0]->re_mean }}</div>
                        <div>Mean Grade: {{ $student->results[0]->re_grade }}</div>
                    </div>
                </td>
                <td class="student-stats">
                    <div class="stats-column">
                        <div class="stats-title">SUBJECT ANALYSIS</div>
                        <div>Subjects: {{ $student->results[0]->re_subC }}</div>
                        <div>Total Points: {{ $student->results[0]->re_pnt }}</div>
                        <div>Mean Points: {{ $student->results[0]->re_avgpnt }}</div>
                    </div>
                </td>
                <td class="student-stats">
                    <div class="stats-column">
                        <div class="stats-title">RANKING</div>
                        <div>Class Rank: {{ $student->results[0]->re_fRank }}</div>
                        <div>Stream Rank: {{ $student->results[0]->re_sRank }}</div>
                        <div>Improvement: </div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="table table-bordered results-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    @foreach ($student->results as $result)
                    <th>
                        <div class="vertical-text">Exam Name</div>
                    </th>
                    @endforeach
                    <th>Remarks</th>
                    <th>Teacher</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($subjects as $subject)
                <tr>
                    <td class="text-capitalize text-start">
                        {{ $subject->systemSubject->sub_code }}
                        {{ $subject->systemSubject->sub_name }}
                    </td>
                    @foreach ($student->results as $result)
                    @php
                    $resultColumn = 're_s'.$subject->sch_sub_code;
                    @endphp
                    <td class="marks">
                        {{ ($result->$resultColumn > 0) ? $result->$resultColumn : '--'  }}
                    </td>
                    @endforeach
                    <td class="text-capitalize text-start"></td>
                    <td class="text-capitalize text-start"></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Page Break -->
        @if(!$loop->last)
        <div class="page-break"></div>
        @endif
    </div>
    @endforeach
</body>

</html>