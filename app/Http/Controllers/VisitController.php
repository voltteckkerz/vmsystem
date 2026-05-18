<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Pass;
use App\Models\Visitor;
use App\Models\Visit;
class VisitController extends Controller
{
    public function create()
    {
        $companies = Company::all();
        $employees = Employee::all();
        $availablePasses = Pass::where('status', 'available')->get();
        $registeredVisitors = Visitor::with('company')->orderBy('name')->get();

        // Get NRICs of visitors who are currently checked in (active visits)
        $activeVisitorNrics = Visitor::whereHas('visits', function ($q) {
            $q->where('status', 'active');
        })->pluck('nric_passport')->toArray();

        return view('visitor', compact('companies', 'employees', 'availablePasses', 'registeredVisitors', 'activeVisitorNrics'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'company_name' => 'required|array|min:1|max:5',
            'company_name.*' => 'required|string',
            'purpose' => 'required|string',
            'nric_passport' => 'required|array|min:1|max:5',
            'nric_passport.*' => 'required|string',
            'visitor_name' => 'required|array|min:1|max:5',
            'visitor_name.*' => 'required|string',
            'pass_id' => 'required|array|min:1|max:5',
            'pass_id.*' => 'required|exists:passes,id|distinct', // distinct prevents duplicate passes!
        ]);

        // Check if any visitor already has an active visit
        foreach ($request->nric_passport as $rawNric) {
            $cleanNric = str_replace('-', '', $rawNric);
            $visitor = Visitor::where('nric_passport', $cleanNric)->first();
            if ($visitor) {
                $activeVisit = $visitor->visits()->where('status', 'active')->exists();
                if ($activeVisit) {
                    return redirect()->back()->with('error', "Visitor '{$visitor->name}' is already checked in.");
                }
            }
        }

        // 1. Create the main Visit Event
        $visit = Visit::create([
            'employee_id' => $request->employee_id,
            'purpose' => $request->purpose,
            'remarks' => $request->remarks,
            'manual_check_in_time' => $request->manual_check_in_time,
            'status' => 'active',
        ]);

        // 2. Loop through each submitted visitor
        foreach ($request->nric_passport as $index => $rawNric) {
            // Strip dashes — store as 12 digits only
            $nric = str_replace('-', '', $rawNric);
            // Find or create the specific company for this visitor
            $company = Company::firstOrCreate(['name' => $request->company_name[$index]]);
            
            // Check if NRIC already exists with different name or company
            $existing = Visitor::where('nric_passport', $nric)->first();
            if ($existing) {
                $submittedName = $request->visitor_name[$index];
                if (strtolower($existing->name) !== strtolower($submittedName)) {
                    return redirect()->back()->with('error', "NRIC '{$nric}' is already registered under '{$existing->name}', but you entered '{$submittedName}'. Please verify the details.");
                }
                if ($existing->company_id !== $company->id) {
                    $existingCompany = $existing->company->name ?? 'Unknown';
                    return redirect()->back()->with('error', "Visitor '{$existing->name}' ({$nric}) is registered under '{$existingCompany}', but you entered '{$company->name}'. Please verify the details.");
                }
            }

            // Find or create the specific visitor
            $visitor = Visitor::firstOrCreate(
                ['nric_passport' => $nric],
                [
                    'name' => $request->visitor_name[$index],
                    'company_id' => $company->id
                ]
            );
            
            $pass_id = $request->pass_id[$index];
            
            // Attach to the pivot table
            $visit->visitors()->attach($visitor->id, [
                'pass_id' => $pass_id,
            ]);
            
            // Mark the pass as in-use
            $pass = Pass::find($pass_id);
            $pass->status = 'in_use';
            $pass->save();
        }
       
        return redirect('/dashboard')->with('success', 'Visitors Successfully Registered!');
    }

    public function checkout(Request $request, $id)
    {
        $visit = Visit::findOrFail($id);

        // Reject if checkout time is before or equal to check-in time
        if ($request->manual_check_out_time <= $visit->manual_check_in_time) {
            return redirect()->back()->with('error', 'Check-out time cannot be before or equal to check-in time.');
        }

        $visit->status = 'completed';
        $visit->manual_check_out_time = $request->manual_check_out_time;
        $visit->save();

        // Free up the physical passes so they can be reused
        foreach ($visit->visitors as $visitor) {
            $passId = $visitor->pivot->pass_id;
            if ($passId) {
                $pass = Pass::find($passId);
                if ($pass) {
                    $pass->status = 'available';
                    $pass->save();
                }
            }
        }

        return redirect('/dashboard')->with('success', 'Visit Checked Out Successfully!');
    }
        
    public function findVisitor($nric)
    {
        $cleanNric = str_replace('-', '', $nric);
        $visitor = \App\Models\Visitor::with('company')->where('nric_passport', $cleanNric)->first();
        
        return response()->json($visitor);
    }

}
