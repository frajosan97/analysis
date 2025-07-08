<?php

namespace App\Services;

use App\Models\GradingCat;
use App\Models\GradingSystem;
use App\Models\Student;
use App\Models\Result;
use App\Models\Subject;
use Illuminate\Support\Facades\Log;

class ResultService
{
    public function getSubjectGrade(float $marks, $gradeBy = 'marks'): ?array
    {
        try {
            $gradingCat = GradingCat::where('sch_token', 'kathekaboys')->first();

            if ($gradingCat) {
                if ($gradeBy == 'points') {
                    $grade = GradingSystem::where('grds_cat_key', $gradingCat->grd_key)
                        ->where('grds_point', '<=', round($marks))
                        ->first();
                } else {
                    $grade = GradingSystem::where('grds_cat_key', $gradingCat->grd_key)
                        ->where('grds_min', '<=', $marks)
                        ->where('grds_max', '>=', $marks)
                        ->first();
                }

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

    public function getDroppedSubjects(Student $student): array
    {
        try {
            if (empty($student->stud_drop_sub)) {
                return [];
            }

            $decoded = json_decode($student->stud_drop_sub, true);
            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable $th) {
            Log::error('Error getting dropped subject: ' . $th->getMessage());
            return [];
        }
    }

    public function getSubjectFields(): array
    {
        try {
            $subjectFields = [];
            $subjects = Subject::all();

            foreach ($subjects as $subject) {
                $subjectFields[] = 're_s' . $subject->sch_sub_code;
            }

            return $subjectFields;
        } catch (\Throwable $th) {
            Log::error('Error getting subject fields: ' . $th->getMessage());
            return [];
        }
    }

    public function prepareSubjectScores(Result $result, array $droppedSubjects, array $subjectFields): array
    {
        try {
            $scores = [];

            foreach ($subjectFields as $field) {
                $subjectCode = substr($field, 4);

                if (in_array($subjectCode, $droppedSubjects)) {
                    $scores[$subjectCode] = '--';
                    continue;
                }

                if ($result->$field) {
                    $gradeInfo = $this->getSubjectGrade($result->$field);
                    $scores[$subjectCode] = $gradeInfo
                        ? $result->$field . ' ' . $gradeInfo['grade']
                        : $result->$field;

                    // $scores[$subjectCode] = $result->$field;
                } else {
                    $scores[$subjectCode] = '--';
                }
            }

            return $scores;
        } catch (\Throwable $th) {
            Log::error('Error getting subject fields: ' . $th->getMessage());
            return [];
        }
    }
}
