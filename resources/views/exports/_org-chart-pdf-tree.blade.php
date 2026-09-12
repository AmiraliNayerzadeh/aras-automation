@php
    $children = collect($node['children'] ?? []);
    $count = max($children->count(), 1);
    $span = $children->count() * 2;
@endphp
<table class="oc2-tree" cellspacing="0" cellpadding="0">
    <tr>
        <td colspan="{{ $children->isNotEmpty() ? $span : 1 }}" class="oc2-node-cell">
            <div class="oc2-node oc2-node-{{ $node['type'] }}">
                @if ($node['type'] === 'person')
                    <img class="oc2-avatar" src="{{ $node['image'] }}" alt="">
                @endif
                <span class="oc2-name">{{ $node['name'] }}</span>
                @if (! empty($node['meta']))
                    <span class="oc2-meta">{{ $node['meta'] }}</span>
                @endif
                <span class="oc2-accent"></span>
            </div>
        </td>
    </tr>

    @if ($children->isNotEmpty())
        <tr>
            <td colspan="{{ $span }}" class="oc2-stem"><div></div></td>
        </tr>
        <tr>
            @foreach ($children as $i => $child)
                <td class="oc2-h {{ $i === 0 ? '' : 'oc2-h-line' }}"></td>
                <td class="oc2-h {{ $i === $children->count() - 1 ? '' : 'oc2-h-line' }}"></td>
            @endforeach
        </tr>
        <tr>
            @foreach ($children as $child)
                <td colspan="2" class="oc2-child">
                    <div class="oc2-stem oc2-stem-short"><div></div></div>
                    @include('exports._org-chart-pdf-tree', ['node' => $child])
                </td>
            @endforeach
        </tr>
    @endif
</table>
