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
    .analytics-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.07);
        padding: 24px;
        margin-bottom: 20px;
    }
    .kpi-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.07);
        padding: 20px 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        height: 100%;
    }
    .kpi-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }
    .kpi-icon.orange  { background: #fff0e0; color: #ea580c; }
    .kpi-icon.green   { background: #e2f8eb; color: #16a34a; }
    .kpi-icon.blue    { background: #e8edfb; color: #3b5998; }
    .kpi-icon.dark    { background: #f1f1ef; color: #16181d; }
    .kpi-value {
        font-size: 2rem;
        font-weight: 700;
        color: #16181d;
        line-height: 1;
        margin-bottom: 4px;
    }
    .kpi-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #6b6f78;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .kpi-sub {
        font-size: 0.78rem;
        color: #b0b4bc;
        margin-top: 2px;
    }
    .section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #16181d;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-title i { color: #6b6f78; }
    .month-picker-wrap {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.07);
        padding: 16px 24px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .month-picker-wrap label {
        font-weight: 600;
        font-size: 0.88rem;
        color: #16181d;
        margin: 0;
    }
    .month-picker-wrap input[type="month"] {
        border: 1px solid #e6e7eb;
        border-radius: 10px;
        padding: 8px 14px;
        font-size: 0.88rem;
        background: #fcfcfd;
        color: #16181d;
    }
    .btn-apply {
        background: #16181d;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 8px 20px;
        font-weight: 600;
        font-size: 0.88rem;
        cursor: pointer;
        transition: background 0.15s;
    }
    .btn-apply:hover { background: #2a2d36; }
    .chart-wrap { position: relative; }
    .freq-table th {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6b6f78;
        border-bottom: 2px solid #f0f1f3;
        padding: 8px 12px;
    }
    .freq-table td {
        font-size: 0.88rem;
        color: #16181d;
        padding: 10px 12px;
        border-bottom: 1px solid #f5f5f5;
        vertical-align: middle;
    }
    .freq-table tr:last-child td { border-bottom: none; }
    .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 700;
        background: #f1f1ef;
        color: #16181d;
    }
    .rank-badge.gold   { background: #fef9c3; color: #a16207; }
    .rank-badge.silver { background: #f1f5f9; color: #475569; }
    .rank-badge.bronze { background: #fff7ed; color: #c2410c; }
    .visit-pill {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        border-radius: 20px;
        background: #fff0e0;
        color: #ea580c;
        font-size: 0.78rem;
        font-weight: 700;
    }
    .no-data {
        text-align: center;
        color: #b0b4bc;
        font-size: 0.88rem;
        padding: 32px 0;
    }
</style>

<div class="container pb-4">

    {{-- Month Picker --}}
    <form method="GET" action="{{ route('analytics.index') }}">
        <div class="month-picker-wrap">
            <i class="bi bi-bar-chart-fill" style="font-size:1.2rem;color:#16181d;"></i>
            <span style="font-weight:700;font-size:1.05rem;color:#16181d;">Analytics</span>
            <div class="ms-auto d-flex align-items-center gap-2 flex-wrap">
                <label for="month">Month</label>
                <input type="month" id="month" name="month" value="{{ $month }}">
                <button type="submit" class="btn-apply">Apply</button>
            </div>
        </div>
    </form>

    {{-- KPI Row --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon orange"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="kpi-value">{{ number_format($totalVisitorsMonth) }}</div>
                    <div class="kpi-label">Visitors This Month</div>
                    <div class="kpi-sub">Today: {{ $totalVisitorsToday }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon green"><i class="bi bi-person-check-fill"></i></div>
                <div>
                    <div class="kpi-value">{{ number_format($totalAttendanceMonth) }}</div>
                    <div class="kpi-label">Attendance This Month</div>
                    <div class="kpi-sub">Today: {{ $totalAttendanceToday }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon blue"><i class="bi bi-clock-history"></i></div>
                <div>
                    @if($avgDurationMinutes !== null)
                        <div class="kpi-value">
                            @if($avgDurationMinutes >= 60)
                                {{ floor($avgDurationMinutes / 60) }}<span style="font-size:1rem;">h</span>{{ $avgDurationMinutes % 60 }}<span style="font-size:1rem;">m</span>
                            @else
                                {{ $avgDurationMinutes }}<span style="font-size:1rem;">m</span>
                            @endif
                        </div>
                    @else
                        <div class="kpi-value" style="font-size:1.2rem;color:#b0b4bc;">—</div>
                    @endif
                    <div class="kpi-label">Avg Visit Duration</div>
                    <div class="kpi-sub">Checked-out visits only</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon dark"><i class="bi bi-building"></i></div>
                <div>
                    <div class="kpi-value">{{ $topCompanies->count() }}</div>
                    <div class="kpi-label">Unique Companies</div>
                    <div class="kpi-sub">This month</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Daily Trend Charts --}}
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="analytics-card">
                <div class="section-title"><i class="bi bi-graph-up"></i> Daily Visitor Trend</div>
                <div class="chart-wrap">
                    <canvas id="chartDailyVisitor" height="180"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="analytics-card">
                <div class="section-title"><i class="bi bi-graph-up-arrow"></i> Daily Attendance Trend</div>
                <div class="chart-wrap">
                    <canvas id="chartDailyAttendance" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Peak Hours + Purpose --}}
    <div class="row g-3 mb-3">
        <div class="col-md-7">
            <div class="analytics-card">
                <div class="section-title"><i class="bi bi-clock"></i> Peak Visitor Hours</div>
                <div class="chart-wrap">
                    <canvas id="chartPeakHours" height="180"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="analytics-card">
                <div class="section-title"><i class="bi bi-pie-chart"></i> Visit Purpose Breakdown</div>
                @if($purposes->count())
                    <div class="chart-wrap" style="max-height:220px;display:flex;justify-content:center;">
                        <canvas id="chartPurpose" style="max-height:220px;"></canvas>
                    </div>
                @else
                    <div class="no-data"><i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px;"></i>No data for this month</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Top Companies + Frequent Visitors --}}
    <div class="row g-3">
        <div class="col-md-5">
            <div class="analytics-card">
                <div class="section-title"><i class="bi bi-building"></i> Top Visiting Companies</div>
                @if($topCompanies->count())
                    <div class="chart-wrap">
                        <canvas id="chartCompanies" height="220"></canvas>
                    </div>
                @else
                    <div class="no-data"><i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px;"></i>No data for this month</div>
                @endif
            </div>
        </div>
        <div class="col-md-7">
            <div class="analytics-card">
                <div class="section-title"><i class="bi bi-person-lines-fill"></i> Most Frequent Visitors</div>
                @if($frequentVisitors->count())
                    <table class="freq-table w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Company</th>
                                <th>Visits</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($frequentVisitors as $i => $v)
                            <tr>
                                <td>
                                    <span class="rank-badge {{ $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : '')) }}">
                                        {{ $i + 1 }}
                                    </span>
                                </td>
                                <td style="font-weight:600;">{{ $v->vname }}</td>
                                <td style="color:#6b6f78;">{{ $v->vcompany }}</td>
                                <td><span class="visit-pill">{{ $v->visit_count }}x</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="no-data"><i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px;"></i>No data for this month</div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
    const dayLabels    = @json($dayLabels);
    const visitorData  = @json($dailyVisitorData);
    const attendData   = @json($dailyAttendanceData);
    const peakLabels   = @json($peakHourLabels);
    const peakData     = @json($peakHourData);
    const purposeLabels = @json($purposes->pluck('purpose'));
    const purposeData   = @json($purposes->pluck('total'));
    const companyLabels = @json($topCompanies->pluck('company'));
    const companyData   = @json($topCompanies->pluck('total'));

    const gridColor  = 'rgba(0,0,0,0.05)';
    const tickColor  = '#9ca3af';
    const baseFont   = { family: "'Nunito', sans-serif", size: 12 };

    function lineDefaults(label, data, color) {
        return {
            type: 'line',
            data: {
                labels: dayLabels,
                datasets: [{
                    label,
                    data,
                    borderColor: color,
                    backgroundColor: color + '18',
                    borderWidth: 2.5,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    fill: true,
                    tension: 0.35,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { color: tickColor, font: baseFont } },
                    y: { grid: { color: gridColor }, ticks: { color: tickColor, font: baseFont, stepSize: 1, precision: 0 }, beginAtZero: true }
                }
            }
        };
    }

    // Daily visitor trend
    new Chart(document.getElementById('chartDailyVisitor'), lineDefaults('Visitors', visitorData, '#ea580c'));

    // Daily attendance trend
    new Chart(document.getElementById('chartDailyAttendance'), lineDefaults('Attendance', attendData, '#16a34a'));

    // Peak hours bar
    new Chart(document.getElementById('chartPeakHours'), {
        type: 'bar',
        data: {
            labels: peakLabels,
            datasets: [{
                label: 'Visitors',
                data: peakData,
                backgroundColor: peakData.map(v => v === Math.max(...peakData) ? '#16181d' : '#e8edfb'),
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
    const purposeColors = ['#16181d','#ea580c','#16a34a','#3b5998','#f59e0b','#8b5cf6','#06b6d4','#ec4899'];
    new Chart(document.getElementById('chartPurpose'), {
        type: 'doughnut',
        data: {
            labels: purposeLabels,
            datasets: [{
                data: purposeData,
                backgroundColor: purposeColors.slice(0, purposeLabels.length),
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            cutout: '62%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#16181d', font: baseFont, boxWidth: 12, padding: 12 }
                }
            }
        }
    });
    @endif

    // Top companies horizontal bar
    @if($topCompanies->count())
    new Chart(document.getElementById('chartCompanies'), {
        type: 'bar',
        data: {
            labels: companyLabels,
            datasets: [{
                label: 'Visitors',
                data: companyData,
                backgroundColor: '#ea580c22',
                borderColor: '#ea580c',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: tickColor, font: baseFont, stepSize: 1, precision: 0 }, beginAtZero: true },
                y: { grid: { display: false }, ticks: { color: '#16181d', font: { ...baseFont, weight: '600' } } }
            }
        }
    });
    @endif
</script>
@endsection
