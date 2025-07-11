<?php

namespace App\Imports;

use App\Models\Result;
use App\Models\Student;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;

class ResultsImport implements ToCollection, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    protected $examId;
    protected $subjectColumns = [];
    protected $skippedCount = 0;
    protected $failedCount = 0;
    protected $processedCount = 0;
    protected $updatedResults = [];

    public function __construct($examId)
    {
        $this->examId = $examId;

        // Define the subject columns
        $this->subjectColumns = [
            're_s101',
            're_s102',
            're_s121',
            're_s231',
            're_s232',
            're_s233',
            're_s311',
            're_s312',
            're_s313',
            're_s443',
            're_s451',
            're_s565',
        ];
    }

    public function collection(Collection $rows)
    {
        // Preload all existing students and their results for this exam in one query
        $studentAdms = $rows->pluck('stud_adm')->unique()->filter()->toArray();

        $students = Student::with(['results' => function ($query) {
            $query->where('re_exam', $this->examId);
        }])
            ->whereIn('stud_adm', $studentAdms)
            ->where('sch_token', 'kathekaboys')
            ->get()
            ->keyBy('stud_adm');

        foreach ($rows as $row) {
            $this->processRow($row, $students);
        }
    }

    protected function processRow($row, $students)
    {
        try {
            if (empty($row['stud_adm'])) {
                $this->skippedCount++;
                return;
            }

            // Find student from preloaded collection
            $student = $students->get($row['stud_adm']);

            if (!$student) {
                $this->skippedCount++;
                return;
            }

            // Get the first result for this exam (assuming one result per exam per student)
            $result = $student->results->first();

            if (!$result) {
                $this->skippedCount++;
                return;
            }

            // Process subject scores
            $scores = $result->scores ?? [];
            $hasUpdates = false;

            foreach ($this->subjectColumns as $subject) {
                if (isset($row[$subject])) {
                    $value = trim($row[$subject]);
                    $scoreValue = explode(' ', $value)[0];

                    if (is_numeric($scoreValue)) {
                        $newScore = (float)$scoreValue;
                        if (!isset($scores[$subject]) || $scores[$subject] != $newScore) {
                            $scores[$subject] = $newScore;
                            $hasUpdates = true;
                        }
                    }
                }
            }

            // Only update if there are changes
            if ($hasUpdates) {
                $result->update([
                    'scores' => $scores,
                    're_status' => 0
                ]);

                $this->updatedResults[] = $result->id;
                $this->processedCount++;
            }
        } catch (\Throwable $th) {
            Log::error('Error processing row for student adm: ' . ($row['stud_adm'] ?? 'unknown') . ' - ' . $th->getMessage());
            $this->failedCount++;
        }
    }

    public function rules(): array
    {
        return [];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function getProcessedCount()
    {
        return $this->processedCount;
    }

    public function getSkippedCount()
    {
        return $this->skippedCount;
    }

    public function getFailedCount()
    {
        return $this->failedCount;
    }

    public function getUpdatedResultIds()
    {
        return $this->updatedResults;
    }
}
