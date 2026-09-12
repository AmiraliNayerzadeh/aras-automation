<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>Organization Chart</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        .header { margin-bottom: 16px; }
        .header h1 { font-size: 18px; margin: 0 0 4px; }
        .header .subtitle { font-size: 11px; color: #666; }
        .dept-box { border: 1px solid #d0d3e0; border-radius: 4px; padding: 8px 10px; margin-bottom: 8px; background-color: #f7f8fc; }
        .dept-header { border-left: 4px solid #4318ff; padding-left: 8px; }
        .dept-name { font-weight: bold; font-size: 12px; color: #2b3674; }
        .dept-code { color: #888; font-size: 9px; }
        .members { margin-top: 8px; }
        .member-card { display: inline-block; width: 130px; vertical-align: top; margin: 0 10px 10px 0; padding: 6px; border: 1px solid #e3e6f0; border-radius: 6px; background-color: #ffffff; text-align: center; }
        .member-photo { width: 46px; height: 46px; border-radius: 50%; border: 2px solid #4318ff; margin-bottom: 4px; }
        .member-name { display: block; font-weight: bold; font-size: 9.5px; color: #1b2559; }
        .member-title { display: block; font-size: 8.5px; color: #707eae; margin-top: 2px; }
        .member-accent { display: block; width: 26px; height: 3px; background-color: #4318ff; margin: 4px auto 0; border-radius: 2px; }
        .children { margin-top: 8px; margin-left: 18px; border-left: 2px solid #e0e3f0; padding-left: 10px; }
        .empty { color: #999; font-style: italic; padding: 20px 0; }
        .section-title { font-size: 14px; font-weight: bold; color: #2b3674; margin: 0 0 12px; }
        .page-break { page-break-before: always; }

        /* Graphical tree (table-based, since DomPDF cannot render JS) */
        table.oc2-tree { border-collapse: collapse; margin: 0 auto; }
        table.oc2-tree td { text-align: center; vertical-align: top; padding: 0 8px; }
        .oc2-node-cell { padding: 0 8px 0; }
        .oc2-node { display: inline-block; min-width: 100px; padding: 8px 10px 6px; border-radius: 8px; background-color: #ffffff; border: 1px solid #e3e6f0; }
        .oc2-node-root { background-color: #1b2559; border-color: #1b2559; }
        .oc2-node-department { background-color: #2b3674; border-color: #2b3674; }
        .oc2-node-root .oc2-name, .oc2-node-root .oc2-meta,
        .oc2-node-department .oc2-name, .oc2-node-department .oc2-meta { color: #ffffff; }
        .oc2-avatar { width: 40px; height: 40px; border-radius: 50%; border: 2px solid #4318ff; margin-bottom: 4px; }
        .oc2-name { display: block; font-weight: bold; font-size: 9.5px; color: #1b2559; white-space: nowrap; }
        .oc2-meta { display: block; font-size: 8px; color: #707eae; margin-top: 2px; white-space: nowrap; }
        .oc2-node-root .oc2-meta, .oc2-node-department .oc2-meta { color: #c9cee8; }
        .oc2-accent { display: block; width: 22px; height: 2px; background-color: #05cd99; margin: 4px auto 0; border-radius: 2px; }
        .oc2-stem { height: 12px; }
        .oc2-stem div { width: 0; height: 12px; border-left: 2px solid #c8ccdb; margin: 0 auto; }
        .oc2-stem-short { height: 12px; }
        .oc2-h { height: 0; }
        .oc2-h-line { border-top: 2px solid #c8ccdb; }
        .oc2-child { padding-top: 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Organization Chart — {{ config('app.name') }}</h1>
        <div class="subtitle">Generated {{ now()->format('Y-m-d H:i') }}</div>
    </div>

    @if ($roots->isEmpty())
        <p class="empty">No departments defined yet.</p>
    @else
        <div class="section-title">Chart View</div>
        <div style="overflow-x: auto;">
            @include('exports._org-chart-pdf-tree', ['node' => $treeData])
        </div>

        <div class="page-break"></div>

        <div class="section-title">Detailed List</div>
        @foreach ($roots as $department)
            @include('exports._org-chart-pdf-node', ['department' => $department, 'all' => $all, 'photos' => $photos, 'defaultPhoto' => $defaultPhoto])
        @endforeach
    @endif
</body>
</html>
