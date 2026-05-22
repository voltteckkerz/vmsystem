@extends('layouts.app')

@section('content')
<style>
    /* High contrast selection styles */
    tr.selected-employee > td {
        background-color: #0d6efd !important; /* Bootstrap primary blue */
        color: white !important;
        font-weight: 600;
    }
    tr.selected-attendance > td {
        background-color: #dc3545 !important; /* Bootstrap danger red */
        color: white !important;
        font-weight: 600;
    }
    /* Subtle hover adjustment for selected rows */
    .table-hover tbody tr.selected-employee:hover > td {
        background-color: #0b5ed7 !important;
    }
    .table-hover tbody tr.selected-attendance:hover > td {
        background-color: #c82333 !important;
    }
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

        {{-- CENTER: Buttons --}}
        <div class="col-auto d-flex align-items-center justify-content-center">
            <div class="text-center px-2" style="position: sticky; top: 80px; width: 140px;">
                <div class="mb-2">
                    <span class="text-muted small d-block">Selected</span>
                    <strong id="selected-display" class="d-block text-truncate" style="min-width: 90px; max-width: 100%; font-size: 0.85rem;" title="None">None</strong>
                </div>


                <div style="position: relative;">
                <button type="button" class="btn btn-sm btn-success w-100 mb-2" id="clockin-btn" disabled>
                    <i class="bi bi-box-arrow-in-right me-1"></i>Clock In
                </button>
                <button type="button" class="btn btn-sm btn-danger w-100" id="clockout-btn" disabled>
                    <i class="bi bi-box-arrow-left me-1"></i>Clock Out
                </button>

                <div id="vehicle-selection" class="d-none" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 10; margin-top: 6px;">
                    <select class="form-select form-select-sm" id="vehicle-dropdown"></select>
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
                                        data-checkin="{{ \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') }}"
                                        style="cursor: pointer;"
                                    @else
                                        style="opacity: 0.4;"
                                    @endif>
                                    <td>{{ $attendance->employee->name }}</td>
                                    <td>
                                        @if($attendance->vehicle_plate)
                                            <span class="badge bg-info">{{ $attendance->vehicle_plate }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($attendance->check_in_time)->format('d M Y, h:i A') }}</td>
                                    <td>
                                        @if($attendance->check_out_time)
                                            @php
                                                $inDate = \Carbon\Carbon::parse($attendance->check_in_time)->toDateString();
                                                $outDate = \Carbon\Carbon::parse($attendance->check_out_time)->toDateString();
                                            @endphp
                                            {{ \Carbon\Carbon::parse($attendance->check_out_time)->format('h:i A') }}
                                            @if($outDate !== $inDate)
                                                <br><small class="text-muted">({{ \Carbon\Carbon::parse($attendance->check_out_time)->format('d M Y') }})</small>
                                            @endif
                                        @else
                                            -
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
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No attendance records yet.</td>
                                </tr>
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
                <input type="text" class="form-control mx-auto" id="modal-clockin-time" style="max-width: 200px; font-size: 1.5rem; text-align: center;" placeholder="HH:MM" maxlength="5" pattern="([01]\d|2[0-3]):[0-5]\d" required>
                <small class="text-muted mt-2 d-block">24-hour format (e.g. 08:30, 14:00). Defaults to current time.</small>
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
                <input type="text" class="form-control mx-auto" id="modal-clockout-time" style="max-width: 200px; font-size: 1.5rem; text-align: center;" placeholder="HH:MM" maxlength="5" pattern="([01]\d|2[0-3]):[0-5]\d" required>
                <small class="text-muted mt-2 d-block">24-hour format (e.g. 08:30, 14:00). Defaults to current time.</small>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i>Cancel</button>
                <button type="button" class="btn btn-danger px-4" id="confirm-clockout-btn"><i class="bi bi-check-lg me-1"></i>Confirm Clock Out</button>
            </div>
        </div>
    </div>
</div>

<script>
    // ===== ERROR TOAST (same style as the global red popup) =====
    function showErrorToast(message) {
        const existing = document.getElementById('vms-toast-client');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.id = 'vms-toast-client';
        toast.className = 'vms-toast toast-error';
        toast.innerHTML = `
            <div class="vms-toast-body">
                <div class="vms-toast-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div class="vms-toast-text">${message}</div>
                <button class="vms-toast-close" onclick="this.closest('.vms-toast').classList.add('hide'); setTimeout(() => this.closest('.vms-toast').remove(), 350)"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="vms-toast-progress"><div class="vms-toast-progress-bar"></div></div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => {
            if (toast.parentNode) {
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 350);
            }
        }, 4000);
    }

    // ===== HELPER: Auto-format time input to HH:MM (24-hour) =====
    function formatTimeInput(input) {
        input.addEventListener('input', function() {
            let val = this.value.replace(/[^0-9]/g, '');
            if (val.length >= 3) {
                val = val.substring(0, 2) + ':' + val.substring(2, 4);
            }
            this.value = val.substring(0, 5);
        });
        input.addEventListener('keydown', function(e) {
            // Allow backspace, delete, tab, arrows
            if ([8, 46, 9, 37, 39].includes(e.keyCode)) return;
            // Block non-numeric keys
            if (e.key.length === 1 && !/[0-9]/.test(e.key)) e.preventDefault();
        });
    }

    function isValidTime(val) {
        return /^([01]\d|2[0-3]):[0-5]\d$/.test(val);
    }

    // Apply auto-format to all time inputs
    formatTimeInput(document.getElementById('modal-clockin-time'));
    formatTimeInput(document.getElementById('modal-clockout-time'));

    // ===== SEARCH EMPLOYEE =====
    document.getElementById('employee-search').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll('#employee-list tr').forEach(row => {
            const name = row.querySelector('td')?.textContent.toLowerCase() || '';
            row.style.display = name.includes(query) ? '' : 'none';
        });
    });

    // ===== SEARCH LIVE ATTENDANCE =====
    document.getElementById('attendance-search').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll('#attendance-tbody tr').forEach(row => {
            const name = row.querySelector('td')?.textContent.toLowerCase() || '';
            row.style.display = name.includes(query) ? '' : 'none';
        });
    });

    // ===== CLICK EMPLOYEE ROW (for Clock In) =====
    document.querySelectorAll('.employee-row').forEach(row => {
        row.addEventListener('click', function() {
            // Clear all highlights
            document.querySelectorAll('.employee-row').forEach(r => r.classList.remove('selected-employee'));
            document.querySelectorAll('.attendance-row').forEach(r => r.classList.remove('selected-attendance'));

            // Highlight this row
            this.classList.add('selected-employee');

            // Set employee data
            const employeeId = this.dataset.id;
            const employeeName = this.dataset.name;
            const vehicles = JSON.parse(this.dataset.vehicles);

            document.getElementById('selected-employee-id').value = employeeId;
            document.getElementById('selected-display').textContent = employeeName;
            document.getElementById('selected-display').title = employeeName;

            // Show vehicle dropdown if employee has vehicles
            const vehicleSection = document.getElementById('vehicle-selection');
            const vehicleDropdown = document.getElementById('vehicle-dropdown');
            vehicleDropdown.innerHTML = '';

            if (vehicles.length > 0) {
                vehicles.forEach(plate => {
                    const opt = document.createElement('option');
                    opt.value = plate;
                    opt.textContent = plate;
                    vehicleDropdown.appendChild(opt);
                });
                const noVehicle = document.createElement('option');
                noVehicle.value = '';
                noVehicle.textContent = 'No Vehicle';
                vehicleDropdown.appendChild(noVehicle);

                vehicleSection.classList.remove('d-none');
                document.getElementById('selected-vehicle-plate').value = vehicles[0];
            } else {
                vehicleSection.classList.add('d-none');
                document.getElementById('selected-vehicle-plate').value = '';
            }

            // Enable Clock In, disable Clock Out
            document.getElementById('clockin-btn').disabled = false;
            document.getElementById('clockout-btn').disabled = true;
            document.getElementById('selected-attendance-id').value = '';
        });
    });

    // Update vehicle hidden input when dropdown changes
    document.getElementById('vehicle-dropdown').addEventListener('change', function() {
        document.getElementById('selected-vehicle-plate').value = this.value;
    });

    // Clock In button opens modal
    document.getElementById('clockin-btn').addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('clockInModal'));
        modal.show();
    });

    // Clock Out button opens modal
    document.getElementById('clockout-btn').addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('clockOutModal'));
        modal.show();
    });

    // ===== CLICK ATTENDANCE ROW (for Clock Out) =====
    document.querySelectorAll('.attendance-row').forEach(row => {
        row.addEventListener('click', function() {
            // Clear all highlights
            document.querySelectorAll('.employee-row').forEach(r => r.classList.remove('selected-employee'));
            document.querySelectorAll('.attendance-row').forEach(r => r.classList.remove('selected-attendance'));

            // Highlight this row
            this.classList.add('selected-attendance');

            // Set attendance data
            document.getElementById('selected-attendance-id').value = this.dataset.id;
            document.getElementById('selected-display').textContent = this.dataset.name;
            document.getElementById('selected-display').title = this.dataset.name;
            document.getElementById('clockOutModal').dataset.checkin = this.dataset.checkin;

            // Enable Clock Out, disable Clock In
            document.getElementById('clockout-btn').disabled = false;
            document.getElementById('clockin-btn').disabled = true;
            document.getElementById('selected-employee-id').value = '';
            document.getElementById('vehicle-selection').classList.add('d-none');
        });
    });

    // ===== CLOCK IN MODAL =====
    document.getElementById('clockInModal').addEventListener('show.bs.modal', function() {
        document.getElementById('modal-clockin-name').textContent = document.getElementById('selected-display').textContent;
        const now = new Date();
        document.getElementById('modal-clockin-time').value = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
    });

    document.getElementById('confirm-clockin-btn').addEventListener('click', function() {
        const timeVal = document.getElementById('modal-clockin-time').value;
        if (!isValidTime(timeVal)) {
            showErrorToast('Please enter a valid time in HH:MM format (e.g. 08:30, 14:00)');
            return;
        }
        const today = new Date();
        const date = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
        document.getElementById('clock_in_time').value = date + 'T' + timeVal;
        document.getElementById('clockin-form').submit();
    });

    // ===== CLOCK OUT MODAL =====
    document.getElementById('clockOutModal').addEventListener('show.bs.modal', function() {
        document.getElementById('modal-clockout-name').textContent = document.getElementById('selected-display').textContent;
        const now = new Date();
        document.getElementById('modal-clockout-time').value = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
    });

    document.getElementById('confirm-clockout-btn').addEventListener('click', function() {
        const timeVal = document.getElementById('modal-clockout-time').value;
        if (!isValidTime(timeVal)) {
            showErrorToast('Please enter a valid time in HH:MM format (e.g. 08:30, 14:00)');
            return;
        }
        const attendanceId = document.getElementById('selected-attendance-id').value;

        // Always use today's date from system clock.
        // Overnight shifts work naturally: if staff clocked in at 17:30 on 21 May,
        // the guard clocks them out at 01:40 when it's already 22 May.
        // Typos are caught: if staff clocked in at 08:30 on 21 May,
        // guard types 07:30 on the same day → server rejects it.
        const today = new Date();
        const date = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
        const clockOutTime = date + 'T' + timeVal;

        const form = document.getElementById('clockout-form');
        form.action = '/attendance/' + attendanceId + '/clock-out';
        document.getElementById('clock_out_time').value = clockOutTime;
        form.submit();
    });

</script>
@endsection
