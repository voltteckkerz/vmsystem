@extends('layouts.app')

@section('content')
<style>
    /* ===== DRAG & DROP ZONE ===== */
    .drop-zone {
        border: 2.5px dashed #b0c4de;
        border-radius: 12px;
        background: #f8fbff;
        padding: 24px 24px;
        text-align: center;
        cursor: pointer;
        transition: all 0.22s ease;
        position: relative;
    }
    .drop-zone:hover,
    .drop-zone.drag-over {
        border-color: #0d6efd;
        background: #eef4ff;
        box-shadow: 0 0 0 4px rgba(13,110,253,0.08);
    }
    .drop-zone.drag-over { transform: scale(1.01); }
    .drop-zone.has-file {
        border-color: #28a745;
        background: #f0fdf4;
    }
    .drop-zone-icon {
        font-size: 2rem;
        color: #b0c4de;
        margin-bottom: 8px;
        transition: color 0.22s, transform 0.22s;
        display: block;
    }
    .drop-zone:hover .drop-zone-icon,
    .drop-zone.drag-over .drop-zone-icon { color: #0d6efd; transform: translateY(-4px); }
    .drop-zone.has-file   .drop-zone-icon { color: #28a745; }
    .drop-zone-label {
        font-size: 0.92rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 3px;
    }
    .drop-zone-sub {
        font-size: 0.78rem;
        color: #adb5bd;
    }
    .drop-zone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }
    /* ===== FILE PREVIEW PILL ===== */
    .file-preview {
        display: none;
        align-items: center;
        gap: 12px;
        background: #fff;
        border: 1.5px solid #28a745;
        border-radius: 10px;
        padding: 12px 18px;
        margin-top: 16px;
        box-shadow: 0 2px 8px rgba(40,167,69,0.1);
    }
    .file-preview.visible { display: flex; }
    .file-preview-icon { font-size: 1.6rem; flex-shrink: 0; }
    .file-preview-icon.csv   { color: #0d6efd; }
    .file-preview-icon.excel { color: #217346; }
    .file-preview-info { flex: 1; text-align: left; min-width: 0; }
    .file-preview-name {
        font-weight: 700;
        font-size: 0.9rem;
        color: #212529;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .file-preview-size { font-size: 0.75rem; color: #6c757d; }
    .file-clear-btn {
        background: none;
        border: none;
        color: #dc3545;
        font-size: 1.1rem;
        cursor: pointer;
        padding: 4px;
        border-radius: 50%;
        transition: background 0.15s;
        flex-shrink: 0;
    }
    .file-clear-btn:hover { background: #fdecea; }
    /* ===== UPLOAD BUTTON ===== */
    .upload-btn {
        background: linear-gradient(135deg, #0d6efd, #0a58ca);
        color: #fff;
        border: none;
        padding: 12px 36px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.18s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 3px 12px rgba(13,110,253,0.3);
    }
    .upload-btn:hover { transform: translateY(-1px); filter: brightness(1.06); box-shadow: 0 5px 18px rgba(13,110,253,0.38); }
    .upload-btn:disabled { opacity: 0.45; cursor: not-allowed; transform: none; filter: none; }
</style>

<div class="container">

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</div>
    @endif

    {{-- Validation Errors (row-by-row) --}}
    @if(session('import_errors'))
        <div class="alert alert-danger">
            <strong><i class="bi bi-x-circle-fill me-1"></i>Import failed — please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
            <h4 class="mb-0"><b>Import Visitor Data</b></h4>
            <p class="text-muted mb-0 mt-1" style="font-size:0.88rem;">
                Upload a CSV or Excel file to import old visitor records.
                Supported: <strong>.csv</strong>, <strong>.xlsx</strong>, <strong>.xls</strong> &nbsp;·&nbsp;
                All columns except <strong>Remarks</strong> are required.
            </p>
        </div>

        <div class="card-body px-4 pb-4">

            {{-- Template Download --}}
            <div class="mb-4">
                <a href="{{ asset('templates/visitor_import_template.xlsx') }}"
                   class="btn btn-sm text-white px-4 py-2 rounded-pill shadow-sm"
                   style="background: linear-gradient(135deg, #217346, #185a34); transition: all 0.2s ease;"
                   onmouseover="this.style.background='linear-gradient(135deg,#185a34,#0f4025)';this.style.transform='translateY(-1px)';"
                   onmouseout="this.style.background='linear-gradient(135deg,#217346,#185a34)';this.style.transform='';"
                   download>
                    <i class="bi bi-file-earmark-excel me-2"></i>Download Excel Template
                </a>
            </div>

            {{-- Upload Form with Drag & Drop --}}
            <form method="POST" action="{{ route('import.upload') }}" enctype="multipart/form-data" id="import-form">
                @csrf

                {{-- Drop Zone --}}
                <div class="drop-zone" id="drop-zone">
                    <input type="file" name="csv_file" id="csv-file-input" accept=".csv,.xlsx,.xls" required>
                    <i class="bi bi-cloud-arrow-up drop-zone-icon" id="drop-icon"></i>
                    <div class="drop-zone-label" id="drop-label">Drag & drop your file here</div>
                    <div class="drop-zone-sub" id="drop-sub">or <span style="color:#0d6efd; font-weight:600;">click to browse</span> &nbsp;·&nbsp; .csv, .xlsx, .xls</div>
                </div>

                {{-- File Preview --}}
                <div class="file-preview" id="file-preview">
                    <i class="bi bi-file-earmark-spreadsheet file-preview-icon" id="file-icon"></i>
                    <div class="file-preview-info">
                        <div class="file-preview-name" id="file-name">—</div>
                        <div class="file-preview-size" id="file-size"></div>
                    </div>
                    <button type="button" class="file-clear-btn" id="file-clear-btn" title="Remove file">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>

                {{-- Submit --}}
                <div class="mt-4">
                    <button type="submit" class="upload-btn" id="upload-btn" disabled>
                        <i class="bi bi-upload"></i>
                        Upload & Import
                    </button>
                    <span class="text-muted ms-3 small" id="upload-hint">Select a file to enable upload.</span>
                </div>
            </form>

            <hr class="my-4">

            {{-- Column Format Reference --}}
            <h6 class="text-muted"><b>Column Format Reference</b></h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Column</th>
                            <th>Example</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>visitor_name</td><td>John Doe</td><td>Full name</td></tr>
                        <tr><td>nric_passport</td><td>980101141234</td><td>Numbers only, no hyphens</td></tr>
                        <tr><td>company_name</td><td>TechCorp Sdn Bhd</td><td>Auto-created if new</td></tr>
                        <tr><td>person_to_meet</td><td>Ahmad</td><td>Must match an employee name</td></tr>
                        <tr><td>purpose</td><td>Meeting</td><td>Reason for visit</td></tr>
                        <tr><td>pass_number</td><td>P0-01</td><td>Auto-created if new</td></tr>
                        <tr><td>check_in_time</td><td>01/05/2026 09:00</td><td>Format: DD/MM/YYYY HH:MM</td></tr>
                        <tr><td>check_out_time</td><td>01/05/2026 17:00</td><td>Must be after check-in</td></tr>
                        <tr><td>remarks</td><td>Delivered equipment</td><td>Optional visit remarks</td></tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<script>
    const dropZone    = document.getElementById('drop-zone');
    const fileInput   = document.getElementById('csv-file-input');
    const filePreview = document.getElementById('file-preview');
    const fileName    = document.getElementById('file-name');
    const fileSize    = document.getElementById('file-size');
    const fileIcon    = document.getElementById('file-icon');
    const clearBtn    = document.getElementById('file-clear-btn');
    const uploadBtn   = document.getElementById('upload-btn');
    const uploadHint  = document.getElementById('upload-hint');
    const dropLabel   = document.getElementById('drop-label');
    const dropSub     = document.getElementById('drop-sub');
    const dropIcon    = document.getElementById('drop-icon');

    const ALLOWED = ['.csv', '.xlsx', '.xls'];

    function formatBytes(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    function getExt(name) {
        return name.slice(name.lastIndexOf('.')).toLowerCase();
    }

    function applyFile(file) {
        const ext = getExt(file.name);
        if (!ALLOWED.includes(ext)) {
            clearFile();
            uploadHint.textContent = '❌ Invalid file type. Use .csv, .xlsx, or .xls';
            uploadHint.style.color = '#dc3545';
            return;
        }

        // Show preview
        fileName.textContent = file.name;
        fileSize.textContent = formatBytes(file.size);
        fileIcon.className   = 'bi ' + (ext === '.csv' ? 'bi-filetype-csv file-preview-icon csv' : 'bi-file-earmark-excel file-preview-icon excel');
        filePreview.classList.add('visible');

        // Update zone
        dropZone.classList.add('has-file');
        dropIcon.className = 'bi bi-check-circle-fill drop-zone-icon';
        dropLabel.textContent = 'File ready to upload';
        dropSub.innerHTML = 'Click the zone to change file';

        // Enable upload
        uploadBtn.disabled = false;
        uploadHint.textContent = 'Ready to import.';
        uploadHint.style.color = '#28a745';
    }

    function clearFile() {
        fileInput.value = '';
        filePreview.classList.remove('visible');
        dropZone.classList.remove('has-file');
        dropIcon.className = 'bi bi-cloud-arrow-up drop-zone-icon';
        dropLabel.textContent = 'Drag & drop your file here';
        dropSub.innerHTML = 'or <span style="color:#0d6efd;font-weight:600;">click to browse</span> &nbsp;·&nbsp; .csv, .xlsx, .xls';
        uploadBtn.disabled = true;
        uploadHint.textContent = 'Select a file to enable upload.';
        uploadHint.style.color = '';
    }

    // File input change
    fileInput.addEventListener('change', function() {
        if (this.files.length) applyFile(this.files[0]);
    });

    // Clear button
    clearBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        clearFile();
    });

    // Drag events
    ['dragenter', 'dragover'].forEach(evt => {
        dropZone.addEventListener(evt, function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('drag-over');
        });
    });
    ['dragleave', 'dragend', 'drop'].forEach(evt => {
        dropZone.addEventListener(evt, function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('drag-over');
        });
    });
    dropZone.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        if (dt.files.length) {
            // Transfer dropped file into the input
            const file = dt.files[0];
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;
            applyFile(file);
        }
    });
</script>
@endsection
