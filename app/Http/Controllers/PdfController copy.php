<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\GradingSystem;
use App\Models\Result;
use App\Models\Stream;
use App\Models\Student;
use App\Models\Subject;
use App\Services\PdfService;
use App\Services\ResultService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PdfController extends Controller
{
    protected PdfService $pdfService;
    protected ResultService $resultService;

    // Constants for common values
    private const SCHOOL_TOKEN = 'kathekaboys';
    private const SCHOOL_NAME = 'Katheka Boys Secondary School';

    public function __construct(PdfService $pdfService, ResultService $resultService)
    {
        $this->pdfService = $pdfService;
        $this->resultService = $resultService;
    }

    /**
     * Generate merit list PDF for a specific exam and class
     */
    public function merit(Request $request, Exam $exam, string $class)
    {
        try {
            $data = $this->preparePdfData($exam, $class, 'Merit List');

            return $this->pdfService->generatePdfFromView(
                'pdf.merit',
                $data,
                "merit-list-form-{$class}-term-{$exam->term->term}-{$exam->exam}.pdf",
                'I',
                'L'
            );
        } catch (\Throwable $th) {
            return $this->handlePdfError($th, $exam, $class, 'merit list');
        }
    }

    /**
     * Generate analysis PDF for a specific exam and class
     */
    public function analysis(Request $request, Exam $exam, string $class)
    {
        try {
            $results = $this->getResultsForClass($exam, $class);

            if ($results->isEmpty()) {
                return back()->with('error', 'No results found for this class and exam.');
            }

            $data = $this->prepareAnalysisData($exam, $class, $results);

            return $this->pdfService->generatePdfFromView(
                'pdf.analysis',
                $data,
                "analysis-form-{$class}-term-{$exam->term->term}-{$exam->exam}.pdf",
                'I',
                'L'
            );
        } catch (\Throwable $th) {
            return $this->handlePdfError($th, $exam, $class, 'analysis');
        }
    }

    /**
     * Generate reportForm PDF for a specific exam and class
     *
     * @param Request $request
     * @param Exam $exam
     * @param string $class
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function reportForm(Request $request, Exam $exam, string $class)
    {
        try {
            // Eager load term with the exam
            $exam->load('term');

            // Fetch students with their most recent results and related data
            $students = Student::with([
                'class',
                'stream',
                'results' => function ($query) use ($exam) {
                    $query->where('re_exam', $exam->exam_key) // Filter results for the current exam
                        ->orderBy('date', 'desc'); // Assuming 'created_at' is a better indicator of recency than 'date'
                }
            ])
                ->where('sch_token', self::SCHOOL_TOKEN)
                ->where('stud_form', $class)
                ->get(); // No need for limit(2) if you want all students in the class

            if ($students->isEmpty()) {
                return back()->with('error', 'No students found for this class.');
            }

            $subjects = $this->getSubjects();
            $gradingSystem = $this->getGradingSystem(); // Fetch grading system for potential use in report forms

            $data = [
                'title' => 'Report Forms',
                'schoolInfo' => [
                    'name' => self::SCHOOL_NAME,
                    'moto' => 'strive to excel',
                    'address' => '222-90200',
                ],
                'exam' => $exam,
                'students' => $students,
                'subjects' => $subjects,
                'gradingSystem' => $gradingSystem, // Pass grading system for report form rendering
                'generatedAt' => now()->format('Y-m-d H:i:s'),
            ];

            return $this->pdfService->generatePdfFromView(
                'pdf.report-form',
                $data,
                "report-form-form-{$class}-term-{$exam->term->term}-{$exam->exam}.pdf",
                'I',
                'P'
            );
        } catch (\Throwable $th) {
            return $this->handlePdfError($th, $exam, $class, 'report form');
        }
    }

    /**
     * Prepare common PDF data structure
     */
    protected function preparePdfData(Exam $exam, string $class, string $titlePrefix): array
    {
        $exam->load('term');
        $results = $this->getResultsForClass($exam, $class);

        // Pre-fetch all necessary grading system, streams, and subjects to avoid redundant queries in loops
        $gradingSystem = $this->getGradingSystem();
        $subjects = $this->getSubjects();

        $studentsData = $results->map(function ($row) use ($gradingSystem) {
            // Optimization: Avoid calling resultService->getSubjectGrade repeatedly for each subject if not needed
            // If re_subC, re_tt, re_pnt, re_avgpnt, re_grade, re_sRank, re_fRank are directly available on $row,
            // then fetching them like this is fine. If they involve complex calculations in ResultService,
            // consider if they can be pre-calculated or stored.
            return [
                'adm' => $row->student->stud_adm,
                'name' => $row->student->stud_lname . ' ' . $row->student->stud_fname,
                'class' => $row->student->class->class . ' ' . $row->student->stream->stream,
                'kcpe' => $row->student->stud_kcpe_marks,
                'scores' => $this->prepareStudentScores($row), // Ensure this method is efficient
                're_subC' => $row->re_subC,
                're_tt' => $row->re_tt,
                're_pnt' => $row->re_pnt,
                're_avgpnt' => $row->re_avgpnt,
                're_grade' => $row->re_grade,
                're_sRank' => $row->re_sRank,
                're_fRank' => $row->re_fRank,
            ];
        })->toArray(); // Convert to array early if no more collection operations are needed

        return [
            'schoolName' => self::SCHOOL_NAME,
            'title' => "{$titlePrefix} - Term {$exam->term->term} - Form {$class}",
            'exam' => $exam,
            'class' => $class,
            'subjects' => $subjects,
            'students' => $studentsData,
            'printDate' => now()->format('D, d-m-Y h:i A'),
        ];
    }

    /**
     * Prepare analysis-specific data
     */
    protected function prepareAnalysisData(Exam $exam, string $class, $results): array
    {
        $gradingSystem = $this->getGradingSystem();
        $streams = $this->getStreams();
        $subjects = $this->getSubjects();

        $gradeDistribution = [];
        foreach ($subjects as $subject) {
            $subjectCode = $subject->systemSubject->sub_code;
            $subjectName = $subject->systemSubject->sub_name;
            $gradeDistribution[$subjectName] = $this->calculateDistribution(
                $results,
                $subjectCode,
                $gradingSystem,
                $streams,
                true
            );
        }

        $data = $this->preparePdfData($exam, $class, 'Analysis');
        $data['gradeDistribution'] = $gradeDistribution;
        $data['overallDistribution'] = $this->calculateDistribution(
            $results,
            null,
            $gradingSystem,
            $streams,
            false
        );
        $data['gradingSystem'] = $gradingSystem;

        return $data;
    }

    /**
     * Calculate grade distribution for subjects or overall performance
     */
    protected function calculateDistribution(
        $results,
        ?string $subjectCode,
        $gradingSystem,
        $streams,
        bool $isSubjectDistribution
    ): array {
        if ($isSubjectDistribution && empty($subjectCode)) {
            throw new \InvalidArgumentException('Subject code cannot be empty for subject distribution');
        }

        $distribution = [
            'total' => $this->initializeGradeCounts($gradingSystem),
            'streams' => [],
        ];

        foreach ($streams as $stream) {
            $distribution['streams'][$stream] = $this->initializeGradeCounts($gradingSystem);
        }

        foreach ($results as $result) {
            $stream = $result->student->stream->stream ?? 'Unknown';
            $value = $isSubjectDistribution
                ? ($result->{"re_s{$subjectCode}"} ?? 0)
                : ($result->re_avgpnt ?? 0);

            // Directly determine grade here instead of relying on external service if logic is simple
            // and `getSubjectGrade` isn't doing more complex lookups.
            // If `getSubjectGrade` handles different grading systems or complex logic, keep it.
            $gradeInfo = $this->resultService->getSubjectGrade($value, $isSubjectDistribution ? 'mark' : 'points');

            $this->updateDistribution($distribution['total'], $gradeInfo);

            if (isset($distribution['streams'][$stream])) {
                $this->updateDistribution($distribution['streams'][$stream], $gradeInfo);
            }
        }

        $this->finalizeDistribution($distribution, $gradingSystem);

        return $distribution;
    }

    /**
     * Update distribution counts for a specific group
     * Removed $value as it's not directly used for counting/points in this method, only gradeInfo.
     */
    protected function updateDistribution(&$distribution, array $gradeInfo): void
    {
        $distribution[$gradeInfo['grade']]++;
        $distribution['entries']++;
        $distribution['total_points'] += $gradeInfo['points'];
    }

    /**
     * Finalize distribution by calculating metrics
     */
    protected function finalizeDistribution(&$distribution, $gradingSystem): void
    {
        $this->calculateMetrics($distribution['total'], $gradingSystem);

        foreach ($distribution['streams'] as &$streamData) {
            $this->calculateMetrics($streamData, $gradingSystem);
        }
    }

    /**
     * Get results for a specific exam and class
     * Eager load student, class, and stream to avoid N+1 query problem.
     */
    protected function getResultsForClass(Exam $exam, string $class)
    {
        return Result::with(['student.class', 'student.stream'])
            ->where('re_exam', $exam->exam_key)
            ->where('re_studF', $class)
            ->orderBy('re_pnt', 'DESC')
            ->orderBy('re_tt', 'DESC')
            ->get();
    }

    /**
     * Get grading system configuration
     * Cache this result if it's static or changes infrequently.
     */
    protected function getGradingSystem()
    {
        // Using `rememberForever` for static data
        return \Illuminate\Support\Facades\Cache::rememberForever('grading_system', function () {
            return GradingSystem::where('sch_token', self::SCHOOL_TOKEN)
                ->orderBy('grds_min', 'DESC')
                ->get();
        });
    }

    /**
     * Get all streams
     * Cache this result as well.
     */
    protected function getStreams()
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('school_streams', function () {
            return Stream::where('sch_token', self::SCHOOL_TOKEN)->pluck('stream');
        });
    }

    /**
     * Get all subjects
     * Cache this result too.
     */
    protected function getSubjects()
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('school_subjects', function () {
            return Subject::with('systemSubject')
                ->where('sch_token', self::SCHOOL_TOKEN)
                ->get();
        });
    }

    /**
     * Handle PDF generation errors
     */
    protected function handlePdfError(\Throwable $th, Exam $exam, string $class, string $type)
    {
        Log::error("Error generating {$type} PDF: " . $th->getMessage(), [
            'exam_id' => $exam->id,
            'class' => $class,
            'trace' => $th->getTraceAsString()
        ]);

        return back()->with('error', "Failed to generate {$type}. Please try again.");
    }

    /**
     * Initialize grade counts structure
     */
    protected function initializeGradeCounts($gradingSystem): array
    {
        $counts = [
            'entries' => 0,
            'total_points' => 0,
            'mean_points' => 0,
            'grade' => 'E', // Default grade
            'total_students' => 0, // Added for clarity, might be useful for percentages
        ];

        foreach ($gradingSystem as $grade) {
            $counts[$grade->grds_grade] = 0;
        }

        return $counts;
    }

    /**
     * Calculate metrics (mean points and overall grade)
     * Renamed from `determineGrade` to reflect its purpose more accurately.
     * Removed `determineOverallGrade` as this method now handles the logic.
     */
    protected function calculateMetrics(&$data, $gradingSystem): void
    {
        if ($data['entries'] > 0) {
            $data['mean_points'] = $data['total_points'] / $data['entries'];

            // Find the grade based on mean_points
            foreach ($gradingSystem as $grade) {
                if ($data['mean_points'] >= $grade->grds_point) {
                    $data['grade'] = $grade->grds_grade;
                    break;
                }
            }
        }
    }

    /**
     * Prepare student subject scores
     * This method relies on ResultService, so ensure ResultService is optimized.
     */
    protected function prepareStudentScores(Result $result): array
    {
        // Assuming getDroppedSubjects and getSubjectFields are efficient or cached within ResultService
        $droppedSubjects = $this->resultService->getDroppedSubjects($result->student);
        $subjectFields = $this->resultService->getSubjectFields();
        return $this->resultService->prepareSubjectScores($result, $droppedSubjects, $subjectFields);
    }
}
