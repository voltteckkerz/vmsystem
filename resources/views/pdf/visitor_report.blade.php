<!DOCTYPE html>
<html>
<head>
    <title>Visitor Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Visitor Report ({{ \Carbon\Carbon::parse($date)->format('d/m/Y') }})</h2>
    <table>
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
</body>
</html>
