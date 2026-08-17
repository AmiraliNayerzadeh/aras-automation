<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>Attendance Report — {{ $date->toDateString() }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        .header { margin-bottom: 16px; }
        .header h1 { font-size: 18px; margin: 0 0 4px; color: #1a1a1a; }
        .header .subtitle { font-size: 12px; color: #666; }
        .summary { margin-bottom: 12px; font-size: 11px; color: #444; }
        .summary span { display: inline-block; margin-inline-end: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e0e0e0; padding: 6px 8px; text-align: left; vertical-align: middle; }
        th { background-color: #2b3674; color: #fff; font-size: 10px; text-transform: uppercase; }
        tr:nth-child(even) td { background-color: #f7f8fc; }
        .avatar { width: 28px; height: 28px; border-radius: 50%; object-fit: cover; }
        .avatar-placeholder { width: 28px; height: 28px; border-radius: 50%; background-color: #d7dbf5; color: #2b3674; text-align: center; line-height: 28px; font-weight: bold; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold; }
        .badge-present { background-color: #e3fbe3; color: #1b7a1b; }
        .badge-overtime { background-color: #e3fbe3; color: #1b7a1b; }
        .badge-shortfall { background-color: #fdeaea; color: #b42318; }
        .badge-absent { background-color: #fdeaea; color: #b42318; }
        .badge-incomplete { background-color: #fff4e0; color: #93670c; }
        .badge-remote { background-color: #e6efff; color: #1849a9; }
        .badge-on_leave { background-color: #e6f7ff; color: #0b6b8f; }
        .footer { margin-top: 16px; font-size: 9px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Aras Automation — Nightly Attendance Report</h1>
        <div class="subtitle">{{ $date->format('l, Y-m-d') }}</div>
    </div>

    <div class="summary">
        <span><strong>{{ $rows->count() }}</strong> employees</span>
        <span><strong>{{ $rows->where('status', 'present')->count() }}</strong> present</span>
        <span><strong>{{ $rows->where('status', 'absent')->count() }}</strong> absent</span>
        <span><strong>{{ $rows->where('status', 'remote')->count() }}</strong> remote</span>
        <span><strong>{{ $rows->where('status', 'on_leave')->count() }}</strong> on leave</span>
        <span><strong>{{ $rows->where('status', 'incomplete')->count() }}</strong> incomplete</span>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px"></th>
                <th>Employee</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Worked</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>
                        @if ($row['photo_data_uri'])
                            <img src="{{ $row['photo_data_uri'] }}" class="avatar" alt="">
                        @else
                            <div class="avatar-placeholder">{{ mb_substr($row['user']->name, 0, 1) }}</div>
                        @endif
                    </td>
                    <td><strong>{{ $row['user']->name }}</strong></td>
                    <td>{{ $row['check_in']?->format('H:i:s') ?? '—' }}</td>
                    <td>{{ $row['check_out']?->format('H:i:s') ?? '—' }}</td>
                    <td>
                        @if ($row['worked_minutes'] !== null)
                            {{ sprintf('%dh %02dm', intdiv($row['worked_minutes'], 60), $row['worked_minutes'] % 60) }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if ($row['status'] === 'present' && $row['overtime_minutes'] > 0)
                            <span class="badge badge-overtime">Overtime +{{ number_format($row['overtime_minutes'] / 60, 1) }}h</span>
                        @elseif ($row['status'] === 'present' && $row['shortfall_minutes'] > 0)
                            <span class="badge badge-shortfall">Shortfall -{{ number_format($row['shortfall_minutes'] / 60, 1) }}h</span>
                        @elseif ($row['status'] === 'present')
                            <span class="badge badge-present">On time</span>
                        @elseif ($row['status'] === 'on_leave')
                            <span class="badge badge-on_leave">On Leave</span>
                        @elseif ($row['status'] === 'remote')
                            <span class="badge badge-remote">Remote</span>
                        @elseif ($row['status'] === 'incomplete')
                            <span class="badge badge-incomplete">Incomplete</span>
                        @else
                            <span class="badge badge-absent">Absent</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Generated automatically by Aras Automation at {{ now()->format('Y-m-d H:i') }}</div>
</body>
</html>
