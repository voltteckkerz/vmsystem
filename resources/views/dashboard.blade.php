@extends('layouts.app')

@section('content')
<style>
    body {
        background-image: url('{{ asset(str_replace(' ', '%20', 'images/login background.jpg')) }}?v={{ filemtime(public_path('images/login background.jpg')) }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
    }
    /* ── Section cards ── */
    .dash-card {
        background: rgba(255,255,255,0.90);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.65);
        border-radius: 18px;
        box-shadow: 0 8px 32px rgba(16,24,40,0.12);
        padding: 20px 24px;
        margin-bottom: 20px;
    }
    .section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #16181d;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 7px;
    }
    .section-title i { color: #6b6f78; }
    /* ── Live visitor table ── */
    .table-rounded-wrapper {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #dee2e6;
    }
    .table-rounded-wrapper table { margin-bottom: 0; }
    /* ── Analytics divider ── */
    .analytics-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 8px 0 20px;
    }
    .analytics-divider span {
        font-size: 0.8rem;
        font-weight: 700;
        color: #fff;
        background: rgba(22,24,29,0.75);
        backdrop-filter: blur(8px);
        padding: 6px 16px;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        white-space: nowrap;
    }
    .analytics-divider hr { flex: 1; border-color: rgba(255,255,255,0.7); opacity: 1; margin: 0; }
    /* ── Purpose doughnut center ── */
    .chart-wrap { position: relative; }
    .no-data { text-align: center; color: #b0b4bc; font-size: 0.85rem; padding: 28px 0; }
</style>

<div class="container pb-4">

    {{-- ── Live Visitor Status ───────────────────────────────────────────── --}}
    <div class="dash-card p-0" style="overflow:hidden;">
        <div class="px-4 pt-3 pb-2">
            <div class="section-title mb-0"><i class="bi bi-broadcast"></i> Live Visitor Status</div>
        </div>
        <div class="table-rounded-wrapper" style="border-radius:0;border-left:none;border-right:none;border-bottom:none;">
            <table class="table table-striped table-bordered mb-0">
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
                    @foreach($liveVisits as $visit)
                        @foreach($visit->visitors as $visitor)
                        <tr style="{{ $visit->status != 'active' ? 'opacity:0.4;' : '' }}">
                            <td><span class="badge bg-primary">{{ $visitor->pivot->pass_id ? App\Models\Pass::find($visitor->pivot->pass_id)->pass_number : 'N/A' }}</span></td>
                            <td>{{ $visitor->pivot->visitor_name ?? $visitor->name }}<br><small class="text-muted">{{ $visitor->nric_passport }}</small></td>
                            <td>{{ $visitor->pivot->visitor_company ?? ($visitor->company->name ?? '-') }}</td>
                            <td>{{ $visit->employee->name }}</td>
                            <td>{{ $visit->remarks ?? '-' }}</td>
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
                                    <span class="text-muted small">Checked out at:<br>{{ \Carbon\Carbon::parse($visit->manual_check_out_time)->format('h:i A') }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                    @if($liveVisits->isEmpty())
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No active visitors right now.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Analytics Divider ────────────────────────────────────────────── --}}
    <div class="analytics-divider">
        <hr><span><i class="bi bi-bar-chart-fill me-1"></i>Analytics — {{ now()->format('F Y') }}</span><hr>
    </div>

    {{-- ── Trend Charts ─────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="dash-card mb-0">
                <div class="section-title"><i class="bi bi-graph-up"></i> Visitor Trend — Last 7 Days</div>
                <canvas id="chartVisitorTrend" height="160"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="dash-card mb-0">
                <div class="section-title"><i class="bi bi-graph-up-arrow"></i> Attendance Trend — Last 7 Days</div>
                <canvas id="chartAttendTrend" height="160"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Peak Hours + Purpose ─────────────────────────────────────────── --}}
    <div class="row g-3">
        <div class="col-md-7">
            <div class="dash-card mb-0">
                <div class="section-title"><i class="bi bi-clock"></i> Peak Visitor Hours — This Month</div>
                <canvas id="chartPeakHours" height="170"></canvas>
            </div>
        </div>
        <div class="col-md-5">
            <div class="dash-card mb-0">
                <div class="section-title"><i class="bi bi-pie-chart"></i> Visit Purpose — This Month</div>
                @if($purposes->count())
                    <div style="max-height:220px;display:flex;justify-content:center;">
                        <canvas id="chartPurpose" style="max-height:220px;"></canvas>
                    </div>
                @else
                    <div class="no-data"><i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px;"></i>No data yet this month</div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ── Modals ────────────────────────────────────────────────────────────── --}}
@foreach($liveVisits as $visit)
    @if($visit->status == 'active')
    <div class="modal fade vms-modal" id="checkoutModal{{ $visit->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('visit.checkout', $visit->id) }}" method="POST" class="checkout-form">
                    @csrf
                    <input type="hidden" class="checkout-datetime" name="manual_check_out_time">
                    <button type="button" class="vms-modal-close" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
                    <div class="modal-body text-center pt-4 pb-2">
                        <div class="vms-modal-icon icon-danger"><i class="bi bi-box-arrow-right"></i></div>
                        <h5 class="vms-modal-title">Confirm Check Out</h5>
                        <p class="vms-modal-sub mb-3">Select the check-out time:</p>
                        <input type="text" class="form-control mx-auto checkout-time" style="max-width:200px;font-size:1.5rem;text-align:center;" placeholder="HH:MM" maxlength="5" required>
                        <small class="text-muted mt-2 d-block">24-hour format (e.g. 08:30, 14:00). Defaults to current time.</small>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4"><i class="bi bi-box-arrow-right me-1"></i>Check Out</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach

<div class="modal fade vms-modal" id="editVisitCheckinModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <button type="button" class="vms-modal-close" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
            <div class="modal-body text-center pt-4 pb-2">
                <div class="vms-modal-icon icon-dark"><i class="bi bi-pencil-fill"></i></div>
                <h5 class="vms-modal-title">Correct Check-In Time</h5>
                <p class="vms-modal-sub mb-3">Enter the correct check-in time:</p>
                <form method="POST" id="edit-visit-checkin-form">
                    @csrf
                    <input type="hidden" id="edit-visit-checkin-hidden" name="check_in_time">
                    <input type="text" class="form-control mx-auto" id="edit-visit-checkin-input"
                        style="max-width:200px;font-size:1.5rem;text-align:center;"
                        placeholder="HH:MM" maxlength="5" required>
                    <small class="text-muted mt-2 d-block">24-hour format (e.g. 08:30, 14:00)</small>
                </form>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4" id="confirm-edit-visit-checkin"><i class="bi bi-check-lg me-1"></i>Save Correction</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade vms-modal" id="cancelVisitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <button type="button" class="vms-modal-close" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
            <div class="modal-body text-center pt-4 pb-2">
                <div class="vms-modal-icon icon-danger"><i class="bi bi-trash-fill"></i></div>
                <h5 class="vms-modal-title">Cancel Visit</h5>
                <p class="vms-modal-sub mb-1">Cancel this visitor check-in?</p>
                <p class="text-muted small mb-0">The pass will be freed and the visit record deleted.<br>This <strong>cannot</strong> be undone.</p>
                <form method="POST" id="cancel-visit-form">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">No, Keep It</button>
                <button type="button" class="btn btn-danger px-4" id="confirm-cancel-visit"><i class="bi bi-trash-fill me-1"></i>Yes, Cancel Visit</button>
            </div>
        </div>
    </div>
</div>

{{-- ── Scripts ───────────────────────────────────────────────────────────── --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
    // ── Chart data from PHP ───────────────────────────────────────────────
    const trendLabels      = @json($trendLabels);
    const visitorTrendData = @json($visitorTrendData);
    const attendTrendData  = @json($attendTrendData);
    const peakHourLabels   = @json($peakHourLabels);
    const peakHourData     = @json($peakHourData);
    const purposeLabels    = @json($purposes->pluck('purpose'));
    const purposeData      = @json($purposes->pluck('total'));

    const gridColor = 'rgba(0,0,0,0.05)';
    const tickColor = '#9ca3af';
    const baseFont  = { family: "'Nunito', sans-serif", size: 12 };

    function makeLine(id, data, color) {
        new Chart(document.getElementById(id), {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{ data, borderColor: color, backgroundColor: color + '18',
                    borderWidth: 2.5, pointRadius: 4, pointHoverRadius: 6,
                    fill: true, tension: 0.35 }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { color: tickColor, font: baseFont } },
                    y: { grid: { color: gridColor }, ticks: { color: tickColor, font: baseFont, stepSize: 1, precision: 0 }, beginAtZero: true }
                }
            }
        });
    }

    makeLine('chartVisitorTrend',  visitorTrendData, '#ea580c');
    makeLine('chartAttendTrend',   attendTrendData,  '#16a34a');

    // Peak hours bar
    new Chart(document.getElementById('chartPeakHours'), {
        type: 'bar',
        data: {
            labels: peakHourLabels,
            datasets: [{
                data: peakHourData,
                backgroundColor: peakHourData.map(v => v === Math.max(...peakHourData) && v > 0 ? '#16181d' : '#e8edfb'),
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: tickColor, font: { ...baseFont, size: 11 } } },
                y: { grid: { color: gridColor }, ticks: { color: tickColor, font: baseFont, stepSize: 1, precision: 0 }, beginAtZero: true }
            }
        }
    });

    // Purpose doughnut
    @if($purposes->count())
    new Chart(document.getElementById('chartPurpose'), {
        type: 'doughnut',
        data: {
            labels: purposeLabels,
            datasets: [{
                data: purposeData,
                backgroundColor: ['#16181d','#ea580c','#16a34a','#3b5998','#f59e0b','#8b5cf6','#06b6d4','#ec4899'],
                borderWidth: 2, borderColor: '#fff', hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            cutout: '62%',
            plugins: {
                legend: { position: 'bottom', labels: { color: '#16181d', font: baseFont, boxWidth: 12, padding: 10 } }
            }
        }
    });
    @endif

    // ── Toast helper ─────────────────────────────────────────────────────
    function showErrorToast(message) {
        const existing = document.getElementById('vms-toast-client');
        if (existing) existing.remove();
        const toast = document.createElement('div');
        toast.id = 'vms-toast-client';
        toast.className = 'vms-toast toast-error';
        toast.innerHTML = `<div class="vms-toast-body"><div class="vms-toast-icon"><i class="bi bi-exclamation-triangle-fill"></i></div><div class="vms-toast-text">${message}</div><button class="vms-toast-close" onclick="this.closest('.vms-toast').classList.add('hide');setTimeout(()=>this.closest('.vms-toast').remove(),350)"><i class="bi bi-x-lg"></i></button></div>`;
        document.body.appendChild(toast);
        setTimeout(() => { if (toast.parentNode) { toast.classList.add('hide'); setTimeout(() => toast.remove(), 350); } }, 4000);
    }

    // ── Time input helpers ────────────────────────────────────────────────
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

    document.querySelectorAll('.checkout-time').forEach(input => formatTimeInput(input));

    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('show.bs.modal', function() {
            const input = this.querySelector('.checkout-time');
            if (!input) return;
            const now = new Date();
            input.value = String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0');
        });
    });

    document.querySelectorAll('.checkout-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const timeInput   = this.querySelector('.checkout-time');
            const hiddenInput = this.querySelector('.checkout-datetime');
            if (!isValidTime(timeInput.value)) { showErrorToast('Please enter a valid time in HH:MM format (e.g. 08:30, 14:00)'); return; }
            const today = new Date();
            const date  = today.getFullYear() + '-' + String(today.getMonth()+1).padStart(2,'0') + '-' + String(today.getDate()).padStart(2,'0');
            hiddenInput.value = date + 'T' + timeInput.value;
            this.submit();
        });
    });

    // ── Edit visit check-in ───────────────────────────────────────────────
    let editVisitId = null, editVisitDate = null;
    function openEditVisitCheckin(visitId, currentTime, currentDate) {
        editVisitId = visitId; editVisitDate = currentDate;
        document.getElementById('edit-visit-checkin-input').value = currentTime;
        new bootstrap.Modal(document.getElementById('editVisitCheckinModal')).show();
    }
    document.getElementById('confirm-edit-visit-checkin').addEventListener('click', function() {
        const timeVal = document.getElementById('edit-visit-checkin-input').value;
        if (!isValidTime(timeVal)) { showErrorToast('Please enter a valid time in HH:MM format'); return; }
        const form = document.getElementById('edit-visit-checkin-form');
        form.action = '/visit/' + editVisitId + '/update-checkin';
        document.getElementById('edit-visit-checkin-hidden').value = editVisitDate + 'T' + timeVal;
        form.submit();
    });
    formatTimeInput(document.getElementById('edit-visit-checkin-input'));

    // ── Cancel visit ──────────────────────────────────────────────────────
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
