<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Result;
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

    /**
     * Generate merit list PDF for a specific exam and class
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Exam $exam
     * @param string $class
     * @return mixed
     */
    public function merit(Request $request, Exam $exam, string $class)
    {
        try {
            // Eager load necessary relationships
            $exam->load(['term']);

            // Get results with student data, ordered by performance
            $results = Result::with(['student'])
                ->where('re_exam', $exam->exam_key)
                ->where('re_studF', $class)
                ->orderBy('re_pnt', 'DESC')
                ->orderBy('re_tt', 'DESC')
                ->get();

            // Prepare data for PDF
            $data = [
                'schoolName' => "katheka boys secondary school",
                'title' => "Merit List - {$exam->term->term} - Class {$class}",
                'exam' => $exam,
                'class' => $class,
                'subjects' => Subject::with('systemSubject')
                    ->where('sch_token', 'kathekaboys')
                    ->get(),
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
                'overalGradesDist' => [
                    
                ]
            ];

            return $this->pdfService->generatePdfFromView(
                'pdf.merit',
                $data,
                "merit-list-{$exam->exam_key}-{$class}.pdf",
                'I',
                'L'
            );
        } catch (\Throwable $th) {
            Log::error('Error generating merit list PDF: ' . $th->getMessage(), [
                'exam' => $exam->id,
                'class' => $class,
                'trace' => $th->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to generate merit list. Please try again.');
        }
    }

    /**
     * Prepare student subject scores
     *
     * @param \App\Models\Result $result
     * @return array
     */
    protected function prepareStudentScores(Result $result)
    {
        $droppedSubjects = $this->getDroppedSubjects($result->student);
        $subjectFields = $this->getSubjectFields();

        return $this->prepareSubjectScores($result, $droppedSubjects, $subjectFields);
    }
}
