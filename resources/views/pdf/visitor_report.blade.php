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
                <th>Company</th>
                <th>Time In</th>
                <th>Time Out</th>
            </tr>
        </thead>
        <tbody>
            @php $counter = 1; @endphp
            @foreach($data as $visit)
                @foreach($visit->visitors as $visitor)
                <tr>
                    <td>{{ $counter++ }}</td>
                    <td>{{ $visitor->name }}</td>
                    <td>{{ $visitor->company->name ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($visit->manual_check_in_time)->format('h:i A') }}</td>
                    <td>{{ $visit->manual_check_out_time ? \Carbon\Carbon::parse($visit->manual_check_out_time)->format('h:i A') : '-' }}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>
