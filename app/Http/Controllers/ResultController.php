<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\GradingCat;
use App\Models\GradingSystem;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use App\Services\ResultService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Yajra\DataTables\Facades\DataTables;

class ResultController extends Controller
{
    protected $resultService;

    public function __construct(ResultService $resultService)
    {
        $this->resultService = $resultService;
    }

    private function getDroppedSubjects(Student $student): array
    {
        return $this->resultService->getDroppedSubjects($student);
    }

    private function getSubjectFields(): array
    {
        return $this->resultService->getSubjectFields();
    }

    private function prepareSubjectScores(Result $result, array $droppedSubjects, array $subjectFields): array
    {
        return $this->resultService->prepareSubjectScores($result, $droppedSubjects, $subjectFields);
    }

    /*
    |--------------------------------------------------------------------------
    | Result Index
    |--------------------------------------------------------------------------
    |
    | Displays a listing of exam results for a specific class. Handles both
    | regular page requests and AJAX DataTables requests. For DataTables,
    | it returns formatted student results with subject scores and grades.
    |
    | @param Request $request The HTTP request object
    | @param Exam $exam The exam model instance
    | @param string $class The class identifier
    | @return mixed Returns either a DataTables response or Inertia render
    |
    */
    public function index(Request $request, Exam $exam, string $class)
    {
        try {
            $exam->load(['term']);

            // Handle AJAX DataTables request
            if ($request->has('draw')) {
                $query = Result::with(['student'])
                    ->where('re_exam', $exam->exam_key)
                    ->where('re_studF', $class)
                    ->orderBy('re_pnt', 'DESC')
                    ->orderBy('re_tt', 'DESC')
                    ->get();

                return DataTables::of($query)
                    ->addColumn('adm', fn($row) => $row->student->stud_adm)
                    ->addColumn('name', fn($row) => $row->student->stud_lname . ' ' . $row->student->stud_fname)
                    ->addColumn('class', fn($row) => $row->student->class->class . ' ' . $row->student->stream->stream)
                    ->addColumn('kcpe', fn($row) => $row->student->stud_kcpe_marks)
                    ->addColumn('scores', function ($row) {
                        $droppedSubjects = $this->getDroppedSubjects($row->student);
                        $subjectFields = $this->getSubjectFields();

                        return $this->prepareSubjectScores($row, $droppedSubjects, $subjectFields);
                    })
                    ->addColumn('action', fn($row) => $this->getActionButtons($row))
                    ->rawColumns(['status_badge', 'action'])
                    ->make(true);
            }

            return Inertia::render('Result/Index', [
                'exam' => $exam,
                'classData' => $class
            ]);
        } catch (\Throwable $th) {
            Log::error('Error: ' . $th->getMessage());
            return back()->with('error', 'An error occurred while loading results.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Subject Grade
    |--------------------------------------------------------------------------
    |
    | Determines the grade for given marks based on the school's grading system.
    | First checks for subject-specific grading category, then falls back to
    | default grading system if available.
    |
    | @param float $marks The obtained marks
    | @return array|null Returns grade details or null if no match found
    |
    */
    public function getSubjectGrade(float $marks): ?array
    {
        try {
            $gradingCat = GradingCat::where('sch_token', 'kathekaboys')->first();

            if ($gradingCat) {
                $grade = GradingSystem::where('grds_cat_key', $gradingCat->grd_key)
                    ->where('grds_min', '<=', $marks)
                    ->where('grds_max', '>=', $marks)
                    ->first();

                if ($grade) {
                    return [
                        'grade' => $grade->grds_grade,
                        'points' => $grade->grds_point,
                        'remarks' => $grade->grds_rem,
                        'min' => $grade->grds_min,
                        'max' => $grade->grds_max,
                    ];
                }
            }

            return null;
        } catch (\Throwable $th) {
            Log::error('Error getting subject grade: ' . $th->getMessage());
            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Action Buttons
    |--------------------------------------------------------------------------
    |
    | Generates HTML for action buttons in the results table. Currently includes
    | only a 'Manage' button, but can be extended with additional actions.
    |
    | @param Result $result The result model instance
    | @return string HTML string containing action buttons
    |
    */
    private function getActionButtons(Result $result): string
    {
        return '
            <div class="btn-group float-end text-nowrap gap-2">
                <a href="" 
                   class="btn btn-sm btn-outline-success rounded">
                   <i class="bi bi-list"></i> Manage
                </a>
            </div>
        ';
    }
}
