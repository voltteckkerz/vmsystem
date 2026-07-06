@extends('layouts.app')

@section('content')
<style>
    body {
        background-image: url('{{ asset(str_replace(' ', '%20', 'images/login background.jpg')) }}?v={{ filemtime(public_path('images/login background.jpg')) }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
    }
    /* ===== REPORT NAV TABS — pill segments ===== */
    .report-nav { border-bottom: none; gap: 8px; margin-bottom: 16px; }
    .report-nav .nav-item { margin-bottom: 0; }
    .report-nav .nav-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 26px;
        font-weight: 700;
        font-size: 0.92rem;
        color: #6b6f78;
        border: 1px solid rgba(255,255,255,0.65);
        border-radius: 999px;
        background: rgba(255,255,255,0.78);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow: 0 4px 14px rgba(16,24,40,0.08);
        transition: all 0.18s;
        cursor: pointer;
    }
    .report-nav .nav-link i { font-size: 1.05rem; }
    .report-nav .nav-link:hover {
        color: #16181d;
        background: #fff;
    }
    .report-nav .nav-link.active-visitor,
    .report-nav .nav-link.active-attendance {
        color: #fff;
        background: #16181d;
        border-color: #16181d;
        box-shadow: 0 6px 18px rgba(22,24,29,0.28);
    }
    /* ===== GLASS TABLE CONTAINER ===== */
    .tab-content {
        background: rgba(255,255,255,0.90);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.65);
        border-radius: 18px;
        box-shadow: 0 8px 32px rgba(16,24,40,0.12);
        padding: 16px;
        margin-bottom: 24px;
        overflow: hidden;
    }
    .tab-content .table { margin-bottom: 0; }
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
    .print-btn { border-radius: 999px; }
    .print-btn-visitor    { background: #16181d; color: #fff; }
    .print-btn-attendance { background: #16a34a; color: #fff; }
    .print-btn i { font-size: 1rem; }
    /* Tables scroll sideways inside the glass card instead of breaking the page */
    .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }

    /* ===== PHONE LAYOUT ===== */
    @media (max-width: 767.98px) {
        /* Tabs: split 50/50 across the screen */
        .report-nav { display: flex; gap: 8px; }
        .report-nav .nav-item { flex: 1; }
        .report-nav .nav-link {
            width: 100%;
            justify-content: center;
            padding: 10px 6px;
            font-size: 0.82rem;
            gap: 6px;
        }
        /* Filter form: stack everything full width */
        #filter-form { flex-direction: column; align-items: stretch; gap: 12px !important; }
        #filter-form > div { width: 100%; }
        #filter-form .form-control { width: 100%; }
        #filter-form button[type="submit"] { width: 100%; }
        /* Print buttons: full width, stacked */
        #filter-form .ms-auto {
            margin-left: 0 !important;
            width: 100%;
            flex-direction: column;
        }
        .print-btn { width: 100%; justify-content: center; padding: 12px 20px; }
        /* Table card: tighter padding, compact rows */
        .tab-content { padding: 10px; }
        .tab-content .table { font-size: 0.8rem; white-space: nowrap; }
        .tab-content .table th, .tab-content .table td { padding: 8px 10px; }
    }
</style>

<div class="container">

    {{-- ===== REPORT TYPE TABS ===== --}}
    <ul class="nav report-nav" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('tab', 'visitor') === 'visitor' ? 'active-visitor' : '' }}"
                id="btn-visitor-tab"
                type="button"
                onclick="switchTab('visitor')">
                <i class="bi bi-people-fill"></i>
                Visit Report
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('tab', 'visitor') === 'attendance' ? 'active-attendance' : '' }}"
                id="btn-attendance-tab"
                type="button"
                onclick="switchTab('attendance')">
                <i class="bi bi-clock-history"></i>
                Attendance Report
            </button>
        </li>
    </ul>

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
            <div class="table-scroll">
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
                            <td>{{ $visitor->pivot->visitor_name ?? $visitor->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($visit->manual_check_in_time)->format('d/m/Y') }}</td>
                            <td>{{ $visitor->pivot->visitor_company ?? ($visitor->company->name ?? '_') }}</td>
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
        </div>

        {{-- ===== Table 2: Attendance Report ===== --}}
        <div class="tab-pane fade {{ request('tab', 'visitor') === 'attendance' ? 'show active' : '' }}" id="attendance-tab">
            <div class="table-scroll">
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
        </div>

    </div>{{-- end tab-content --}}
</div>

<script>
    function switchTab(tab) {
        document.getElementById('activeTab').value = tab;

        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('show', 'active'));
        document.getElementById(tab + '-tab').classList.add('show', 'active');

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
