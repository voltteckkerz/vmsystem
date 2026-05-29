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
                <tr style="{{ $visit->status != 'active' ? 'opacity: 0.4;' : '' }}">
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
                            <div class="d-flex flex-column gap-1">
                                <button type="button" class="btn btn-sm btn-danger w-100" data-bs-toggle="modal" data-bs-target="#checkoutModal{{ $visit->id }}">
                                    <i class="bi bi-box-arrow-right me-1"></i>Check Out
                                </button>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary flex-fill" title="Correct check-in time"
                                        onclick="openEditVisitCheckin({{ $visit->id }}, '{{ \Carbon\Carbon::parse($visit->manual_check_in_time)->format('H:i') }}', '{{ \Carbon\Carbon::parse($visit->manual_check_in_time)->format('Y-m-d') }}')">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger flex-fill" title="Cancel this visit"
                                        onclick="openCancelVisit({{ $visit->id }})">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>
                            </div>
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

{{-- Edit Visit Check-In Modal --}}
<div class="modal fade" id="editVisitCheckinModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-fill me-2"></i>Correct Check-In Time</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-3 text-muted">Enter the correct check-in time:</p>
                <form method="POST" id="edit-visit-checkin-form">
                    @csrf
                    <input type="hidden" id="edit-visit-checkin-hidden" name="check_in_time">
                    <input type="text" class="form-control mx-auto" id="edit-visit-checkin-input"
                        style="max-width:200px; font-size:1.5rem; text-align:center;"
                        placeholder="HH:MM" maxlength="5" required>
                    <small class="text-muted mt-2 d-block">24-hour format (e.g. 08:30, 14:00)</small>
                </form>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i>Cancel</button>
                <button type="button" class="btn btn-primary px-4" id="confirm-edit-visit-checkin"><i class="bi bi-check-lg me-1"></i>Save Correction</button>
            </div>
        </div>
    </div>
</div>

{{-- Cancel Visit Modal --}}
<div class="modal fade" id="cancelVisitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-trash-fill me-2"></i>Cancel Visit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:2.5rem;"></i>
                <p class="mt-3 mb-1">Cancel this visitor check-in?</p>
                <p class="text-muted small mb-0">The pass will be freed and the visit record deleted.<br>This <strong>cannot</strong> be undone.</p>
                <form method="POST" id="cancel-visit-form">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i>No, Keep It</button>
                <button type="button" class="btn btn-danger px-4" id="confirm-cancel-visit"><i class="bi bi-trash-fill me-1"></i>Yes, Cancel Visit</button>
            </div>
        </div>
    </div>
</div>

<script>
    // ===== ERROR TOAST =====
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

    // ===== HELPER: Auto-format time input to HH:MM (24-hour) =====
    function formatTimeInput(input) {
        input.addEventListener('input', function() {
            let val = this.value.replace(/[^0-9]/g, '');
            if (val.length >= 3) val = val.substring(0, 2) + ':' + val.substring(2, 4);
            this.value = val.substring(0, 5);
        });
        input.addEventListener('keydown', function(e) {
            if ([8, 46, 9, 37, 39].includes(e.keyCode)) return;
            if (e.key.length === 1 && !/[0-9]/.test(e.key)) e.preventDefault();
        });
    }

    function isValidTime(val) { return /^([01]\d|2[0-3]):[0-5]\d$/.test(val); }

    // Apply auto-format to all checkout time inputs
    document.querySelectorAll('.checkout-time').forEach(input => formatTimeInput(input));

    // When any checkout modal opens, auto-set the time to now
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('show.bs.modal', function() {
            const input = this.querySelector('.checkout-time');
            if (!input) return;
            const now = new Date();
            input.value = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
        });
    });

    // Checkout form submit — validate time then combine with today's date
    document.querySelectorAll('.checkout-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const timeInput  = this.querySelector('.checkout-time');
            const hiddenInput = this.querySelector('.checkout-datetime');
            const timeVal = timeInput.value;
            if (!isValidTime(timeVal)) { showErrorToast('Please enter a valid time in HH:MM format (e.g. 08:30, 14:00)'); return; }
            const today = new Date();
            const date  = today.getFullYear() + '-' + String(today.getMonth()+1).padStart(2,'0') + '-' + String(today.getDate()).padStart(2,'0');
            hiddenInput.value = date + 'T' + timeVal;
            this.submit();
        });
    });

    // ===== EDIT VISIT CHECK-IN =====
    let editVisitId = null, editVisitDate = null;

    function openEditVisitCheckin(visitId, currentTime, currentDate) {
        editVisitId   = visitId;
        editVisitDate = currentDate;
        document.getElementById('edit-visit-checkin-input').value = currentTime;
        new bootstrap.Modal(document.getElementById('editVisitCheckinModal')).show();
    }

    document.getElementById('confirm-edit-visit-checkin').addEventListener('click', function() {
        const timeVal = document.getElementById('edit-visit-checkin-input').value;
        if (!isValidTime(timeVal)) { showErrorToast('Please enter a valid time in HH:MM format (e.g. 08:30, 14:00)'); return; }
        const form = document.getElementById('edit-visit-checkin-form');
        form.action = '/visit/' + editVisitId + '/update-checkin';
        document.getElementById('edit-visit-checkin-hidden').value = editVisitDate + 'T' + timeVal;
        form.submit();
    });

    formatTimeInput(document.getElementById('edit-visit-checkin-input'));

    // ===== CANCEL / DELETE VISIT =====
    let cancelVisitId = null;

    function openCancelVisit(visitId) {
        cancelVisitId = visitId;
        new bootstrap.Modal(document.getElementById('cancelVisitModal')).show();
    }

    document.getElementById('confirm-cancel-visit').addEventListener('click', function() {
        const form = document.getElementById('cancel-visit-form');
        form.action = '/visit/' + cancelVisitId;
        form.submit();
    });
</script>

@endsection
