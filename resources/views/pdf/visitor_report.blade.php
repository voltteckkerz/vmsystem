<!DOCTYPE html>
<html>
<head>
    <title>Visitor Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header-table { width: 100%; margin-bottom: 10px; }
        .header-table td { vertical-align: middle; border: none; padding: 0; }
        .header-table .logo { width: 60px; }
        .header-table .logo img { max-height: 50px; }
        .header-table .info { text-align: right; }
        .header-table .info h2 { margin: 0 0 4px 0; }
        .header-table .info p { margin: 0; }
        table.report { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table.report th, table.report td { border: 1px solid #000; padding: 8px; text-align: left; }
        table.report th { background-color: #f2f2f2; }
        .signature { margin-top: 50px; }
        .signature p { margin: 0; }
        .signature .label { font-weight: bold; margin-bottom: 40px; }
        .signature .line { border-bottom: 1px dotted #000; width: 200px; margin-bottom: 4px; }
        .signature .caption { font-size: 11px; color: #555; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="logo">
                <img src="{{ public_path('images/lembahsari.jpg') }}" alt="Lembahsari">
            </td>
            <td class="info">
                <h2>Visitor Report</h2>
                <p>From: {{ \Carbon\Carbon::parse($from_date)->format('d/m/Y') }} &mdash; To: {{ \Carbon\Carbon::parse($to_date)->format('d/m/Y') }}</p>
                <p>Printed by: {{ $printed_by }}</p>
            </td>
        </tr>
    </table>
    <table class="report">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Date</th>
                <th>Company</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Pass</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                    <td>{{ $row->company }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->time_in)->format('h:i A') }}</td>
                    <td>{{ $row->time_out ? \Carbon\Carbon::parse($row->time_out)->format('h:i A') : '-' }}</td>
                    <td>
                        @php
                            $pass = $row->pass_id ? \App\Models\Pass::find($row->pass_id) : null;
                        @endphp
                        {{ $pass->pass_number ?? '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature">
        <p class="label">Checked by</p>
        <div class="line"></div>
        <p class="caption">Caption</p>
    </div>
</body>
</html>
