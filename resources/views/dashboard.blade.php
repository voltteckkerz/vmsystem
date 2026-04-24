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
                {{-- Check Out Modal for this specific visit --}}
                <div class="modal fade" id="checkoutModal{{ $visit->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('visit.checkout', $visit->id) }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Confirm Check Out</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Set the check-out time for this visit:</p>
                                    <input type="datetime-local" class="form-control checkout-time" name="manual_check_out_time" required>
                                    <small class="text-muted">Defaults to current time. Change if visitor left earlier.</small>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Confirm Check Out</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
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
<script>
    // When any checkout modal opens, auto-set the time to right now
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('show.bs.modal', function() {
            const input = this.querySelector('.checkout-time');
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            input.value = now.toISOString().slice(0,16);
        });
    });
</script>

@endsection
