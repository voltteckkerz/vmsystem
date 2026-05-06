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

    {{-- ===== SECTION 1: Employee List with Clock In / Clock Out ===== --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px;">
        <div class="card-header bg-white border-0 pt-4 pb-2">
            <h4 class="mb-0"><b>Employee List</b></h4>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-striped table-bordered mb-0">
                    <thead class="table-dark" style="position: sticky; top: 0; z-index: 1;">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Car Plate 1</th>
                            <th>Car Plate 2</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $index => $employee)
                    @php
                        // Check if this specific employee is currently clocked in
                        $isClockedIn = $liveAttendances->where('employee_id', $employee->id)->where('status', 'clocked_in')->isNotEmpty();
                    @endphp
                    
                    {{-- If clocked in, remove 'employee-row' class and make it unclickable --}}
                    <tr class="{{ $isClockedIn ? '' : 'employee-row' }}" 
                        @if(!$isClockedIn)
                            data-id="{{ $employee->id }}" 
                            data-name="{{ $employee->name }}" 
                            data-vehicles='@json($employee->vehicles->pluck("plate_number"))' 
                            style="cursor: pointer;"
                        @else
                            {{-- Grey out and disable clicks --}}
                            style="opacity: 0.5; pointer-events: none;"
                        @endif>
                        
                        <td>{{ $index + 1 }}</td>
                        <td>
                            {{ $employee->name }}
                            {{-- Optional: Add a small badge next to their name --}}
                            @if($isClockedIn)
                                <span class="badge bg-secondary ms-1">Clocked In</span>
                            @endif
                        </td>
                        <td>{{ $employee->vehicles[0]->plate_number ?? '-' }}</td>
                        <td>{{ $employee->vehicles[1]->plate_number ?? '-' }}</td>
                    </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Selected Display + Vehicle Selection + Buttons --}}
            <div class="mt-3 p-3 bg-light rounded">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted">Selected:</span>
                        <strong id="selected-display" class="ms-2">None — click an employee or attendance row</strong>
                    </div>
                    <div>
                        {{-- Clock In Form --}}
                        <form method="POST" action="{{ route('attendance.clockIn') }}" id="clockin-form" class="d-inline">
                            @csrf
                            <input type="hidden" id="clock_in_time" name="clock_in_time">
                            <input type="hidden" id="selected-employee-id" name="employee_id">
                            <input type="hidden" id="selected-vehicle-plate" name="vehicle_plate">
                            <button type="button" class="btn btn-success px-4 me-2" id="clockin-btn" data-bs-toggle="modal" data-bs-target="#clockInModal" disabled>Clock In</button>
                        </form>

                        {{-- Clock Out Form --}}
                        <form method="POST" id="clockout-form" class="d-inline">
                            @csrf
                            <input type="hidden" id="clock_out_time" name="clock_out_time">
                            <input type="hidden" id="selected-attendance-id" value="">
                            <button type="button" class="btn btn-danger px-4" id="clockout-btn" data-bs-toggle="modal" data-bs-target="#clockOutModal" disabled>Clock Out</button>
                        </form>
                    </div>
                </div>

                {{-- Vehicle Radio Buttons (hidden by default, shown when employee has vehicles) --}}
                <div id="vehicle-selection" class="mt-3 d-none">
                    <label class="form-label text-muted"><b>Select Vehicle:</b></label>
                    <div id="vehicle-radios"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== SECTION 2: Live Attendance Status ===== --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px;">
        <div class="card-header bg-white border-0 pt-4 pb-2">
            <h4 class="mb-0"><b>Live Attendance Status</b></h4>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
            <table class="table table-striped table-bordered mb-0">
                <thead class="table-dark" style="position: sticky; top: 0; z-index: 1;">
                    <tr>
                        <th>Employee Name</th>
                        <th>Vehicle Plate</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($liveAttendances as $attendance)
                    <tr class="{{ $attendance->status == 'clocked_in' ? 'attendance-row' : '' }}" 
                        @if($attendance->status == 'clocked_in') 
                            data-id="{{ $attendance->id }}" 
                            data-name="{{ $attendance->employee->name }}" 
                            style="cursor: pointer;" 
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
                                {{ \Carbon\Carbon::parse($attendance->check_out_time)->format('h:i A') }}
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
                        <td colspan="5" class="text-center">No attendance records yet.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Clock In Modal --}}
<div class="modal fade" id="clockInModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Confirm Clock In</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-1">Clocking in:</p>
                <h5 class="mb-3" id="modal-clockin-name"></h5>
                <input type="time" class="form-control mx-auto" id="modal-clockin-time" style="max-width: 200px; font-size: 1.5rem; text-align: center;">
                <small class="text-muted mt-2 d-block">Defaults to current time. Click to change.</small>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success px-4" id="confirm-clockin-btn">Confirm Clock In</button>
            </div>
        </div>
    </div>
</div>

{{-- Clock Out Modal (single, reusable) --}}
<div class="modal fade" id="clockOutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Clock Out</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-1">Clocking out:</p>
                <h5 class="mb-3" id="modal-clockout-name"></h5>
                <input type="time" class="form-control mx-auto" id="modal-clockout-time" style="max-width: 200px; font-size: 1.5rem; text-align: center;">
                <small class="text-muted mt-2 d-block">Defaults to current time. Click to change.</small>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger px-4" id="confirm-clockout-btn">Confirm Clock Out</button>
            </div>
        </div>
    </div>
</div>

<script>
    // ===== CLICK EMPLOYEE ROW (for Clock In) =====
    document.querySelectorAll('.employee-row').forEach(row => {
        row.addEventListener('click', function() {
            // Clear all highlights
            document.querySelectorAll('.employee-row').forEach(r => r.classList.remove('table-primary'));
            document.querySelectorAll('.attendance-row').forEach(r => r.classList.remove('table-danger'));
            
            // Highlight this row
            this.classList.add('table-primary');
            
            // Set employee data
            document.getElementById('selected-employee-id').value = this.dataset.id;
            document.getElementById('selected-display').textContent = this.dataset.name;
            
            // Enable Clock In, disable Clock Out
            document.getElementById('clockin-btn').disabled = false;
            document.getElementById('clockout-btn').disabled = true;
            document.getElementById('selected-attendance-id').value = '';

            // Show vehicle radio buttons if employee has vehicles
            const vehicles = JSON.parse(this.dataset.vehicles);
            const vehicleSelection = document.getElementById('vehicle-selection');
            const vehicleRadios = document.getElementById('vehicle-radios');
            vehicleRadios.innerHTML = '';

            if (vehicles.length > 0) {
                vehicles.forEach((plate, index) => {
                    const div = document.createElement('div');
                    div.className = 'form-check form-check-inline';
                    div.innerHTML = '<input class="form-check-input vehicle-radio" type="radio" name="vehicle_radio" id="vehicle' + index + '" value="' + plate + '"' + (index === 0 ? ' checked' : '') + '>' +
                                    '<label class="form-check-label" for="vehicle' + index + '">' + plate + '</label>';
                    vehicleRadios.appendChild(div);
                });

                // Add "No Vehicle" option
                const noVehicle = document.createElement('div');
                noVehicle.className = 'form-check form-check-inline';
                noVehicle.innerHTML = '<input class="form-check-input vehicle-radio" type="radio" name="vehicle_radio" id="vehicleNone" value="">' +
                                     '<label class="form-check-label" for="vehicleNone">No Vehicle</label>';
                vehicleRadios.appendChild(noVehicle);

                vehicleSelection.classList.remove('d-none');
                document.getElementById('selected-vehicle-plate').value = vehicles[0];
            } else {
                vehicleSelection.classList.add('d-none');
                document.getElementById('selected-vehicle-plate').value = '';
            }
        });
    });

    // Update hidden input when radio button changes
    document.getElementById('vehicle-radios').addEventListener('change', function(e) {
        if (e.target.classList.contains('vehicle-radio')) {
            document.getElementById('selected-vehicle-plate').value = e.target.value;
        }
    });

    // ===== CLICK ATTENDANCE ROW (for Clock Out) =====
    document.querySelectorAll('.attendance-row').forEach(row => {
        row.addEventListener('click', function() {
            // Clear all highlights
            document.querySelectorAll('.employee-row').forEach(r => r.classList.remove('table-primary'));
            document.querySelectorAll('.attendance-row').forEach(r => r.classList.remove('table-danger'));
            
            // Highlight this row in red
            this.classList.add('table-danger');
            
            // Set attendance data
            document.getElementById('selected-attendance-id').value = this.dataset.id;
            document.getElementById('selected-display').textContent = this.dataset.name;
            
            // Enable Clock Out, disable Clock In
            document.getElementById('clockout-btn').disabled = false;
            document.getElementById('clockin-btn').disabled = true;
            document.getElementById('selected-employee-id').value = '';

            // Hide vehicle selection when clocking out
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
        const today = new Date();
        const date = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
        document.getElementById('clock_in_time').value = date + 'T' + document.getElementById('modal-clockin-time').value;
        document.getElementById('clockin-form').submit();
    });

    // ===== CLOCK OUT MODAL =====
    document.getElementById('clockOutModal').addEventListener('show.bs.modal', function() {
        document.getElementById('modal-clockout-name').textContent = document.getElementById('selected-display').textContent;
        const now = new Date();
        document.getElementById('modal-clockout-time').value = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
    });

    document.getElementById('confirm-clockout-btn').addEventListener('click', function() {
        const attendanceId = document.getElementById('selected-attendance-id').value;
        const today = new Date();
        const date = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
        const clockOutTime = date + 'T' + document.getElementById('modal-clockout-time').value;
        
        // Set the form action and hidden input, then submit
        const form = document.getElementById('clockout-form');
        form.action = '/attendance/' + attendanceId + '/clock-out';
        document.getElementById('clock_out_time').value = clockOutTime;
        form.submit();
    });
</script>
@endsection
