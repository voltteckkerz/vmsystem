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
                    <td><span class="badge bg-primary">{{ App\Models\Pass::find($visitor->pivot->pass_id)->pass_number }}</span></td>
                    
                    <td>{{ $visitor->name }} <br><small class="text-muted">{{ $visitor->nric_passport }}</small></td>
                    <td>{{ $visitor->company->name }}</td>
                    
                    <td>{{ $visit->employee->name }}</td>
                    
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
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#checkoutModal{{ $visit->id }}">
                                Check Out
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
                    <td colspan="7" class="text-center">No active visitors right now.</td>
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
                        <input type="time" class="form-control mx-auto checkout-time" style="max-width: 200px; font-size: 1.5rem; text-align: center;" required>
                        <small class="text-muted mt-2 d-block">Defaults to current time. Click to change.</small>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4">Confirm Check Out</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach

<script>
    // When any checkout modal opens, auto-set the time to right now
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('show.bs.modal', function() {
            const input = this.querySelector('.checkout-time');
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            input.value = hours + ':' + minutes;
        });
    });

    // When checkout form submits, combine today's date + selected time
    document.querySelectorAll('.checkout-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const timeInput = this.querySelector('.checkout-time');
            const hiddenInput = this.querySelector('.checkout-datetime');
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            hiddenInput.value = year + '-' + month + '-' + day + 'T' + timeInput.value;
            this.submit();
        });
    });
</script>

@endsection
