<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;
use Inertia\Inertia;

class ExamController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Exam Index
    |--------------------------------------------------------------------------
    |
    | Displays a listing of exams. Handles both regular page requests and
    | AJAX DataTables requests. For DataTables, returns formatted exam data
    | including term information, status badges, and action buttons.
    |
    | @param Request $request The HTTP request object
    | @return mixed Returns either a DataTables response or Inertia render
    |
    */
    public function index(Request $request)
    {
        try {
            // Handle AJAX DataTables request
            if ($request->has('draw')) {
                $query = Exam::with('term')
                    ->where('sch_token', 'kathekaboys')
                    ->orderBy('date', 'DESC');

                return DataTables::of($query)
                    ->addColumn('term', fn($row) => $row->term->term ?? '-')
                    ->addColumn('status_badge', fn($row) => $this->getStatusBadge($row))
                    ->addColumn('action', fn($row) => $this->getActionButtons($row))
                    ->rawColumns(['status_badge', 'action'])
                    ->make(true);
            }

            // Initial page render
            return Inertia::render('Exam/Index');
        } catch (\Throwable $th) {
            Log::error('Error in ExamController@index: ' . $th->getMessage());
            return back()->with('error', 'Failed to load exams listing.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Show Exam Details
    |--------------------------------------------------------------------------
    |
    | Displays detailed information about a specific exam including statistics
    | for each class (student count and average scores). Initializes default
    | values for classes that might not have results yet.
    |
    | @param Exam $exam The exam model instance
    | @return \Inertia\Response Returns Inertia render with exam data
    |
    */
    public function show(Exam $exam)
    {
        try {
            $exam->load(['term']);
            $classStats = $this->getClassStatistics($exam);
            $classData = $this->prepareClassData($classStats);

            return Inertia::render('Exam/Show', [
                'exam' => $exam,
                'classData' => $classData
            ]);
        } catch (\Throwable $th) {
            Log::error('Error in ExamController@show: ' . $th->getMessage());
            return back()->with('error', 'Failed to load exam details.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Status Badge
    |--------------------------------------------------------------------------
    |
    | Generates an HTML badge indicating the exam's current status.
    | Uses different colors for 'active' and 'closed' states.
    |
    | @param Exam $exam The exam model instance
    | @return string HTML badge element
    |
    */
    private function getStatusBadge(Exam $exam): string
    {
        return $exam->exam_status == 'active'
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-warning">Closed</span>';
    }

    /*
    |--------------------------------------------------------------------------
    | Get Action Buttons
    |--------------------------------------------------------------------------
    |
    | Generates HTML for action buttons in the exams table. Includes a 'Manage'
    | button that links to the exam details page.
    |
    | @param Exam $exam The exam model instance
    | @return string HTML string containing action buttons
    |
    */
    private function getActionButtons(Exam $exam): string
    {
        return '
            <div class="btn-group float-end text-nowrap gap-2">
                <a href="' . route('exams.show', $exam->id) . '" 
                   class="btn btn-sm btn-outline-success rounded">
                   <i class="bi bi-list"></i> Manage
                </a>
            </div>
        ';
    }

    /*
    |--------------------------------------------------------------------------
    | Get Class Statistics
    |--------------------------------------------------------------------------
    |
    | Retrieves basic statistics (student count and average score) for each
    | class from the exam results.
    |
    | @param Exam $exam The exam model instance
    | @return \Illuminate\Support\Collection Collection of class statistics
    |
    */
    private function getClassStatistics(Exam $exam)
    {
        return Result::where('re_exam', $exam->exam_key)
            ->selectRaw('re_studF as class_id, 
                COUNT(DISTINCT re_studK) as student_count, 
                AVG(re_mean) as average_score')
            ->groupBy('re_studF')
            ->get()
            ->keyBy('class_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Prepare Class Data
    |--------------------------------------------------------------------------
    |
    | Prepares class statistics data with default values for classes that might
    | not have results. Ensures all classes (1-4) are represented in the output.
    |
    | @param \Illuminate\Support\Collection $classStats Collected statistics
    | @return array Formatted class data with defaults for missing classes
    |
    */
    private function prepareClassData($classStats): array
    {
        $classData = [];
        foreach ([1, 2, 3, 4] as $classId) {
            $stats = $classStats->get($classId);

            $classData[$classId] = [
                'student_count' => $stats->student_count ?? 0,
                'average_score' => isset($stats->average_score) ? round($stats->average_score, 2) : null,
            ];
        }

        return $classData;
    }
}
