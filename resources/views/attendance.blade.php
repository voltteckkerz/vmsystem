@extends('layouts.app')

@section('content')
<style>
    tr.selected-employee > td {
        background-color: #0d6efd !important;
        color: white !important;
        font-weight: 600;
    }
    tr.selected-attendance > td {
        background-color: #dc3545 !important;
        color: white !important;
        font-weight: 600;
    }
    .table-hover tbody tr.selected-employee:hover > td { background-color: #0b5ed7 !important; }
    .table-hover tbody tr.selected-attendance:hover > td { background-color: #c82333 !important; }
    #clockin-deviation-warning { display: none; margin-top: 8px; }

    /* ===== ICON BUTTONS ===== */
    .icon-btn {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        border: 2px solid #ced4da;
        background: transparent;
        color: #ced4da;
        font-size: 1.15rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: not-allowed;
        transition: background 0.18s, color 0.18s, border-color 0.18s, box-shadow 0.18s, transform 0.15s, filter 0.15s;
        position: relative;
    }
    .icon-btn:disabled,
    .icon-btn.inactive {
        border-color: #ced4da !important;
        color: #ced4da !important;
        background: transparent !important;
        cursor: not-allowed;
        box-shadow: none !important;
        transform: none !important;
        filter: none !important;
    }
    /* Active (enabled) state — solid filled background */
    .icon-btn.active-btn { cursor: pointer; border-color: transparent; color: #fff; }
    .icon-btn.active-btn.btn-clockin    { background: #28a745; box-shadow: 0 3px 10px rgba(40,167,69,0.45); }
    .icon-btn.active-btn.btn-clockout   { background: #dc3545; box-shadow: 0 3px 10px rgba(220,53,69,0.45); }
    .icon-btn.active-btn.btn-correct    { background: #0d6efd; box-shadow: 0 3px 10px rgba(13,110,253,0.45); }
    .icon-btn.active-btn.btn-remove-att { background: #fd7e14; box-shadow: 0 3px 10px rgba(253,126,20,0.45); }
    .icon-btn.active-btn.btn-remove-emp { background: #dc3545; box-shadow: 0 3px 10px rgba(220,53,69,0.45); }
    .icon-btn.active-btn.btn-add-emp    { background: #28a745; box-shadow: 0 3px 10px rgba(40,167,69,0.45); }
    /* Hover — slightly darker + scale */
    .icon-btn.active-btn:hover { transform: scale(1.1); filter: brightness(0.88); }
    /* Tooltip label under icon */
    .icon-btn-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }
    .icon-btn-label {
        font-size: 0.62rem;
        color: #adb5bd;
        white-space: nowrap;
        transition: color 0.18s;
    }
    /* Labels match button color when active */
    .icon-btn.active-btn.btn-clockin    + .icon-btn-label { color: #28a745; }
    .icon-btn.active-btn.btn-clockout   + .icon-btn-label { color: #dc3545; }
    .icon-btn.active-btn.btn-correct    + .icon-btn-label { color: #0d6efd; }
    .icon-btn.active-btn.btn-remove-att + .icon-btn-label { color: #fd7e14; }
    .icon-btn.active-btn.btn-remove-emp + .icon-btn-label { color: #dc3545; }
    .icon-btn.active-btn.btn-add-emp    + .icon-btn-label { color: #28a745; }
    /* Divider */
    .center-divider { border-top: 1px dashed #dee2e6; margin: 12px 0; }
    /* Add Employee modal input uppercase */
    #modal-emp-name, #modal-emp-plate1, #modal-emp-plate2 { text-transform: uppercase; }
</style>

<div class="container-fluid px-4">

    {{-- Hidden Forms --}}
    <form method="POST" action="{{ route('attendance.clockIn') }}" id="clockin-form" class="d-none">
        @csrf
        <input type="hidden" id="clock_in_time" name="clock_in_time">
        <input type="hidden" id="selected-employee-id" name="employee_id">
        <input type="hidden" id="selected-vehicle-plate" name="vehicle_plate">
    </form>
    <form method="POST" id="clockout-form" class="d-none">
        @csrf
        <input type="hidden" id="clock_out_time" name="clock_out_time">
        <input type="hidden" id="selected-attendance-id" value="">
    </form>
    <form method="POST" id="edit-checkin-form" class="d-none">
        @csrf
        @method('POST')
        <input type="hidden" id="edit_checkin_time" name="check_in_time">
    </form>
    <form method="POST" id="delete-attendance-form" class="d-none">
        @csrf
        @method('DELETE')
    </form>
    <form method="POST" action="{{ route('employees.store') }}" id="add-employee-form" class="d-none">
        @csrf
        <input type="hidden" id="new-emp-name" name="name">
        <input type="hidden" id="new-emp-plate1" name="plate_1">
        <input type="hidden" id="new-emp-plate2" name="plate_2">
    </form>
    <form method="POST" id="delete-employee-form" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    {{-- ===== 3-COLUMN LAYOUT ===== --}}
    <div class="row">

        {{-- LEFT: Employee List --}}
        <div class="col">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px;">
                <div class="card-header bg-white border-0 pt-4 pb-2">
                    <h5 class="mb-0"><b><i class="bi bi-people me-2"></i>Employee List</b></h5>
                </div>
                <div class="card-body pt-0">
                    <input type="text" class="form-control form-control-sm mb-3" id="employee-search" placeholder="Search employee...">
                    <div class="table-responsive" style="height: 450px; overflow-y: auto;">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th>Name</th>
                                    <th>Car Plate 1</th>
                                    <th>Car Plate 2</th>
                                </tr>
                            </thead>
                            <tbody id="employee-list">
                                @foreach($employees as $employee)
                                @php
                                    $isClockedIn = $liveAttendances->where('employee_id', $employee->id)->where('status', 'clocked_in')->isNotEmpty();
                                    $vehicles = $employee->vehicles->pluck('plate_number')->toArray();
                                @endphp
                                <tr class="{{ $isClockedIn ? '' : 'employee-row' }}"
                                    @if(!$isClockedIn)
                                        data-id="{{ $employee->id }}"
                                        data-name="{{ $employee->name }}"
                                        data-vehicles='@json($vehicles)'
                                        style="cursor: pointer;"
                                    @else
                                        style="opacity: 0.4; pointer-events: none;"
                                    @endif>
                                    <td>
                                        {{ $employee->name }}
                                        @if($isClockedIn)
                                            <span class="badge bg-success ms-1">In</span>
                                        @endif
                                    </td>
                                    <td>{{ $vehicles[0] ?? '-' }}</td>
                                    <td>{{ $vehicles[1] ?? '-' }}</td>
                                </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- CENTER: Icon Buttons --}}
        <div class="col-auto d-flex align-items-center justify-content-center">
            <div class="text-center px-2" style="position: sticky; top: 80px; width: 140px;">

                {{-- Selected Name --}}
                <div class="mb-3">
                    <span class="text-muted small d-block">Selected</span>
                    <strong id="selected-display" class="d-block text-truncate" style="font-size: 0.85rem;" title="None">None</strong>
                </div>

                {{-- Row 1: Clock In + Clock Out --}}
                <div class="d-flex justify-content-center gap-3 mb-2">
                    <div class="icon-btn-wrap">
                        <button type="button" class="icon-btn btn-clockin" id="clockin-btn" disabled title="Clock In">
                            <i class="bi bi-box-arrow-in-right"></i>
                        </button>
                        <span class="icon-btn-label">Clock In</span>
                    </div>
                    <div class="icon-btn-wrap">
                        <button type="button" class="icon-btn btn-clockout" id="clockout-btn" disabled title="Clock Out">
                            <i class="bi bi-box-arrow-left"></i>
                        </button>
                        <span class="icon-btn-label">Clock Out</span>
                    </div>
                </div>

                {{-- Vehicle dropdown (normal flow, no overlap) --}}
                <div id="vehicle-selection" class="mb-2" style="visibility:hidden;">
                    <select class="form-select form-select-sm" id="vehicle-dropdown"></select>
                </div>

                <div class="center-divider"></div>

                {{-- Action Buttons 2x2 --}}
                <div class="d-flex justify-content-center gap-2 mb-2">
                    <div class="icon-btn-wrap">
                        <button type="button" class="icon-btn btn-correct" id="correct-checkin-btn" disabled title="Correct Clock-In Time">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                        <span class="icon-btn-label">Correct</span>
                    </div>
                    <div class="icon-btn-wrap">
                        <button type="button" class="icon-btn btn-remove-att" id="remove-att-btn" disabled title="Remove Wrong Entry">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                        <span class="icon-btn-label">Remove</span>
                    </div>
                </div>
                <div class="d-flex justify-content-center gap-2">
                    <div class="icon-btn-wrap">
                        <button type="button" class="icon-btn btn-remove-emp" id="remove-emp-btn" disabled title="Remove Employee">
                            <i class="bi bi-person-dash-fill"></i>
                        </button>
                        <span class="icon-btn-label">Del Emp</span>
                    </div>
                    <div class="icon-btn-wrap">
                        <button type="button" class="icon-btn btn-add-emp active-btn" id="show-add-emp-btn" title="Add New Employee">
                            <i class="bi bi-person-plus-fill"></i>
                        </button>
                        <span class="icon-btn-label" style="color:#28a745;">Add Emp</span>
                    </div>
                </div>

            </div>
        </div>

        {{-- RIGHT: Live Attendance Status --}}
        <div class="col">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px;">
                <div class="card-header bg-white border-0 pt-4 pb-2">
                    <h5 class="mb-0"><b><i class="bi bi-clock-history me-2"></i>Live Attendance Status</b></h5>
                </div>
                <div class="card-body pt-0">
                    <input type="text" class="form-control form-control-sm mb-3" id="attendance-search" placeholder="Search employee...">
                    <div class="table-responsive" style="height: 450px; overflow-y: auto;">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th>Employee</th>
                                    <th>Vehicle</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="attendance-tbody">
                                @foreach($liveAttendances as $attendance)
                                <tr class="{{ $attendance->status == 'clocked_in' ? 'attendance-row' : '' }}"
                                    @if($attendance->status == 'clocked_in')
                                        data-id="{{ $attendance->id }}"
                                        data-name="{{ $attendance->employee->name }}"
                                        data-status="{{ $attendance->status }}"
                                        data-checkin="{{ \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') }}"
                                        data-checkin-date="{{ \Carbon\Carbon::parse($attendance->check_in_time)->format('Y-m-d') }}"
                                        style="cursor: pointer;"
                                    @else
                                        style="opacity: 0.4;"
                                    @endif>
                                    <td>{{ $attendance->employee->name }}</td>
                                    <td>
                                        @if($attendance->vehicle_plate)
                                            <span class="badge bg-info">{{ $attendance->vehicle_plate }}</span>
                                        @else -
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($attendance->check_in_time)->format('d M Y, h:i A') }}</td>
                                    <td>
                                        @if($attendance->check_out_time)
                                            @php
                                                $inDate  = \Carbon\Carbon::parse($attendance->check_in_time)->toDateString();
                                                $outDate = \Carbon\Carbon::parse($attendance->check_out_time)->toDateString();
                                            @endphp
                                            {{ \Carbon\Carbon::parse($attendance->check_out_time)->format('h:i A') }}
                                            @if($outDate !== $inDate)
                                                <br><small class="text-muted">({{ \Carbon\Carbon::parse($attendance->check_out_time)->format('d M Y') }})</small>
                                            @endif
                                        @else -
                                        @endif
                                    </td>
                                    <td>
                                        @if($attendance->status == 'clocked_in')
                                            <span class="badge bg-success">Clocked In</span>
                                        @else
                                            <span class="badge bg-secondary">Clocked Out</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                @if($liveAttendances->isEmpty())
                                <tr><td colspan="5" class="text-center text-muted py-3">No attendance records yet.</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Clock In Modal --}}
<div class="modal fade" id="clockInModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-box-arrow-in-right me-2"></i>Confirm Clock In</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-1">Clocking in:</p>
                <h5 class="mb-3" id="modal-clockin-name"></h5>
                <input type="text" class="form-control mx-auto" id="modal-clockin-time"
                    style="max-width:200px; font-size:1.5rem; text-align:center;" placeholder="HH:MM" maxlength="5" required>
                <small class="text-muted mt-2 d-block">24-hour format (e.g. 08:30, 14:00). Defaults to current time.</small>
                <div id="clockin-deviation-warning" class="alert alert-warning py-2 px-3 mt-2 mb-0 mx-auto" style="max-width:280px; font-size:0.85rem;">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i><span id="clockin-deviation-text"></span>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i>Cancel</button>
                <button type="button" class="btn btn-success px-4" id="confirm-clockin-btn"><i class="bi bi-check-lg me-1"></i>Confirm Clock In</button>
            </div>
        </div>
    </div>
</div>

{{-- Clock Out Modal --}}
<div class="modal fade" id="clockOutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-box-arrow-left me-2"></i>Confirm Clock Out</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-1">Clocking out:</p>
                <h5 class="mb-3" id="modal-clockout-name"></h5>
                <input type="text" class="form-control mx-auto" id="modal-clockout-time"
                    style="max-width:200px; font-size:1.5rem; text-align:center;" placeholder="HH:MM" maxlength="5" required>
                <small class="text-muted mt-2 d-block">24-hour format (e.g. 08:30, 14:00). Defaults to current time.</small>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i>Cancel</button>
                <button type="button" class="btn btn-danger px-4" id="confirm-clockout-btn"><i class="bi bi-check-lg me-1"></i>Confirm Clock Out</button>
            </div>
        </div>
    </div>
</div>

{{-- Correct Clock-In Modal --}}
<div class="modal fade" id="editCheckinModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-fill me-2"></i>Correct Clock-In Time</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-1">Correcting clock-in for:</p>
                <h5 class="mb-3" id="modal-edit-name"></h5>
                <input type="text" class="form-control mx-auto" id="modal-edit-time"
                    style="max-width:200px; font-size:1.5rem; text-align:center;" placeholder="HH:MM" maxlength="5" required>
                <small class="text-muted mt-2 d-block">Enter the correct time (24-hour format).</small>
                <div class="alert alert-info py-2 px-3 mt-3 mb-0 mx-auto" style="max-width:280px; font-size:0.82rem;">
                    <i class="bi bi-info-circle me-1"></i>The date will remain <strong id="modal-edit-date-display"></strong>.
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i>Cancel</button>
                <button type="button" class="btn btn-primary px-4" id="confirm-edit-checkin-btn"><i class="bi bi-check-lg me-1"></i>Save Correction</button>
            </div>
        </div>
    </div>
</div>

{{-- Remove Attendance Modal --}}
<div class="modal fade" id="deleteAttendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:#fd7e14;" >
                <h5 class="modal-title text-white"><i class="bi bi-trash-fill me-2"></i>Remove Clock-In Entry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-exclamation-triangle-fill" style="font-size:2.5rem; color:#fd7e14;"></i>
                <p class="mt-3 mb-1">Remove the clock-in record for:</p>
                <h5 class="mb-2" id="modal-delete-att-name"></h5>
                <p class="text-muted small mb-0">This cannot be undone. The employee can clock in again.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i>Cancel</button>
                <button type="button" class="btn px-4 text-white" style="background:#fd7e14;" id="confirm-delete-att-btn"><i class="bi bi-trash-fill me-1"></i>Yes, Remove</button>
            </div>
        </div>
    </div>
</div>

{{-- Remove Employee Modal --}}
<div class="modal fade" id="deleteEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-person-dash-fill me-2"></i>Remove Employee</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:2.5rem;"></i>
                <p class="mt-3 mb-1">Permanently delete employee:</p>
                <h5 class="mb-3" id="modal-delete-emp-name"></h5>
                <p class="text-muted small mb-0">All their attendance records will also be deleted.<br>This <strong>cannot</strong> be undone.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i>Cancel</button>
                <button type="button" class="btn btn-danger px-4" id="confirm-delete-emp-btn"><i class="bi bi-trash-fill me-1"></i>Yes, Delete</button>
            </div>
        </div>
    </div>
</div>

{{-- Add Employee Modal --}}
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background: #28a745;">
                <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Add New Employee</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4 px-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="modal-emp-name"
                        placeholder="e.g. JOHN DOE" autocomplete="off">
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Car Plate 1 <span class="text-muted fw-normal small">(optional)</span></label>
                        <input type="text" class="form-control" id="modal-emp-plate1"
                            placeholder="e.g. WXY1234" autocomplete="off">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Car Plate 2 <span class="text-muted fw-normal small">(optional)</span></label>
                        <input type="text" class="form-control" id="modal-emp-plate2"
                            placeholder="e.g. VBG9876" autocomplete="off">
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i>Cancel</button>
                <button type="button" class="btn btn-success px-4" id="save-emp-btn"><i class="bi bi-person-check-fill me-1"></i>Save Employee</button>
            </div>
        </div>
    </div>
</div>

<script>
    // ===== HELPERS =====
    function showErrorToast(message) {
        const existing = document.getElementById('vms-toast-client');
        if (existing) existing.remove();
        const toast = document.createElement('div');
        toast.id = 'vms-toast-client';
        toast.className = 'vms-toast toast-error';
        toast.innerHTML = `<div class="vms-toast-body"><div class="vms-toast-icon"><i class="bi bi-exclamation-triangle-fill"></i></div><div class="vms-toast-text">${message}</div><button class="vms-toast-close" onclick="this.closest('.vms-toast').classList.add('hide');setTimeout(()=>this.closest('.vms-toast').remove(),350)"><i class="bi bi-x-lg"></i></button></div><div class="vms-toast-progress"><div class="vms-toast-progress-bar"></div></div>`;
        document.body.appendChild(toast);
        setTimeout(() => { if (toast.parentNode) { toast.classList.add('hide'); setTimeout(() => toast.remove(), 350); } }, 4000);
    }
    function formatTimeInput(input) {
        input.addEventListener('input', function() {
            let val = this.value.replace(/[^0-9]/g, '');
            if (val.length >= 3) val = val.substring(0, 2) + ':' + val.substring(2, 4);
            this.value = val.substring(0, 5);
        });
        input.addEventListener('keydown', function(e) {
            if ([8,46,9,37,39].includes(e.keyCode)) return;
            if (e.key.length === 1 && !/[0-9]/.test(e.key)) e.preventDefault();
        });
    }
    function isValidTime(val) { return /^([01]\d|2[0-3]):[0-5]\d$/.test(val); }
    formatTimeInput(document.getElementById('modal-clockin-time'));
    formatTimeInput(document.getElementById('modal-clockout-time'));
    formatTimeInput(document.getElementById('modal-edit-time'));

    // ===== ICON BUTTON ACTIVATION =====
    function setIconBtn(id, active) {
        const btn = document.getElementById(id);
        btn.disabled = !active;
        if (active) btn.classList.add('active-btn');
        else        btn.classList.remove('active-btn');
    }

    // ===== CONTEXT STATE =====
    let selectedAttendanceId = null, selectedAttendanceDate = null;
    let selectedAttendanceName = null, selectedAttendanceClockin = null;
    let selectedAttendanceStatus = null;
    let selectedEmployeeId = null, selectedEmployeeName = null;

    function setEmployeeMode(id, name) {
        selectedEmployeeId = id; selectedEmployeeName = name;
        selectedAttendanceId = null;
        setIconBtn('clockin-btn',        true);
        setIconBtn('clockout-btn',       false);
        setIconBtn('correct-checkin-btn',false);
        setIconBtn('remove-att-btn',     false);
        setIconBtn('remove-emp-btn',     true);
    }
    function setAttendanceMode(id, name, status, checkin, checkinDate) {
        selectedAttendanceId = id; selectedAttendanceName = name;
        selectedAttendanceStatus = status; selectedAttendanceClockin = checkin;
        selectedAttendanceDate = checkinDate; selectedEmployeeId = null;
        setIconBtn('clockin-btn',        false);
        setIconBtn('clockout-btn',       status === 'clocked_in');
        setIconBtn('correct-checkin-btn',true);
        setIconBtn('remove-att-btn',     status === 'clocked_in');
        setIconBtn('remove-emp-btn',     false);
    }

    // ===== SEARCH =====
    document.getElementById('employee-search').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#employee-list tr').forEach(r => {
            if (r.id === 'add-emp-inline-row') return;
            r.style.display = (r.querySelector('td')?.textContent.toLowerCase().includes(q)) ? '' : 'none';
        });
    });
    document.getElementById('attendance-search').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#attendance-tbody tr').forEach(r => {
            r.style.display = (r.querySelector('td')?.textContent.toLowerCase().includes(q)) ? '' : 'none';
        });
    });

    // ===== CLICK EMPLOYEE ROW =====
    document.querySelectorAll('.employee-row').forEach(row => {
        row.addEventListener('click', function() {
            document.querySelectorAll('.employee-row').forEach(r => r.classList.remove('selected-employee'));
            document.querySelectorAll('.attendance-row').forEach(r => r.classList.remove('selected-attendance'));
            this.classList.add('selected-employee');
            const id = this.dataset.id, name = this.dataset.name;
            const vehicles = JSON.parse(this.dataset.vehicles);
            document.getElementById('selected-employee-id').value = id;
            document.getElementById('selected-display').textContent = name;
            document.getElementById('selected-display').title = name;
            document.getElementById('selected-attendance-id').value = '';
            const vehicleSection  = document.getElementById('vehicle-selection');
            const vehicleDropdown = document.getElementById('vehicle-dropdown');
            vehicleDropdown.innerHTML = '';
            if (vehicles.length > 0) {
                vehicles.forEach(plate => {
                    const opt = document.createElement('option');
                    opt.value = plate; opt.textContent = plate;
                    vehicleDropdown.appendChild(opt);
                });
                const nv = document.createElement('option');
                nv.value = ''; nv.textContent = 'No Vehicle';
                vehicleDropdown.appendChild(nv);
                vehicleSection.style.visibility = 'visible';
                document.getElementById('selected-vehicle-plate').value = vehicles[0];
            } else {
                vehicleSection.style.visibility = 'hidden';
                document.getElementById('selected-vehicle-plate').value = '';
            }
            setEmployeeMode(id, name);
        });
    });

    document.getElementById('vehicle-dropdown').addEventListener('change', function() {
        document.getElementById('selected-vehicle-plate').value = this.value;
    });

    // ===== CLICK ATTENDANCE ROW =====
    document.querySelectorAll('.attendance-row').forEach(row => {
        row.addEventListener('click', function() {
            document.querySelectorAll('.employee-row').forEach(r => r.classList.remove('selected-employee'));
            document.querySelectorAll('.attendance-row').forEach(r => r.classList.remove('selected-attendance'));
            this.classList.add('selected-attendance');
            const id = this.dataset.id, name = this.dataset.name;
            const status = this.dataset.status, checkin = this.dataset.checkin;
            const checkinDate = this.dataset.checkinDate;
            document.getElementById('selected-attendance-id').value = id;
            document.getElementById('selected-display').textContent = name;
            document.getElementById('selected-display').title = name;
            document.getElementById('selected-employee-id').value = '';
            document.getElementById('vehicle-selection').style.visibility = 'hidden';
            setAttendanceMode(id, name, status, checkin, checkinDate);
        });
    });

    // ===== CLOCK IN =====
    document.getElementById('clockin-btn').addEventListener('click', function() {
        if (this.disabled) return;
        new bootstrap.Modal(document.getElementById('clockInModal')).show();
    });
    const clockinTimeInput = document.getElementById('modal-clockin-time');
    const deviationWarning = document.getElementById('clockin-deviation-warning');
    const deviationText    = document.getElementById('clockin-deviation-text');
    document.getElementById('clockInModal').addEventListener('show.bs.modal', function() {
        document.getElementById('modal-clockin-name').textContent = document.getElementById('selected-display').textContent;
        const now = new Date();
        clockinTimeInput.value = String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0');
        deviationWarning.style.display = 'none';
    });
    clockinTimeInput.addEventListener('input', function() {
        const val = this.value;
        if (!isValidTime(val)) { deviationWarning.style.display = 'none'; return; }
        const [h,m] = val.split(':').map(Number);
        const now = new Date();
        const diff = Math.abs(now.getHours()*60+now.getMinutes() - (h*60+m));
        if (diff > 30) { deviationText.textContent = `Differs from now by ${diff} min — double-check.`; deviationWarning.style.display = 'block'; }
        else deviationWarning.style.display = 'none';
    });
    document.getElementById('confirm-clockin-btn').addEventListener('click', function() {
        const timeVal = clockinTimeInput.value;
        if (!isValidTime(timeVal)) { showErrorToast('Please enter a valid time in HH:MM format (e.g. 08:30, 14:00)'); return; }
        const today = new Date();
        const date = today.getFullYear()+'-'+String(today.getMonth()+1).padStart(2,'0')+'-'+String(today.getDate()).padStart(2,'0');
        document.getElementById('clock_in_time').value = date+'T'+timeVal;
        document.getElementById('clockin-form').submit();
    });

    // ===== CLOCK OUT =====
    document.getElementById('clockout-btn').addEventListener('click', function() {
        if (this.disabled) return;
        new bootstrap.Modal(document.getElementById('clockOutModal')).show();
    });
    document.getElementById('clockOutModal').addEventListener('show.bs.modal', function() {
        document.getElementById('modal-clockout-name').textContent = document.getElementById('selected-display').textContent;
        const now = new Date();
        document.getElementById('modal-clockout-time').value = String(now.getHours()).padStart(2,'0')+':'+String(now.getMinutes()).padStart(2,'0');
    });
    document.getElementById('confirm-clockout-btn').addEventListener('click', function() {
        const timeVal = document.getElementById('modal-clockout-time').value;
        if (!isValidTime(timeVal)) { showErrorToast('Please enter a valid time in HH:MM format (e.g. 08:30, 14:00)'); return; }
        const today = new Date();
        const date = today.getFullYear()+'-'+String(today.getMonth()+1).padStart(2,'0')+'-'+String(today.getDate()).padStart(2,'0');
        const form = document.getElementById('clockout-form');
        form.action = '/attendance/'+document.getElementById('selected-attendance-id').value+'/clock-out';
        document.getElementById('clock_out_time').value = date+'T'+timeVal;
        form.submit();
    });

    // ===== CORRECT CLOCK-IN =====
    document.getElementById('correct-checkin-btn').addEventListener('click', function() {
        if (this.disabled) return;
        document.getElementById('modal-edit-name').textContent = selectedAttendanceName;
        document.getElementById('modal-edit-time').value = selectedAttendanceClockin;
        const d = new Date(selectedAttendanceDate+'T00:00:00');
        document.getElementById('modal-edit-date-display').textContent =
            d.toLocaleDateString('en-GB', {day:'2-digit', month:'short', year:'numeric'});
        new bootstrap.Modal(document.getElementById('editCheckinModal')).show();
    });
    document.getElementById('confirm-edit-checkin-btn').addEventListener('click', function() {
        const timeVal = document.getElementById('modal-edit-time').value;
        if (!isValidTime(timeVal)) { showErrorToast('Please enter a valid time in HH:MM format (e.g. 08:30, 14:00)'); return; }
        const form = document.getElementById('edit-checkin-form');
        form.action = '/attendance/'+selectedAttendanceId+'/update-checkin';
        document.getElementById('edit_checkin_time').value = selectedAttendanceDate+'T'+timeVal;
        form.submit();
    });

    // ===== REMOVE ENTRY =====
    document.getElementById('remove-att-btn').addEventListener('click', function() {
        if (this.disabled) return;
        document.getElementById('modal-delete-att-name').textContent = selectedAttendanceName;
        new bootstrap.Modal(document.getElementById('deleteAttendanceModal')).show();
    });
    document.getElementById('confirm-delete-att-btn').addEventListener('click', function() {
        const form = document.getElementById('delete-attendance-form');
        form.action = '/attendance/'+selectedAttendanceId;
        form.submit();
    });

    // ===== REMOVE EMPLOYEE =====
    document.getElementById('remove-emp-btn').addEventListener('click', function() {
        if (this.disabled) return;
        document.getElementById('modal-delete-emp-name').textContent = selectedEmployeeName;
        new bootstrap.Modal(document.getElementById('deleteEmployeeModal')).show();
    });
    document.getElementById('confirm-delete-emp-btn').addEventListener('click', function() {
        const form = document.getElementById('delete-employee-form');
        form.action = '/employees/'+selectedEmployeeId;
        form.submit();
    });

    // ===== ADD EMPLOYEE MODAL =====

    // Auto-uppercase on input
    ['modal-emp-name','modal-emp-plate1','modal-emp-plate2'].forEach(id => {
        document.getElementById(id).addEventListener('input', function() {
            const pos = this.selectionStart;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(pos, pos);
        });
    });

    // Clear fields when modal closes
    document.getElementById('addEmployeeModal').addEventListener('hidden.bs.modal', function() {
        ['modal-emp-name','modal-emp-plate1','modal-emp-plate2'].forEach(id => document.getElementById(id).value = '');
    });

    // Open modal when Add Emp button is clicked (lazy init — same pattern as other modals)
    document.getElementById('show-add-emp-btn').addEventListener('click', function() {
        new bootstrap.Modal(document.getElementById('addEmployeeModal')).show();
        document.getElementById('addEmployeeModal').addEventListener('shown.bs.modal', function focusName() {
            document.getElementById('modal-emp-name').focus();
            document.getElementById('addEmployeeModal').removeEventListener('shown.bs.modal', focusName);
        });
    });

    // Save employee
    document.getElementById('save-emp-btn').addEventListener('click', function() {
        const name = document.getElementById('modal-emp-name').value.trim();
        if (!name) { showErrorToast('Please enter the employee name.'); return; }
        document.getElementById('new-emp-name').value   = name;
        document.getElementById('new-emp-plate1').value = document.getElementById('modal-emp-plate1').value.trim();
        document.getElementById('new-emp-plate2').value = document.getElementById('modal-emp-plate2').value.trim();
        document.getElementById('add-employee-form').submit();
    });

    // Allow Enter key in name field to submit
    document.getElementById('modal-emp-name').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') document.getElementById('save-emp-btn').click();
    });

</script>
@endsection
