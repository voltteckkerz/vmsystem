@extends('layouts.app')

@section('content')
<style>
    /* Validation error styles */
    .is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
    .validation-error {
        color: #dc3545;
        font-size: 0.8rem;
        margin-top: 4px;
        display: block;
    }
</style>
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
                                <i class="bi bi-plus-lg me-1"></i>Add Visitor
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
                                        <input type="text" class="form-control nric-input" name="nric_passport[]" required placeholder="e.g. 980101141234" maxlength="12" pattern="\d{12}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
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
                            <a href="/dashboard" class="btn btn-light me-2"><i class="bi bi-x-lg me-1"></i>Cancel</a>
                            <button type="button" class="btn btn-primary px-4" id="register-visit-btn" @if($availablePasses->isEmpty()) disabled @endif><i class="bi bi-person-check me-1"></i>Register Visit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ===== RIGHT COLUMN: REGISTERED VISITORS ===== --}}
        <div class="col-md-5">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px;">
                <div class="card-header bg-white border-0 pt-4 pb-2">
                    <h5 class="mb-3"><b>Registered Visitors</b></h5>
                    <input type="text" class="form-control" id="visitor-search" placeholder="Search by NRIC or Name...">
                </div>
                <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th>Name</th>
                                <th>NRIC</th>
                                <th>Company</th>
                            </tr>
                        </thead>
                        <tbody id="registered-visitors-list">
                            @foreach($registeredVisitors as $rv)
                            @php $isActive = in_array($rv->nric_passport, $activeVisitorNrics); @endphp
                            <tr class="registered-visitor-row {{ $isActive ? 'already-added' : '' }}" 
                                data-nric="{{ $rv->nric_passport }}" 
                                data-name="{{ $rv->name }}" 
                                data-company="{{ $rv->company->name ?? '' }}"
                                style="cursor: {{ $isActive ? 'not-allowed' : 'pointer' }}; position: relative; {{ $isActive ? 'opacity: 0.4; pointer-events: none;' : '' }}">
                                <td>{{ $rv->name }} @if($isActive)<span class="badge bg-warning text-dark ms-1">Checked In</span>@endif</td>
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
                <input type="text" class="form-control mx-auto" id="modal-checkin-time" style="max-width: 200px; font-size: 1.5rem; text-align: center;" placeholder="HH:MM" maxlength="5" pattern="([01]\d|2[0-3]):[0-5]\d" required>
                <small class="text-muted mt-2 d-block">24-hour format (e.g. 08:30, 14:00). Defaults to current time.</small>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i>Cancel</button>
                <button type="button" class="btn btn-primary px-4" id="confirm-checkin-btn"><i class="bi bi-check-lg me-1"></i>Confirm Check-In</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('visitors-container');
    const addBtn = document.getElementById('add-visitor-btn');
    const maxVisitors = 5;

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

    // ===== CHECK-IN MODAL =====
    const modalTimeInput = document.getElementById('modal-checkin-time');
    formatTimeInput(modalTimeInput);

    // When the modal opens, auto-set the time to right now
    document.getElementById('checkinModal').addEventListener('show.bs.modal', function() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        modalTimeInput.value = hours + ':' + minutes;
    });

    // ===== VALIDATION HELPER =====
    function clearValidationErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.validation-error').forEach(el => el.remove());
    }

    function showFieldError(field, message) {
        field.classList.add('is-invalid');
        // Only add error message if one doesn't already exist
        if (!field.parentNode.querySelector('.validation-error')) {
            const errorSpan = document.createElement('span');
            errorSpan.className = 'validation-error';
            errorSpan.textContent = message;
            field.parentNode.appendChild(errorSpan);
        }
    }

    // Clear error styling when user interacts with the field
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('is-invalid')) {
            e.target.classList.remove('is-invalid');
            const errMsg = e.target.parentNode.querySelector('.validation-error');
            if (errMsg) errMsg.remove();
        }
    });
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('is-invalid')) {
            e.target.classList.remove('is-invalid');
            const errMsg = e.target.parentNode.querySelector('.validation-error');
            if (errMsg) errMsg.remove();
        }
    });

    // ===== ERROR TOAST (same style as the global red popup) =====
    function showErrorToast(message) {
        // Remove any existing client-side toast
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

    // ===== REGISTER VISIT BUTTON — VALIDATE BEFORE OPENING MODAL =====
    document.getElementById('register-visit-btn').addEventListener('click', function() {
        clearValidationErrors();
        let isValid = true;
        let errorMessages = [];

        // 1. Validate Person to Meet
        const employeeSelect = document.getElementById('employee_id');
        if (!employeeSelect.value) {
            showFieldError(employeeSelect, 'Please select a person to meet.');
            errorMessages.push('Person to Meet');
            isValid = false;
        }

        // 2. Validate Purpose of Visit
        const purposeInput = document.getElementById('purpose');
        if (!purposeInput.value.trim()) {
            showFieldError(purposeInput, 'Please enter the purpose of visit.');
            errorMessages.push('Purpose of Visit');
            isValid = false;
        }

        // 3. Check at least one visitor exists
        const visitorBlocks = container.querySelectorAll('.visitor-block');
        if (visitorBlocks.length === 0) {
            errorMessages.push('At least one visitor');
            isValid = false;
        }

        // 4. Validate each visitor's fields
        visitorBlocks.forEach(function(block, index) {
            const nricInput = block.querySelector('input[name="nric_passport[]"]');
            const nameInput = block.querySelector('input[name="visitor_name[]"]');
            const companyInput = block.querySelector('input[name="company_name[]"]');
            const passSelect = block.querySelector('select[name="pass_id[]"]');

            // NRIC (only validate visible inputs, not hidden ones from registered visitors)
            if (nricInput && nricInput.type !== 'hidden' && !nricInput.value.trim()) {
                showFieldError(nricInput, 'NRIC / Passport is required.');
                if (!errorMessages.includes('NRIC / Passport')) errorMessages.push('NRIC / Passport');
                isValid = false;
            }
            // Name
            if (nameInput && nameInput.type !== 'hidden' && !nameInput.value.trim()) {
                showFieldError(nameInput, 'Full name is required.');
                if (!errorMessages.includes('Visitor Name')) errorMessages.push('Visitor Name');
                isValid = false;
            }
            // Company
            if (companyInput && companyInput.type !== 'hidden' && !companyInput.value.trim()) {
                showFieldError(companyInput, 'Company name is required.');
                if (!errorMessages.includes('Company Name')) errorMessages.push('Company Name');
                isValid = false;
            }
            // Pass
            if (passSelect && !passSelect.value) {
                showFieldError(passSelect, 'Please assign a pass.');
                if (!errorMessages.includes('Pass Number')) errorMessages.push('Pass Number');
                isValid = false;
            }
        });

        if (!isValid) {
            // Show the red toast popup with summary
            showErrorToast('Please fill in the required fields: ' + errorMessages.join(', '));

            // Scroll to the first error
            const firstError = document.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
            return;
        }

        // All valid — open the check-in modal
        const modal = new bootstrap.Modal(document.getElementById('checkinModal'));
        modal.show();
    });

    // When user clicks "Confirm Check-In", validate time and submit
    document.getElementById('confirm-checkin-btn').addEventListener('click', function() {
        const timeVal = modalTimeInput.value;
        if (!isValidTime(timeVal)) {
            showErrorToast('Please enter a valid time in HH:MM format (e.g. 08:30, 14:00)');
            return;
        }
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        const fullDateTime = year + '-' + month + '-' + day + 'T' + timeVal;
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
            emptyMsg.innerHTML = 'No visitors added yet. Click <b>+ Add Visitor</b> or select from the Registered Visitors list.';
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
            addBtn.innerText = '+ Add Visitor';
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

    // ===== COMPANY LOCK =====
    let lockedCompany = null;

    function updateCompanyFilter() {
        // Check what company is currently in the form
        const companyInputs = document.querySelectorAll('input[name="company_name[]"]');
        let activeCompany = null;

        companyInputs.forEach(input => {
            if (input.value.trim() !== '') {
                activeCompany = input.value.trim();
            }
        });

        lockedCompany = activeCompany;

        // Update the registered visitors list
        document.querySelectorAll('.registered-visitor-row').forEach(row => {
            if (row.classList.contains('already-added')) return; // skip already-added rows

            const rowCompany = row.getAttribute('data-company');

            if (lockedCompany && rowCompany !== lockedCompany) {
                // Different company — grey out and disable
                row.classList.add('company-locked');
                row.style.opacity = '0.3';
                row.style.pointerEvents = 'none';
            } else {
                // Same company or no lock — enable
                row.classList.remove('company-locked');
                row.style.opacity = '';
                row.style.pointerEvents = '';
            }
        });
    }

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
            // If already added or company-locked, do nothing
            if (this.classList.contains('already-added')) return;
            if (this.classList.contains('company-locked')) return;

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
                        showErrorToast('This visitor is already added!');
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
                        showErrorToast('Maximum 5 visitors reached!');
                        return;
                    }
                }

                // Fill in the visitor data — replace inputs with plain text + hidden inputs
                const nricInput = targetBlock.querySelector('input[name="nric_passport[]"]');
                const nameInput = targetBlock.querySelector('input[name="visitor_name[]"]');
                const companyInput = targetBlock.querySelector('input[name="company_name[]"]');

                [nricInput, nameInput, companyInput].forEach(input => {
                    const value = input === nricInput ? nric : input === nameInput ? name : company;
                    // Create hidden input to keep form submission working
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = input.name;
                    hidden.value = value;
                    // Create plain text display
                    const span = document.createElement('span');
                    span.className = 'fw-bold d-block fs-6';
                    span.textContent = value;
                    // Replace the visible input
                    input.parentNode.insertBefore(hidden, input);
                    input.parentNode.insertBefore(span, input);
                    input.remove();
                });

                // Registered visitor block styling (change color codes here)
                targetBlock.classList.remove('bg-light');
                targetBlock.style.backgroundColor = '#599476';  // dark navy background
                targetBlock.style.color = '#ffffff';             // white text
                targetBlock.style.borderColor = '#16213e';       // border color
                targetBlock.classList.add('registered-block');
                targetBlock.querySelectorAll('.form-label, .visitor-number').forEach(el => { el.classList.remove('text-muted'); el.style.color = '#ffffff'; });

                // Remove the popup
                popup.remove();

                // Gray out the row so it can't be added again
                clickedRow.classList.add('already-added');
                clickedRow.style.opacity = '0.4';
                clickedRow.style.pointerEvents = 'none';

                // Lock company filter after adding
                updateCompanyFilter();

                // When this visitor block is removed, re-enable the row and reset fields
                const removeBtn = targetBlock.querySelector('.remove-visitor-btn');
                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        clickedRow.classList.remove('already-added');
                        clickedRow.style.opacity = '1';
                        clickedRow.style.pointerEvents = '';
                        // Re-check company lock after removal
                        setTimeout(updateCompanyFilter, 50);
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