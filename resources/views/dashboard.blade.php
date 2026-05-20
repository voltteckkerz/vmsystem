@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Live Visitor Status</h3>
    
    <table class="table table-striped table-bordered mt-3">
        <thead class="table-dark">
            <tr>
                <th>Pass No.</th>
                <th>Visitor Name</th>
                <th>Company</th>
                <th>Person to Meet</th>
                <th>Remarks</th>
                <th>Check-In Time</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            {{-- Loop through every active visit --}}
            @foreach($liveVisits as $visit)
                
                {{-- Since a visit can have multiple visitors, we loop through them --}}
                @foreach($visit->visitors as $visitor)
                <tr>
                    {{-- Get the pass number from the pivot table! --}}
                    <td><span class="badge bg-primary">{{ $visitor->pivot->pass_id ? App\Models\Pass::find($visitor->pivot->pass_id)->pass_number : 'N/A' }}</span></td>
                    
                    <td>{{ $visitor->name }} <br><small class="text-muted">{{ $visitor->nric_passport }}</small></td>
                    <td>{{ $visitor->company->name }}</td>
                    
                    <td>{{ $visit->employee->name }}</td>
                    
                    <td>{{ $visit->remarks ?? '-' }}</td>
                    
                    {{-- Format the date nicely --}}
                    <td>{{ \Carbon\Carbon::parse($visit->manual_check_in_time)->format('d M Y, h:i A') }}</td>
                    
                    <td>
                        @if($visit->status == 'active')
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Checked Out</span>
                        @endif
                    </td>
                    
                    <td>
                        @if($visit->status == 'active')
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#checkoutModal{{ $visit->id }}"
                                data-checkin="{{ \Carbon\Carbon::parse($visit->manual_check_in_time)->format('H:i') }}">
                                <i class="bi bi-box-arrow-right me-1"></i>Check Out
                            </button>
                        @else
                            <span class="text-muted small">Checked out at: <br>{{ \Carbon\Carbon::parse($visit->manual_check_out_time)->format('h:i A') }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                
            @endforeach
            
            {{-- What to show if no one is visiting --}}
            @if($liveVisits->isEmpty())
                <tr>
                    <td colspan="8" class="text-center">No active visitors right now.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

{{-- All Checkout Modals are placed OUTSIDE the table so the browser doesn't break them --}}
@foreach($liveVisits as $visit)
    @if($visit->status == 'active')
    <div class="modal fade" id="checkoutModal{{ $visit->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('visit.checkout', $visit->id) }}" method="POST" class="checkout-form">
                    @csrf
                    <input type="hidden" class="checkout-datetime" name="manual_check_out_time">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Check Out</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <p class="mb-3">Select the check-out time:</p>
                        <input type="text" class="form-control mx-auto checkout-time" style="max-width: 200px; font-size: 1.5rem; text-align: center;" placeholder="HH:MM" maxlength="5" pattern="([01]\d|2[0-3]):[0-5]\d" required>
                        <small class="text-muted mt-2 d-block">24-hour format (e.g. 08:30, 14:00). Defaults to current time.</small>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i>Cancel</button>
                        <button type="submit" class="btn btn-danger px-4"><i class="bi bi-box-arrow-right me-1"></i>Confirm Check Out</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach

<script>
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
            if ([8, 46, 9, 37, 39].includes(e.keyCode)) return;
            if (e.key.length === 1 && !/[0-9]/.test(e.key)) e.preventDefault();
        });
    }

    function isValidTime(val) {
        return /^([01]\d|2[0-3]):[0-5]\d$/.test(val);
    }

    // Apply auto-format to all checkout time inputs
    document.querySelectorAll('.checkout-time').forEach(input => formatTimeInput(input));

    // When any checkout modal opens, auto-set the time and enforce minimum time
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('show.bs.modal', function(event) {
            const input = this.querySelector('.checkout-time');
            if (!input) return;

            const now = new Date();
            input.value = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
        });
    });

    // When checkout form submits, validate and combine today's date + selected time
    document.querySelectorAll('.checkout-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const timeInput = this.querySelector('.checkout-time');
            const hiddenInput = this.querySelector('.checkout-datetime');
            const timeVal = timeInput.value;

            if (!isValidTime(timeVal)) {
                alert('Please enter a valid time in HH:MM format (e.g. 08:30, 14:00)');
                return;
            }

            // Get checkin time from the trigger button
            const modalEl = this.closest('.modal');
            const trigger = modalEl ? document.querySelector('[data-bs-target="#' + modalEl.id + '"]') : null;
            if (trigger && trigger.dataset.checkin && timeVal < trigger.dataset.checkin) {
                alert('Check-out time cannot be earlier than check-in time (' + trigger.dataset.checkin + ')');
                return;
            }

            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            hiddenInput.value = year + '-' + month + '-' + day + 'T' + timeVal;
            this.submit();

    
        });
    });
</script>

@endsection
