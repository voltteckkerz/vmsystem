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

        return view('visitor', compact('companies', 'employees', 'availablePasses', 'registeredVisitors'));
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

        // 1. Create the main Visit Event
        $visit = Visit::create([
            'employee_id' => $request->employee_id,
            'purpose' => $request->purpose,
            'remarks' => $request->remarks,
            'manual_check_in_time' => $request->manual_check_in_time,
            'status' => 'active',
        ]);

        // 2. Loop through each submitted visitor
        foreach ($request->nric_passport as $index => $nric) {
            // Find or create the specific company for this visitor
            $company = Company::firstOrCreate(['name' => $request->company_name[$index]]);
            
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
        // Search the database for this exact NRIC, and pull their Company data too
        $visitor = \App\Models\Visitor::with('company')->where('nric_passport', $nric)->first();
        
        // Return the data as JSON so JavaScript can read it
        return response()->json($visitor);
    }

}
