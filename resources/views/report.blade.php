@extends('layouts.app')

@section('content')
<div class="container">
    {{-- ===== DATE FILTER ===== --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius:10px;">
        <div class="card-body">
            <form method="GET" action="{{ route('report.index') }}" class="d-flex align-items-end gap-3 w-100">
                <div>
                    <label class="form-label text-muted"><b>Select Date</b></label>
                    <input type="date" class="form-control" name="date" value="{{ $date }}">
                </div>
                <button type="submit" class="btn btn-primary px-4">Filter</button>
                {{-- Print Button (target="blank" opens in a new tab!) --}}
                <div class="ms-auto">
                    <a href="{{ route('report.print', ['type' => 'visitor', 'date' => $date] ) }}" target="_blank" class="btn btn-success">Print Visitor PDF</a>
                    <a href="{{ route('report.print', ['type' => 'attendance', 'date' => $date] ) }}" target="_blank" class="btn btn-info">Print Attendance PDF</a>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== TABS ===== --}}
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#visitor-tab" type="button"><b>Visit Report</b></button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#attendance-tab" type="button"><b>Attendance Report</b></button>
        </li>
    </ul>

    <div class="tab-content mt-3">

    {{-- ===== Table 2: Visitor Report ===== --}}
    <div class="tab-pane fade show active" id="visitor-tab">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Company</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Pass</th>
                </tr>
            </thead>
            <tbody>
                @php $counter = 1; @endphp
                @foreach($visits as $visit)
                    @foreach($visit->visitors as $visitor)
                    <tr>
                        <td>{{ $counter++ }}</td>
                        <td>{{ $visitor->name}}</td>
                        <td>{{ \Carbon\Carbon::parse($visit->date)->format('d/m/Y') }}</td>
                        <td>{{ $visitor->company->name ?? '_'}}</td>
                        <td>{{ \Carbon\Carbon::parse($visit->manual_check_in_time)->format('h:i A') }}</td>
                        <td>
                            @if($visit->manual_check_out_time)
                                {{ \Carbon\Carbon::parse($visit->manual_check_out_time)->format('h:i A') }}
                                @else
                                -
                                @endif
                        </td>
                        <td>
                            @php
                                $passId = $visitor->pivot->pass_id;
                                $pass = $passId ? \App\Models\Pass::find($passId) : null;
                                @endphp
                                {{ $pass->pass_number ?? '_' }}
                        </td>
                    </tr>
                    @endforeach
                @endforeach

                @if($visits->isEmpty())
                <tr>
                    <td colspan="7" class="text-center">No visitor records for this date.</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- ===== Table 2: Attendance Report ===== --}}
    <div class="tab-pane fade" id="attendance-tab">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Vehicle No</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $index => $attendance)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $attendance->employee->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</td>
                    <td>{{ $attendance->vehicle_plate ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->check_in_time)->format('h:i A') }}</td>
                    <td>
                        @if($attendance->check_out_time)
                            {{ \Carbon\Carbon::parse($attendance->check_out_time)->format('h:i A') }}
                            @else
                            -
                            @endif
                    </td>
                </tr>
                @endforeach

                @if($attendances->isEmpty())
                <tr>
                    <td colspan="6" class="text-center">No attendance records for this date.</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
