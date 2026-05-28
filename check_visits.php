$visits = \App\Models\Visit::with('visitors')->whereDate('created_at', '2026-05-26')->get();
echo "=== Visits from 26th May: " . $visits->count() . " ===\n";
foreach ($visits as $v) {
    $names = $v->visitors->pluck('name')->join(', ');
    echo "Visit #{$v->id} | Status: {$v->status} | Created: {$v->created_at} | Check-out: {$v->manual_check_out_time} | Visitors: {$names}\n";
}

echo "\n=== Visits showing on dashboard today ===\n";
$today = now()->toDateString();
$liveVisits = \App\Models\Visit::with('visitors')
    ->where(function ($q) use ($today) {
        $q->whereDate('created_at', $today);
    })
    ->orWhere(function ($q) {
        $q->where('status', 'active');
    })
    ->get();
echo "Total: " . $liveVisits->count() . "\n";
foreach ($liveVisits as $v) {
    $names = $v->visitors->pluck('name')->join(', ');
    echo "Visit #{$v->id} | Status: {$v->status} | Created: {$v->created_at} | Visitors: {$names}\n";
}
