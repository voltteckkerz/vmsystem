<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    // Show all employees
    public function index()
    {
        $employees = Employee::with('vehicles')->orderBy('name')->get();
        return view('employees', compact('employees'));
    }

    // Add a new employee (with optional vehicle plates)
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'plate_1'   => 'nullable|string|max:20',
            'plate_2'   => 'nullable|string|max:20',
        ]);

        $name = strtoupper(trim($request->name));

        // Prevent duplicate names
        if (Employee::whereRaw('UPPER(name) = ?', [$name])->exists()) {
            return redirect()->back()->with('error', "Employee \"{$name}\" already exists.");
        }

        DB::transaction(function () use ($name, $request) {
            $employee = Employee::create([
                'name'   => $name,
                'status' => 'active',
            ]);

            // Attach vehicle plates if provided
            foreach (['plate_1', 'plate_2'] as $field) {
                $plate = strtoupper(trim($request->input($field, '')));
                if ($plate !== '') {
                    $vehicle = Vehicle::firstOrCreate(
                        ['plate_number' => $plate],
                        ['owner_type'   => 'employee']
                    );
                    $employee->vehicles()->syncWithoutDetaching([$vehicle->id]);
                }
            }
        });

        return redirect()->route('attendance.index')->with('success', "Employee \"{$name}\" added successfully.");
    }

    // Deactivate an employee (soft — preserves attendance history)
    public function deactivate($id)
    {
        $employee = Employee::findOrFail($id);

        // Block if currently clocked in
        if ($employee->attendances()->where('status', 'clocked_in')->exists()) {
            return redirect()->back()->with('error',
                "\"{$employee->name}\" is currently clocked in. Please clock them out first.");
        }

        $employee->status = 'inactive';
        $employee->save();

        return redirect()->route('attendance.index')
            ->with('success', "\"{$employee->name}\" has been marked as inactive.");
    }

    // Re-activate an employee
    public function reactivate($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->status = 'active';
        $employee->save();

        return redirect()->route('attendance.index')
            ->with('success', "\"{$employee->name}\" has been re-activated.");
    }

    // Permanently delete an employee
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        if ($employee->attendances()->where('status', 'clocked_in')->exists()) {
            return redirect()->back()->with('error',
                "\"{$employee->name}\" is currently clocked in. Please clock them out first.");
        }

        $name = $employee->name;
        $employee->delete(); // cascade deletes employees_vehicles rows

        return redirect()->route('attendance.index')
            ->with('success', "\"{$name}\" has been permanently deleted.");
    }
}
