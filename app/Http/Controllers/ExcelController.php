<?php

namespace App\Http\Controllers;

use App\Imports\ResultsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'exam_id' => 'required',
            'class_id' => 'required',
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $file = $request->file('file');

        Excel::import(new ResultsImport($request->exam_id), $file);

        return response()->json([
            'success' => true,
            'message' => 'uploaded successfully'
        ]);
    }
}
