<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 fw-semibold mb-0">{{ __('files.title_activity_log') }}</h2>
            <a href="{{ route('files.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">
                <i class="ri-arrow-go-back-line"></i> {{ __('app.back') }}
            </a>
        </div>
    </x-slot>

    <div class="card radius-12 mb-24">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <x-input-label for="event" :value="__('files.field_event')" />
                    <select id="event" name="event" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        @foreach ($events as $eventOption)
                            <option value="{{ $eventOption }}" @selected(request('event') === $eventOption)>{{ __('files.event_'.$eventOption) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <x-input-label for="causer_id" :value="__('files.field_causer')" />
                    <select id="causer_id" name="causer_id" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(request('causer_id') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <x-input-label for="subject_type" :value="__('files.field_subject')" />
                    <select id="subject_type" name="subject_type" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        <option value="folder" @selected(request('subject_type') === 'folder')>{{ __('files.field_folder_name') }}</option>
                        <option value="file" @selected(request('subject_type') === 'file')>{{ __('files.field_file') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <x-input-label for="date_from" :value="__('orders.field_date_from')" />
                    <x-text-input id="date_from" name="date_from" type="date" class="mt-1 w-100" :value="request('date_from')" />
                </div>
                <div class="col-md-2">
                    <x-input-label for="date_to" :value="__('orders.field_date_to')" />
                    <x-text-input id="date_to" name="date_to" type="date" class="mt-1 w-100" :value="request('date_to')" />
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary-600 radius-8 w-100 py-8">
                        <i class="ri-filter-3-line"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @php
        $eventBadge = fn (?string $event) => match ($event) {
            'created' => 'text-success-600 bg-success-100',
            'deleted' => 'text-danger-600 bg-danger-100',
            'restored' => 'text-info-600 bg-info-100',
            'shared' => 'text-primary-600 bg-primary-50',
            'unshared' => 'text-warning-600 bg-warning-100',
            default => 'text-neutral-600 bg-neutral-200',
        };
    @endphp

    <div class="card radius-12">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('files.field_when') }}</th>
                        <th>{{ __('files.field_causer') }}</th>
                        <th>{{ __('files.field_event') }}</th>
                        <th>{{ __('files.field_subject') }}</th>
                        <th>{{ __('files.field_details') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $activity)
                        @php
                            $attrs = $activity->properties['attributes'] ?? [];
                            $subjectLabel = $activity->subject
                                ? ($activity->subject_type === 'folder' ? $activity->subject->name : ($activity->subject->title ?: $activity->subject->original_name))
                                : ($attrs['name'] ?? $attrs['title'] ?? $attrs['original_name'] ?? __('files.deleted_item'));
                        @endphp
                        <tr>
                            <td class="text-nowrap">{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                            <td>
                                @if ($activity->causer)
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $activity->causer->avatar_url }}" alt="" class="w-24-px h-24-px rounded-circle object-fit-cover">
                                        {{ $activity->causer->name }}
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <span class="badge text-sm fw-semibold px-16 py-6 radius-4 {{ $eventBadge($activity->event) }}">
                                    {{ __('files.event_'.$activity->event) }}
                                </span>
                            </td>
                            <td>{{ $subjectLabel }}</td>
                            <td class="text-secondary-light text-sm">{{ $activity->description }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-body">
            {{ $activities->links() }}
        </div>
    </div>
</x-app-layout>
