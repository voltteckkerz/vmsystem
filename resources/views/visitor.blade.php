@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0" style="border-radius: 10px;">
                <div class="card-header bg-white border-0 pt-4 pb-2">
                    <h4 class="mb-0"><b>Register New Visitor(s)</b></h4>
                </div>

                <div class="card-body p-4">
                    {{-- The form submits to the /visitor route using POST --}}
                    <form method="POST" action="{{ route('visit.store') }}" id="visitor-form">
                        @csrf
                        
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

                        <div class="mb-4">
                            <label for="manual_check_in_time" class="form-label text-primary mb-0"><h6><b>Check-In Time</b></h6></label>
                            <input type="datetime-local" class="form-control" id="manual_check_in_time" name="manual_check_in_time" required>
                            <small class="form-text">Default to current time. Change if visitor arrived earlier.</small>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="/dashboard" class="btn btn-light me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4" @if($availablePasses->isEmpty()) disabled @endif>Register Visit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('visitors-container');
    const addBtn = document.getElementById('add-visitor-btn');
    const maxVisitors = 5;
    const now =new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset()); // Adjust for timezone
    document.getElementById('manual_check_in_time').value = now.toISOString().slice(0,16); // Set default value to current time (without seconds)
    
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