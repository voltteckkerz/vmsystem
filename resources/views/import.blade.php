@extends('layouts.app')

@section('content')
<div class="container">

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Validation Errors (row-by-row) --}}
    @if(session('import_errors'))
        <div class="alert alert-danger">
            <strong>Import failed — please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px;">
        <div class="card-header bg-white border-0 pt-4 pb-2">
            <h4 class="mb-0"><b>Import Visitor Data (CSV)</b></h4>
        </div>
        <div class="card-body">
            <p class="text-muted">
                Upload a CSV or Excel file to import old visitor records into the system.<br>
                Supported formats: <strong>.csv</strong>, <strong>.xlsx</strong>, <strong>.xls</strong><br>
                Only data from the <strong>past 1 month</strong> is allowed. All 9 columns are required.
            </p>

            <div class="mb-4">
                <a href="{{ asset('templates/visitor_import_template.csv') }}" class="btn btn-outline-primary btn-sm" download>
                    ⬇ Download CSV Template
                </a>
            </div>

            <form method="POST" action="{{ route('import.upload') }}" enctype="multipart/form-data">
                @csrf
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label class="form-label text-muted"><b>Select File (CSV or Excel)</b></label>
                        <input type="file" class="form-control" name="csv_file" accept=".csv,.xlsx,.xls" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary px-4">Upload & Import</button>
                    </div>
                </div>
            </form>

            <hr class="my-4">

            <h6 class="text-muted"><b>CSV Format Reference</b></h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Column</th>
                            <th>Example</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>visitor_name</td><td>John Doe</td><td>Full name</td></tr>
                        <tr><td>nric_passport</td><td>980101-14-1234</td><td>Unique ID</td></tr>
                        <tr><td>company_name</td><td>TechCorp Sdn Bhd</td><td>Auto-created if new</td></tr>
                        <tr><td>person_to_meet</td><td>Ahmad</td><td>Must match an employee name</td></tr>
                        <tr><td>purpose</td><td>Meeting</td><td>Reason for visit</td></tr>
                        <tr><td>pass_number</td><td>P001</td><td>Must match a pass in the system</td></tr>
                        <tr><td>check_in_time</td><td>01/05/2026 09:00</td><td>Format: DD/MM/YYYY HH:MM</td></tr>
                        <tr><td>check_out_time</td><td>01/05/2026 17:00</td><td>Must be after check-in</td></tr>
                        <tr><td>remarks</td><td>Delivered equipment</td><td>Visit remarks</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
