<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Pass;
use App\Models\Visitor;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportController extends Controller
{
    private const DATE_FORMAT = 'd/m/Y H:i'; // DD/MM/YYYY HH:MM

    private const REQUIRED_COLUMNS = [
        'visitor_name',
        'nric_passport',
        'company_name',
        'person_to_meet',
        'purpose',
        'pass_number',
        'check_in_time',
        'check_out_time',
        'remarks',
    ];

    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only admins can access this page.');
        }

        return view('import');
    }

    public function import(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only admins can access this page.');
        }

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:2048',
        ]);

        $file = $request->file('csv_file');
        $extension = strtolower($file->getClientOriginalExtension());

        // Read file into rows array based on format
        if ($extension === 'xlsx' || $extension === 'xls') {
            // Use PhpSpreadsheet for Excel files
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = [];
            foreach ($sheet->toArray() as $row) {
                $rows[] = array_map(fn($cell) => trim((string) $cell), $row);
            }
        } else {
            // CSV
            $rows = array_map('str_getcsv', file($file->getRealPath()));
            $rows = array_map(fn($row) => array_map('trim', $row), $rows);
        }

        // First row is the header
        $header = array_shift($rows);

        // Check if all required columns exist in header
        $missing = array_diff(self::REQUIRED_COLUMNS, $header);
        if (!empty($missing)) {
            return redirect()->back()->with('error', 'Missing columns in file: ' . implode(', ', $missing));
        }

        // Remove empty rows
        $rows = array_filter($rows, fn($row) => count(array_filter($row)) > 0);

        if (empty($rows)) {
            return redirect()->back()->with('error', 'File is empty (no data rows found).');
        }

        // Map each row to an associative array using the header
        $data = array_map(fn($row) => array_combine($header, $row), $rows);

        // Validate all rows first
        $errors = [];
        foreach ($data as $index => $row) {
            $rowNum = $index + 2; // +2 because row 1 is header, array is 0-indexed

            // Check required fields
            foreach (self::REQUIRED_COLUMNS as $col) {
                if ($col === 'remarks') continue; // Remarks is optional

                if (empty($row[$col])) {
                    $errors[] = "Row {$rowNum}: '{$col}' is required.";
                }
            }

            // Check employee exists
            if (!empty($row['person_to_meet'])) {
                $employee = Employee::where('name', $row['person_to_meet'])->first();
                if (!$employee) {
                    $errors[] = "Row {$rowNum}: Employee '{$row['person_to_meet']}' not found.";
                }
            }

            // Passes will be auto-created if they don't exist, so no validation error needed here.

            // Check date format and rules
            if (!empty($row['check_in_time'])) {
                try {
                    $checkIn = Carbon::createFromFormat(self::DATE_FORMAT, $row['check_in_time']);

                    if ($checkIn->isFuture()) {
                        $errors[] = "Row {$rowNum}: Check-in time cannot be in the future.";
                    }

                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNum}: Invalid check_in_time format. Use 'DD/MM/YYYY HH:MM'.";
                }
            }

            if (!empty($row['check_out_time']) && !empty($row['check_in_time'])) {
                try {
                    $checkIn = Carbon::createFromFormat(self::DATE_FORMAT, $row['check_in_time']);
                    $checkOut = Carbon::createFromFormat(self::DATE_FORMAT, $row['check_out_time']);

                    if ($checkOut->lte($checkIn)) {
                        $errors[] = "Row {$rowNum}: Check-out time must be after check-in time.";
                    }
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNum}: Invalid check_out_time format. Use 'DD/MM/YYYY HH:MM'.";
                }
            }
        }

        // If any errors, reject the entire file
        if (!empty($errors)) {
            return redirect()->back()->with('import_errors', $errors);
        }

        // All rows passed — import using a transaction
        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($data as $row) {
                // 1. Company
                $company = Company::firstOrCreate(['name' => $row['company_name']]);

                // 2. Visitor
                $visitor = Visitor::firstOrCreate(
                    ['nric_passport' => $row['nric_passport']],
                    [
                        'name' => $row['visitor_name'],
                        'company_id' => $company->id,
                    ]
                );

                // 3. Employee
                $employee = Employee::where('name', $row['person_to_meet'])->first();

                // 4. Pass (auto-create if it doesn't exist, set as Archived so it doesn't seed available passes)
                $pass = Pass::firstOrCreate(
                    ['pass_number' => $row['pass_number']],
                    ['status' => 'Archived']
                );

                // 5. Visit
                $visit = Visit::create([
                    'employee_id' => $employee->id,
                    'purpose' => $row['purpose'],
                    'remarks' => $row['remarks'] ?? null,
                    'manual_check_in_time' => Carbon::createFromFormat(self::DATE_FORMAT, $row['check_in_time']),
                    'manual_check_out_time' => Carbon::createFromFormat(self::DATE_FORMAT, $row['check_out_time']),
                    'status' => 'completed',
                ]);

                // 6. Pivot with pass
                $visit->visitors()->attach($visitor->id, ['pass_id' => $pass->id]);

                $count++;
            }

            DB::commit();
            return redirect()->back()->with('success', "Successfully imported {$count} visitor record(s).");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
