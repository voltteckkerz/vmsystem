@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">

        {{-- ===== LEFT COLUMN: REGISTRATION FORM ===== --}}
        <div class="col-md-7">
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
                                    <button type="button" class="btn-close remove-visitor-btn" aria-label="Close"></button>
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

        {{-- ===== RIGHT COLUMN: REGISTERED VISITORS ===== --}}
        <div class="col-md-5">
            <div class="card shadow-sm border-0" style="border-radius: 10px;">
                <div class="card-header bg-white border-0 pt-4 pb-2">
                    <h5 class="mb-3"><b>Registered Visitors</b></h5>
                    <input type="text" class="form-control" id="visitor-search" placeholder="Search by NRIC or Name...">
                </div>
                <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Name</th>
                                <th>NRIC</th>
                                <th>Company</th>
                            </tr>
                        </thead>
                        <tbody id="registered-visitors-list">
                            @foreach($registeredVisitors as $rv)
                            <tr class="registered-visitor-row" 
                                data-nric="{{ $rv->nric_passport }}" 
                                data-name="{{ $rv->name }}" 
                                data-company="{{ $rv->company->name ?? '' }}"
                                style="cursor: pointer; position: relative;">
                                <td>{{ $rv->name }}</td>
                                <td>{{ $rv->nric_passport }}</td>
                                <td>{{ $rv->company->name ?? '-' }}</td>
                            </tr>
                            @endforeach

                            @if($registeredVisitors->isEmpty())
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">No registered visitors yet.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
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

        // Show/hide the empty message
        let emptyMsg = document.getElementById('no-visitors-msg');
        if (!emptyMsg) {
            emptyMsg = document.createElement('div');
            emptyMsg.id = 'no-visitors-msg';
            emptyMsg.className = 'text-center text-muted py-4';
            emptyMsg.innerHTML = 'No visitors added yet. Click <b>+ Add Another Visitor</b> or select from the Registered Visitors list.';
            container.parentNode.insertBefore(emptyMsg, container.nextSibling);
        }

        if (blocks.length === 0) {
            emptyMsg.style.display = '';
        } else {
            emptyMsg.style.display = 'none';
        }

        blocks.forEach((block, index) => {
            block.querySelector('.visitor-number').innerText = 'Visitor ' + (index + 1);
            block.querySelector('.remove-visitor-btn').classList.remove('d-none');
        });
        
        // Disable Add button if limit reached
        if (blocks.length >= maxVisitors) {
            addBtn.disabled = true;
            addBtn.innerText = 'Max 5 Visitors Reached';
        } else {
            addBtn.disabled = false;
            addBtn.innerText = '+ Add Another Visitor';
        }

        // Disable Register button if no visitors
        const registerBtn = document.querySelector('[data-bs-target="#checkinModal"]');
        if (registerBtn) {
            registerBtn.disabled = (blocks.length === 0);
        }
    }

    // Save a template of the first visitor block for cloning later
    const blockTemplate = container.querySelector('.visitor-block').cloneNode(true);
    blockTemplate.querySelectorAll('input').forEach(input => input.value = '');
    blockTemplate.querySelectorAll('select').forEach(select => select.value = '');

    // Remove the first block so the page starts empty
    container.querySelector('.visitor-block').remove();
    updateVisitorNumbers();

    // Event listener for adding a new visitor
    addBtn.addEventListener('click', function() {
        const blocks = container.querySelectorAll('.visitor-block');
        if (blocks.length >= maxVisitors) return;

        // Clone from the saved template (not from the first block)
        const newBlock = blockTemplate.cloneNode(true);
        
        // Clear inputs
        newBlock.querySelectorAll('input').forEach(input => input.value = '');
        newBlock.querySelectorAll('select').forEach(select => {
            select.value = '';
            select.addEventListener('change', updatePassDropdowns);
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

    // Attach remove listener to the first visitor block's close button
    const firstBlock = container.querySelector('.visitor-block');
    if (firstBlock) {
        firstBlock.querySelector('.remove-visitor-btn').addEventListener('click', function() {
            firstBlock.remove();
            updateVisitorNumbers();
            updatePassDropdowns();
        });
    }

    // ===== AUTO-FILL from NRIC =====
    container.addEventListener('focusout', function(e) {
        if (e.target && e.target.name === 'nric_passport[]') {
            let nric = e.target.value.trim();
            if (nric === '') return;

            let block = e.target.closest('.visitor-block');
            let nameInput = block.querySelector('input[name="visitor_name[]"]');
            let companyInput = block.querySelector('input[name="company_name[]"]');

            fetch('/api/visitor/' + nric)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        nameInput.value = data.name;
                        if (data.company) {
                            companyInput.value = data.company.name;
                        }
                    }
                })
                .catch(error => console.log('Visitor not found yet'));
        }
    });

    // ===== SEARCH FILTER =====
    document.getElementById('visitor-search').addEventListener('input', function() {
        const searchValue = this.value.trim().toLowerCase();
        const rows = document.querySelectorAll('.registered-visitor-row');

        rows.forEach(function(row) {
            const nric = row.getAttribute('data-nric').toLowerCase();
            const name = row.getAttribute('data-name').toLowerCase();

            if (nric.includes(searchValue) || name.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // ===== CLICK TO ADD VISITOR =====
    document.querySelectorAll('.registered-visitor-row').forEach(function(row) {
        row.addEventListener('click', function(e) {
            // If already added, do nothing
            if (this.classList.contains('already-added')) return;

            // Remove any existing popup
            document.querySelectorAll('.add-popup').forEach(p => p.remove());

            // Get visitor data from the row
            const name = this.getAttribute('data-name');
            const nric = this.getAttribute('data-nric');
            const company = this.getAttribute('data-company');
            const clickedRow = this;

            // Create the "Add" popup button
            const popup = document.createElement('span');
            popup.className = 'add-popup badge bg-success ms-2';
            popup.style.cssText = 'cursor:pointer; font-size:0.85rem; padding:5px 12px;';
            popup.innerText = '+ Add';

            // When user clicks the "Add" button
            popup.addEventListener('click', function(evt) {
                evt.stopPropagation();

                // Check if this NRIC is already in the form
                const allNricInputs = document.querySelectorAll('input[name="nric_passport[]"]');
                for (let i = 0; i < allNricInputs.length; i++) {
                    if (allNricInputs[i].value.trim() === nric) {
                        alert('This visitor is already added!');
                        popup.remove();
                        return;
                    }
                }

                const blocks = document.querySelectorAll('.visitor-block');
                let targetBlock = null;

                for (let i = 0; i < blocks.length; i++) {
                    const nricInput = blocks[i].querySelector('input[name="nric_passport[]"]');
                    if (nricInput.value.trim() === '') {
                        targetBlock = blocks[i];
                        break;
                    }
                }

                if (!targetBlock) {
                    const addVisitorBtn = document.getElementById('add-visitor-btn');
                    if (!addVisitorBtn.disabled) {
                        addVisitorBtn.click();
                        const allBlocks = document.querySelectorAll('.visitor-block');
                        targetBlock = allBlocks[allBlocks.length - 1];
                    } else {
                        alert('Maximum 5 visitors reached!');
                        return;
                    }
                }

                // Fill in the visitor data
                targetBlock.querySelector('input[name="nric_passport[]"]').value = nric;
                targetBlock.querySelector('input[name="visitor_name[]"]').value = name;
                targetBlock.querySelector('input[name="company_name[]"]').value = company;

                // Remove the popup
                popup.remove();

                // Gray out the row so it can't be added again
                clickedRow.classList.add('already-added');
                clickedRow.style.opacity = '0.4';
                clickedRow.style.pointerEvents = 'none';

                // Flash the filled block green briefly
                targetBlock.style.transition = 'background-color 0.3s';
                targetBlock.style.backgroundColor = '#d4edda';
                setTimeout(() => { targetBlock.style.backgroundColor = ''; }, 1000);

                // When this visitor block is removed, re-enable the row
                const removeBtn = targetBlock.querySelector('.remove-visitor-btn');
                if (removeBtn) {
                    const originalHandler = removeBtn.onclick;
                    removeBtn.addEventListener('click', function() {
                        clickedRow.classList.remove('already-added');
                        clickedRow.style.opacity = '1';
                        clickedRow.style.pointerEvents = '';
                    });
                }
            });

            // Append the popup next to the name
            const nameCell = this.querySelector('td');
            nameCell.appendChild(popup);
        });
    });

});
</script>
@endsection