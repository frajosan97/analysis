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
    protected $pdfService;
    protected $resultService;

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
                "merit-list-{$exam->exam_key}-{$class}.pdf",
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
                "analysis-{$exam->exam_key}-{$class}.pdf",
                'I',
                'L'
            );
        } catch (\Throwable $th) {
            return $this->handlePdfError($th, $exam, $class, 'analysis');
        }
    }

    /**
     * Prepare common PDF data structure
     */
    protected function preparePdfData(Exam $exam, string $class, string $titlePrefix): array
    {
        $exam->load(['term']);
        $results = $this->getResultsForClass($exam, $class);

        return [
            'schoolName' => "Katheka Boys Secondary School",
            'title' => "{$titlePrefix} - Term {$exam->term->term} - Form {$class}",
            'exam' => $exam,
            'class' => $class,
            'subjects' => $this->getSubjects(),
            'students' => $results->map(function ($row) {
                return [
                    'adm' => $row->student->stud_adm,
                    'name' => $row->student->stud_lname . ' ' . $row->student->stud_fname,
                    'class' => $row->student->class->class . ' ' . $row->student->stream->stream,
                    'kcpe' => $row->student->stud_kcpe_marks,
                    'scores' => $this->prepareStudentScores($row),
                    're_subC' => $row->re_subC,
                    're_tt' => $row->re_tt,
                    're_pnt' => $row->re_pnt,
                    're_avgpnt' => $row->re_avgpnt,
                    're_grade' => $row->re_grade,
                    're_sRank' => $row->re_sRank,
                    're_fRank' => $row->re_fRank,
                ];
            }),
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
        $subjectCode,
        $gradingSystem,
        $streams,
        bool $isSubjectDistribution
    ): array {
        if ($isSubjectDistribution && empty($subjectCode)) {
            throw new \InvalidArgumentException('Subject code cannot be empty for subject distribution');
        }

        $distribution = [
            'total' => $this->initializeGradeCounts($gradingSystem),
            'streams' => []
        ];

        foreach ($streams as $stream) {
            $distribution['streams'][$stream] = $this->initializeGradeCounts($gradingSystem);
        }

        foreach ($results as $result) {
            $stream = $result->student->stream->stream ?? 'Unknown';
            $value = $isSubjectDistribution
                ? $result->{"re_s{$subjectCode}"} ?? 0
                : $result->re_avgpnt ?? 0;

            $gradeInfo = $isSubjectDistribution
                ? $this->resultService->getSubjectGrade($value)
                : $this->resultService->getSubjectGrade($value, 'points');

            $this->updateDistribution($distribution['total'], $gradeInfo, $value);

            if (isset($distribution['streams'][$stream])) {
                $this->updateDistribution($distribution['streams'][$stream], $gradeInfo, $value);
            }
        }

        $this->finalizeDistribution($distribution, $gradingSystem);

        return $distribution;
    }

    /**
     * Update distribution counts for a specific group
     */
    protected function updateDistribution(&$distribution, $gradeInfo, $value): void
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
     */
    protected function getResultsForClass(Exam $exam, string $class)
    {
        return Result::with(['student', 'student.class', 'student.stream'])
            ->where('re_exam', $exam->exam_key)
            ->where('re_studF', $class)
            ->orderBy('re_pnt', 'DESC')
            ->orderBy('re_tt', 'DESC')
            ->get();
    }

    /**
     * Get grading system configuration
     */
    protected function getGradingSystem()
    {
        return GradingSystem::where('sch_token', 'kathekaboys')
            ->orderBy('grds_min', 'DESC')
            ->get();
    }

    /**
     * Get all streams
     */
    protected function getStreams()
    {
        return Stream::where('sch_token', 'kathekaboys')->pluck('stream');
    }

    /**
     * Get all subjects
     */
    protected function getSubjects()
    {
        return Subject::with('systemSubject')
            ->where('sch_token', 'kathekaboys')
            ->get();
    }

    /**
     * Handle PDF generation errors
     */
    protected function handlePdfError(\Throwable $th, Exam $exam, string $class, string $type)
    {
        Log::error("Error generating {$type} PDF: " . $th->getMessage(), [
            'exam' => $exam->id,
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
            'grade' => 'E'
        ];

        foreach ($gradingSystem as $grade) {
            $counts[$grade->grds_grade] = 0;
        }

        return $counts;
    }

    /**
     * Determine grade based on mark
     */
    protected function determineGrade($mark, $gradingSystem): array
    {
        foreach ($gradingSystem as $grade) {
            if ($mark >= $grade->grds_min && $mark <= $grade->grds_max) {
                return [
                    'grade' => $grade->grds_grade,
                    'points' => $grade->grds_point
                ];
            }
        }

        return [
            'grade' => 'E',
            'points' => 1
        ];
    }

    /**
     * Determine overall grade based on total points
     */
    protected function determineOverallGrade($totalPoints, $gradingSystem): array
    {
        $average = $totalPoints / 10; // Assuming 10 subjects
        return $this->determineGrade($average, $gradingSystem);
    }

    /**
     * Calculate metrics (mean points and overall grade)
     */
    protected function calculateMetrics(&$data, $gradingSystem): void
    {
        if ($data['entries'] > 0) {
            $data['mean_points'] = $data['total_points'] / $data['entries'];

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
     */
    protected function prepareStudentScores(Result $result): array
    {
        $droppedSubjects = $this->resultService->getDroppedSubjects($result->student);
        $subjectFields = $this->resultService->getSubjectFields();
        return $this->resultService->prepareSubjectScores($result, $droppedSubjects, $subjectFields);
    }
}
