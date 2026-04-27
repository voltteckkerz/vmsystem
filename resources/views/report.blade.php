@extends('layouts.app')

@section('content')
<div class="container">
    {{-- ===== VISITOR REPORT ===== --}}
    <h3>Visitor Report</h3>
    <table class="table table-striped table-bordered mt-3">
        <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>Date</th>
                <th>Company</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Pass No.</th>
            </tr>
        </thead>
        <tbody>
            {{-- Visitor data rows will go here --}}
        </tbody>
    </table>

    <hr class="my-5"> {{-- A visible line to separate the two reports --}}

    {{-- ===== ATTENDANCE REPORT ===== --}}
    <h3>Attendance Report</h3>
    <table class="table table-striped table-bordered mt-3">
        <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>Date</th>
                <th>Vehicle No</th>
                <th>Time In</th>
                <th>Time Out</th>
            </tr>
        </thead>
        <tbody>
            {{-- Attendance data rows will go here --}}
        </tbody>
    </table>
</div>
@endsection
