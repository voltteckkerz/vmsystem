@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0" style="border-radius: 10px;">
                <div class="card-header bg-white border-0 pt-4 pb-2">
                    <h4 class="mb-0"><b>Register Visitor(s)</b></h4>
                </div>

                <div class="card-body p-4">
                    {{-- The form submits to the /visitor route using POST --}}
                    <form method="POST" action="{{ route('visit.store') }}" id="visitor-form">
                        @csrf
                        <input type="hidden" id="manual_check_in_time" name="manual_check_in_time">
                        
                        <h6 class="text-primary mt-4"><b>Visit Details</b></h6>
                        <hr class="mt-1 mb-3">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="employee_id" class="form-label text-muted">Person to Meet</label>
                                <select class="form-select" id="employee_id" name="employee_id" required>
                                    <option value="" selected disabled>Select an Employee...</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="purpose" class="form-label text-muted">Purpose of Visit</label>
                                <input type="text" class="form-control" id="purpose" name="purpose" required placeholder="e.g. Meeting, Interview, Delivery">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="remarks" class="form-label text-muted">Remarks (Optional)</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="2" placeholder="Any additional notes..."></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-end mt-5 mb-2">
                            <h6 class="text-primary mb-0"><b>Visitor Details</b></h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-visitor-btn">
                                + Add Another Visitor
                            </button>
                        </div>
                        <hr class="mt-1 mb-3">

                        <div id="visitors-container">
                            {{-- First Visitor Block --}}
                            <div class="visitor-block border rounded p-3 mb-3 bg-light">
                                <div class="d-flex justify-content-between">
                                    <h6 class="text-muted mb-3 visitor-number">Visitor 1</h6>
                                    {{-- Remove button (hidden for the first visitor) --}}
                                    <button type="button" class="btn-close remove-visitor-btn d-none" aria-label="Close"></button>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label text-muted small">NRIC / Passport</label>
                                        <input type="text" class="form-control" name="nric_passport[]" required placeholder="e.g. 980101-14-1234">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label text-muted small">Full Name</label>
                                        <input type="text" class="form-control" name="visitor_name[]" required placeholder="e.g. John Doe">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label text-muted small">Company Name</label>
                                        <input type="text" class="form-control" name="company_name[]" required placeholder="e.g. TechCorp">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label text-muted small">Assign Pass Number</label>
                                        <select class="form-select pass-select" name="pass_id[]" required>
                                            <option value="" selected disabled>Select an available pass...</option>
                                            @foreach($availablePasses as $pass)
                                                <option value="{{ $pass->id }}">{{ $pass->pass_number }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if($availablePasses->isEmpty())
                            <div class="alert alert-danger mt-3">No passes available! You cannot register new visitors.</div>
                        @endif

                        <div class="d-flex justify-content-end mt-4">
                            <a href="/dashboard" class="btn btn-light me-2">Cancel</a>
                            <button type="button" class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#checkinModal" @if($availablePasses->isEmpty()) disabled @endif>Register Visit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Check-In Confirmation Modal --}}
<div class="modal fade" id="checkinModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Check-In</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-3">Select the check-in time:</p>
                <input type="time" class="form-control mx-auto" id="modal-checkin-time" style="max-width: 200px; font-size: 1.5rem; text-align: center;">
                <small class="text-muted mt-2 d-block">Defaults to current time. Click to change.</small>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4" id="confirm-checkin-btn">Confirm Check-In</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('visitors-container');
    const addBtn = document.getElementById('add-visitor-btn');
    const maxVisitors = 5;

    // ===== CHECK-IN MODAL =====
    const modalTimeInput = document.getElementById('modal-checkin-time');

    // When the modal opens, auto-set the time to right now
    document.getElementById('checkinModal').addEventListener('show.bs.modal', function() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        modalTimeInput.value = hours + ':' + minutes;
    });

    // When user clicks "Confirm Check-In", combine today's date + selected time and submit
    document.getElementById('confirm-checkin-btn').addEventListener('click', function() {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        const fullDateTime = year + '-' + month + '-' + day + 'T' + modalTimeInput.value;
        document.getElementById('manual_check_in_time').value = fullDateTime;
        document.getElementById('visitor-form').submit();
    });

    // Function to update pass dropdowns to prevent duplicates
    function updatePassDropdowns() {
        const selects = document.querySelectorAll('.pass-select');
        const selectedValues = Array.from(selects).map(select => select.value).filter(val => val !== "");

        selects.forEach(select => {
            const options = select.querySelectorAll('option');
            options.forEach(option => {
                if (option.value !== "" && selectedValues.includes(option.value) && select.value !== option.value) {
                    option.disabled = true; // Disable if selected in another dropdown
                } else {
                    option.disabled = false; // Enable otherwise
                }
            });
        });
    }

    // Function to update visitor numbers
    function updateVisitorNumbers() {
        const blocks = container.querySelectorAll('.visitor-block');
        blocks.forEach((block, index) => {
            block.querySelector('.visitor-number').innerText = 'Visitor ' + (index + 1);
            const removeBtn = block.querySelector('.remove-visitor-btn');
            if (index === 0) {
                removeBtn.classList.add('d-none'); // Cannot remove the first visitor
            } else {
                removeBtn.classList.remove('d-none');
            }
        });
        
        // Disable Add button if limit reached
        if (blocks.length >= maxVisitors) {
            addBtn.disabled = true;
            addBtn.innerText = 'Max 5 Visitors Reached';
        } else {
            addBtn.disabled = false;
            addBtn.innerText = '+ Add Another Visitor';
        }
    }

    // Event listener for adding a new visitor
    addBtn.addEventListener('click', function() {
        const blocks = container.querySelectorAll('.visitor-block');
        if (blocks.length >= maxVisitors) return;

        // Clone the first block
        const newBlock = blocks[0].cloneNode(true);
        
        // Clear inputs
        newBlock.querySelectorAll('input').forEach(input => input.value = '');
        newBlock.querySelectorAll('select').forEach(select => {
            select.value = '';
            select.addEventListener('change', updatePassDropdowns); // re-attach event listener
        });
        
        // Attach remove event listener
        newBlock.querySelector('.remove-visitor-btn').addEventListener('click', function() {
            newBlock.remove();
            updateVisitorNumbers();
            updatePassDropdowns();
        });

        container.appendChild(newBlock);
        updateVisitorNumbers();
        updatePassDropdowns();
    });

    // Attach change listener to the initial select
    document.querySelectorAll('.pass-select').forEach(select => {
        select.addEventListener('change', updatePassDropdowns);
    });

    // Auto-fill logic using 'focusout' (triggers when user clicks away from NRIC box)
    container.addEventListener('focusout', function(e) {
        // Only trigger if they were typing in an NRIC box
        if (e.target && e.target.name === 'nric_passport[]') {
            let nric = e.target.value.trim();
            if (nric === '') return; // Do nothing if it's empty

            // Find the exact block they are typing in (so we don't accidentally fill Visitor 2's name into Visitor 1's box)
            let block = e.target.closest('.visitor-block');
            let nameInput = block.querySelector('input[name="visitor_name[]"]');
            let companyInput = block.querySelector('input[name="company_name[]"]');

            // Secretly ask our new API Route if this NRIC exists!
            fetch('/api/visitor/' + nric)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        // Success! Auto-fill the form boxes instantly!
                        nameInput.value = data.name;
                        if (data.company) {
                            companyInput.value = data.company.name;
                        }
                    }
                })
                .catch(error => console.log('Visitor not found yet'));
        }
    });

});
</script>
@endsection