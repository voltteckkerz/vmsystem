@extends('layouts.app')

@section('content')
<style>
    /* ===== REPORT TYPE TOGGLE BUTTONS ===== */
    .report-type-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 18px 32px;
        border-radius: 12px;
        border: 2.5px solid #dee2e6;
        background: #fff;
        color: #6c757d;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s ease;
        min-width: 170px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        text-decoration: none;
    }
    .report-type-btn i { font-size: 1.6rem; transition: transform 0.2s; }
    .report-type-btn:hover {
        border-color: #0d6efd;
        color: #0d6efd;
        box-shadow: 0 4px 16px rgba(13,110,253,0.13);
        transform: translateY(-2px);
    }
    .report-type-btn.active-visitor {
        border-color: #0d6efd;
        background: linear-gradient(135deg, #e8f0fe 0%, #f0f6ff 100%);
        color: #0d6efd;
        box-shadow: 0 4px 16px rgba(13,110,253,0.18);
    }
    .report-type-btn.active-attendance {
        border-color: #28a745;
        background: linear-gradient(135deg, #e6f9ec 0%, #f0fdf4 100%);
        color: #28a745;
        box-shadow: 0 4px 16px rgba(40,167,69,0.18);
    }
    /* ===== PRINT BUTTONS ===== */
    .print-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.88rem;
        border: none;
        cursor: pointer;
        transition: all 0.18s;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        white-space: nowrap;
    }
    .print-btn:hover { transform: translateY(-1px); filter: brightness(0.92); box-shadow: 0 4px 14px rgba(0,0,0,0.18); }
    .print-btn-visitor    { background: #0d6efd; color: #fff; }
    .print-btn-attendance { background: #28a745; color: #fff; }
    .print-btn i { font-size: 1rem; }
</style>

<div class="container">

    {{-- ===== REPORT TYPE SELECTOR ===== --}}
    <div class="d-flex justify-content-center gap-3 mb-4">
        <button type="button"
            class="report-type-btn {{ request('tab', 'visitor') === 'visitor' ? 'active-visitor' : '' }}"
            id="btn-visitor-tab"
            onclick="switchTab('visitor')">
            <i class="bi bi-people-fill"></i>
            Visit Report
        </button>
        <button type="button"
            class="report-type-btn {{ request('tab', 'visitor') === 'attendance' ? 'active-attendance' : '' }}"
            id="btn-attendance-tab"
            onclick="switchTab('attendance')">
            <i class="bi bi-clock-history"></i>
            Attendance Report
        </button>
    </div>

    {{-- ===== DATE RANGE FILTER ===== --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius:10px;">
        <div class="card-body">
            <form method="GET" action="{{ route('report.index') }}" class="d-flex align-items-end gap-3 flex-wrap w-100" id="filter-form">
                <div>
                    <label class="form-label text-muted"><b>From Date</b></label>
                    <input type="date" class="form-control" name="from_date" value="{{ $from_date }}">
                </div>
                <div>
                    <label class="form-label text-muted"><b>To Date</b></label>
                    <input type="date" class="form-control" name="to_date" value="{{ $to_date }}">
                </div>
                <div>
                    <label class="form-label text-muted"><b>Search Name</b></label>
                    <input type="text" class="form-control" name="name" value="{{ request('name') }}" placeholder="e.g. John Doe">
                </div>
                <input type="hidden" name="tab" id="activeTab" value="{{ request('tab', 'visitor') }}">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-funnel-fill me-1"></i>Filter
                </button>

                {{-- Print Buttons --}}
                <div class="ms-auto d-flex gap-2">
                    <a href="{{ route('report.print', ['filename' => 'Visitor_Report.pdf', 'type' => 'visitor', 'from_date' => $from_date, 'to_date' => $to_date, 'name' => request('name')]) }}"
                        target="_blank" class="print-btn print-btn-visitor">
                        <i class="bi bi-printer-fill"></i>
                        Print Visit Report
                    </a>
                    <a href="{{ route('report.print', ['filename' => 'Attendance_Report.pdf', 'type' => 'attendance', 'from_date' => $from_date, 'to_date' => $to_date, 'name' => request('name')]) }}"
                        target="_blank" class="print-btn print-btn-attendance">
                        <i class="bi bi-printer-fill"></i>
                        Print Attendance
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== TAB CONTENT ===== --}}
    <div class="tab-content">

        {{-- ===== Table 1: Visitor Report ===== --}}
        <div class="tab-pane fade {{ request('tab', 'visitor') === 'visitor' ? 'show active' : '' }}" id="visitor-tab">
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
                            <td>{{ $visitor->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($visit->manual_check_in_time)->format('d/m/Y') }}</td>
                            <td>{{ $visitor->company->name ?? '_' }}</td>
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
                        <td colspan="7" class="text-center text-muted py-3">No visitor records for this date range.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- ===== Table 2: Attendance Report ===== --}}
        <div class="tab-pane fade {{ request('tab', 'visitor') === 'attendance' ? 'show active' : '' }}" id="attendance-tab">
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
                        <td>{{ \Carbon\Carbon::parse($attendance->check_in_time)->format('d/m/Y') }}</td>
                        <td>{{ $attendance->vehicle_plate ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($attendance->check_in_time)->format('h:i A') }}</td>
                        <td>
                            @if($attendance->check_out_time)
                                @php
                                    $inDate  = \Carbon\Carbon::parse($attendance->check_in_time)->toDateString();
                                    $outDate = \Carbon\Carbon::parse($attendance->check_out_time)->toDateString();
                                @endphp
                                {{ \Carbon\Carbon::parse($attendance->check_out_time)->format('h:i A') }}
                                @if($outDate !== $inDate)
                                    <br><small class="text-muted">({{ \Carbon\Carbon::parse($attendance->check_out_time)->format('d/m/Y') }})</small>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endforeach

                    @if($attendances->isEmpty())
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">No attendance records for this date range.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

    </div>{{-- end tab-content --}}
</div>

<script>
    function switchTab(tab) {
        // Update hidden input so filter form remembers the tab
        document.getElementById('activeTab').value = tab;

        // Toggle tab pane visibility
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('show', 'active'));
        document.getElementById(tab + '-tab').classList.add('show', 'active');

        // Update toggle button styles
        const visitorBtn    = document.getElementById('btn-visitor-tab');
        const attendanceBtn = document.getElementById('btn-attendance-tab');
        visitorBtn.classList.remove('active-visitor', 'active-attendance');
        attendanceBtn.classList.remove('active-visitor', 'active-attendance');

        if (tab === 'visitor') {
            visitorBtn.classList.add('active-visitor');
        } else {
            attendanceBtn.classList.add('active-attendance');
        }
    }
</script>
@endsection
