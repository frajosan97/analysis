<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Get Subjects
    |--------------------------------------------------------------------------
    |
    | Retrieves a list of all subjects with their details (code, name, short name)
    | for the specified school. Formats the data and returns it as JSON.
    | Handles errors gracefully with appropriate logging and error responses.
    |
    | @return JsonResponse Returns JSON response containing:
    | - success: Array of subject data (code, name, short_name)
    | - error: Error message with 500 status code on failure
    |
    */
    public function subjects(): JsonResponse
    {
        try {
            $subjects = $this->fetchAndFormatSubjects();
            return response()->json($subjects);
        } catch (\Exception $e) {
            Log::error('Failed to fetch subjects: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch subjects');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Fetch and Format Subjects
    |--------------------------------------------------------------------------
    |
    | Retrieves subjects from database with their related system subject data,
    | then formats them into a standardized structure for API response.
    |
    | @return array Array of formatted subject data
    |
    */
    private function fetchAndFormatSubjects(): array
    {
        return Subject::with('systemSubject')
            ->where('sch_token', 'kathekaboys')
            ->get()
            ->map(function ($subject) {
                return $this->formatSubjectData($subject);
            })
            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | Format Subject Data
    |--------------------------------------------------------------------------
    |
    | Formats a single subject model into a standardized array structure
    | containing the essential subject information.
    |
    | @param Subject $subject The subject model instance
    | @return array Formatted subject data
    |
    */
    private function formatSubjectData(Subject $subject): array
    {
        return [
            'code' => $subject->systemSubject->sub_code,
            'name' => $subject->systemSubject->sub_name,
            'short_name' => $subject->systemSubject->sub_short_name
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Error Response
    |--------------------------------------------------------------------------
    |
    | Creates a standardized error response for API failures.
    |
    | @param string $message Error message to return
    | @param int $status HTTP status code (default: 500)
    | @return JsonResponse Error response in JSON format
    |
    */
    private function errorResponse(string $message, int $status = 500): JsonResponse
    {
        return response()->json([
            'error' => $message
        ], $status);
    }
}
