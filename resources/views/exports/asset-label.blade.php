<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>Asset Labels</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 0; }
        table.grid { width: 100%; border-collapse: collapse; }
        table.grid td { width: 33.33%; padding: 6px; vertical-align: top; }
        .label {
            border: 1px dashed #999;
            border-radius: 4px;
            padding: 8px 10px;
            text-align: center;
            height: 110px;
        }
        .label .company { font-size: 8px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; }
        .label .title { font-size: 11px; font-weight: bold; margin: 2px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .label .code { font-size: 9px; color: #444; margin-bottom: 4px; }
        .label .barcode { margin-top: 2px; }
    </style>
</head>
<body>
    <table class="grid">
        @foreach ($assets->chunk(3) as $row)
            <tr>
                @foreach ($row as $asset)
                    <td>
                        <div class="label">
                            <div class="company">Aras Automation</div>
                            <div class="title">{{ $asset->title }}</div>
                            <div class="code">{{ $asset->asset_code }}</div>
                            <div class="barcode">{!! \App\Support\Barcode::svg($asset->asset_code, 1.3, 32) !!}</div>
                        </div>
                    </td>
                @endforeach
                @for ($i = $row->count(); $i < 3; $i++)
                    <td></td>
                @endfor
            </tr>
        @endforeach
    </table>
</body>
</html>
