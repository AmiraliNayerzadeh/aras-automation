@php($children = $all->where('parent_id', $department->id)->values())

<div class="dept-box">
    <div class="dept-header">
        <span class="dept-name">{{ $department->name }}</span>
        <span class="dept-code">({{ $department->code }})</span>
    </div>

    @if ($department->members->isNotEmpty())
        <div class="members">
            @foreach ($department->members as $member)
                <div class="member-card">
                    <img class="member-photo" src="{{ $photos[$member->id] ?? $defaultPhoto }}" alt="">
                    <span class="member-name">{{ $member->name }}</span>
                    <span class="member-title">{{ $member->position?->title ?? __('org_chart.employee') }}</span>
                    <span class="member-accent"></span>
                </div>
            @endforeach
        </div>
    @endif

    @if ($children->isNotEmpty())
        <div class="children">
            @foreach ($children as $child)
                @include('exports._org-chart-pdf-node', ['department' => $child, 'all' => $all, 'photos' => $photos, 'defaultPhoto' => $defaultPhoto])
            @endforeach
        </div>
    @endif
</div>
