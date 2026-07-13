@extends('layouts.app')

@section('content')
{{-- flatpickr CSS must load BEFORE our <style> so our overrides win --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
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

    /* ===== ROUND-TRIP DATE RANGE PILL ===== */
    .date-range-pill {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 6px 14px;
        border: 1px solid #d6d9e0;
        border-radius: 12px;
        background: #fff;
        cursor: pointer;
        transition: border-color 0.18s, box-shadow 0.18s;
    }
    .date-range-pill:hover,
    .date-range-pill.picker-open {
        border-color: #16181d;
        box-shadow: 0 0 0 3px rgba(22,24,29,0.12);
    }
    .date-range-side { display: flex; flex-direction: column; }
    .date-range-value {
        font-weight: 600;
        font-size: 0.92rem;
        color: #16181d;
        white-space: nowrap;
    }
    /* invisible input that flatpickr attaches to */
    .date-range-anchor {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        border: 0;
        pointer-events: none;
    }
    .date-range-caption {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #9aa0ab;
        line-height: 1;
        margin-bottom: 2px;
    }
    .date-range-arrow { color: #9aa0ab; font-size: 1.05rem; }
    .date-range-reset {
        border: 0;
        background: transparent;
        padding: 0 0 0 4px;
        color: #c3c8d1;
        font-size: 1.05rem;
        cursor: pointer;
        line-height: 1;
        transition: color 0.18s;
    }
    .date-range-reset:hover { color: #dc3545; }

    /* ===== FLATPICKR POPUP — match the app's look ===== */
    .flatpickr-calendar {
        border-radius: 16px;
        border: 1px solid rgba(255,255,255,0.65);
        box-shadow: 0 12px 40px rgba(16,24,40,0.22);
    }
    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange {
        background: #16181d;
        border-color: #16181d;
    }
    .flatpickr-day.selected:hover,
    .flatpickr-day.startRange:hover,
    .flatpickr-day.endRange:hover {
        background: #16181d;
        border-color: #16181d;
    }
    .flatpickr-day.inRange {
        background: #e8eaef;
        border-color: #e8eaef;
        box-shadow: -5px 0 0 #e8eaef, 5px 0 0 #e8eaef;
    }
    .flatpickr-day.startRange.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange {
        box-shadow: none;
    }
    .flatpickr-day.today { border-color: #16181d; }
    /* Reset / Done footer inside the popup */
    .fp-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 14px;
        margin-top: 4px;
        border-top: 1px solid #e8eaef;
    }
    .fp-footer .fp-reset {
        border: 0;
        background: transparent;
        color: #4a6cf7;
        font-weight: 600;
        font-size: 0.88rem;
        cursor: pointer;
    }
    .fp-footer .fp-done {
        border: 0;
        border-radius: 999px;
        padding: 6px 22px;
        background: #16181d;
        color: #fff;
        font-weight: 600;
        font-size: 0.88rem;
        cursor: pointer;
    }
    .fp-footer .fp-done:hover { filter: brightness(1.3); }

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
        /* Date pill: stretch across, halves share the row evenly */
        .date-range-pill { display: flex; width: 100%; }
        .date-range-side { flex: 1; align-items: center; }
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
                    <label class="form-label text-muted"><b>Date Range</b></label>
                    <div class="date-range-pill" id="dateRangePill">
                        <div class="date-range-side">
                            <span class="date-range-caption">From</span>
                            <span class="date-range-value" id="fromDisplay">{{ \Carbon\Carbon::parse($from_date)->format('D, j M') }}</span>
                        </div>
                        <i class="bi bi-arrow-right date-range-arrow"></i>
                        <div class="date-range-side">
                            <span class="date-range-caption">To</span>
                            <span class="date-range-value" id="toDisplay">{{ \Carbon\Carbon::parse($to_date)->format('D, j M') }}</span>
                        </div>
                        <button type="button" class="date-range-reset" id="resetDates" title="Reset to today">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                        <input type="text" id="rangePicker" class="date-range-anchor" tabindex="-1" aria-hidden="true">
                        <input type="hidden" name="from_date" id="fromDate" value="{{ $from_date }}">
                        <input type="hidden" name="to_date" id="toDate" value="{{ $to_date }}">
                    </div>
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

<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
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

    // ===== Round-trip popup calendar =====
    const fromDate    = document.getElementById('fromDate');
    const toDate      = document.getElementById('toDate');
    const fromDisplay = document.getElementById('fromDisplay');
    const toDisplay   = document.getElementById('toDisplay');
    const pill        = document.getElementById('dateRangePill');

    const fmtDisplay = d => d.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short' });
    const fmtValue   = d => flatpickr.formatDate(d, 'Y-m-d');

    // Reset: snap the selection back to today (no page reload —
    // the table updates when the user presses Filter)
    function resetDates() {
        const today = new Date();
        fp.setDate([today, today], false);
        fp.jumpToDate(today);
        fromDate.value = fmtValue(today);
        toDate.value = fmtValue(today);
        fromDisplay.textContent = fmtDisplay(today);
        toDisplay.textContent = fmtDisplay(today);
    }

    const fp = flatpickr('#rangePicker', {
        mode: 'range',
        closeOnSelect: false,
        showMonths: window.matchMedia('(max-width: 767.98px)').matches ? 1 : 2,
        defaultDate: [fromDate.value, toDate.value],
        disableMobile: true,
        positionElement: pill,
        onReady(_, __, instance) {
            const footer = document.createElement('div');
            footer.className = 'fp-footer';
            footer.innerHTML = '<button type="button" class="fp-reset">Reset</button>'
                             + '<button type="button" class="fp-done">Done</button>';
            footer.querySelector('.fp-reset').addEventListener('click', resetDates);
            footer.querySelector('.fp-done').addEventListener('click', () => instance.close());
            instance.calendarContainer.appendChild(footer);
        },
        onOpen()  { pill.classList.add('picker-open'); },
        onChange(dates) {
            if (dates.length >= 1) {
                fromDate.value = fmtValue(dates[0]);
                fromDisplay.textContent = fmtDisplay(dates[0]);
            }
            if (dates.length === 2) {
                toDate.value = fmtValue(dates[1]);
                toDisplay.textContent = fmtDisplay(dates[1]);
            }
        },
        onClose(dates, _, instance) {
            pill.classList.remove('picker-open');
            // Picked only one day, then closed: treat it as a same-day range
            if (dates.length === 1) {
                toDate.value = fmtValue(dates[0]);
                toDisplay.textContent = fmtDisplay(dates[0]);
                instance.setDate([dates[0], dates[0]], false);
            }
        }
    });

    // Clicking anywhere on the pill (either date) opens the popup
    pill.addEventListener('click', e => {
        if (e.target.closest('.date-range-reset')) return;
        fp.open();
    });

    document.getElementById('resetDates').addEventListener('click', resetDates);
</script>
@endsection
