<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('leaves.title_new_request') }}</h2>
    </x-slot>

    <div class="card radius-12">
        <div class="card-body">
            <form method="POST" action="{{ route('leave-requests.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <x-input-label for="lookup_value_id" :value="__('leaves.field_leave_type')" />
                        <select id="lookup_value_id" name="lookup_value_id" class="form-select radius-8 mt-1" required>
                            <option value="">—</option>
                            @foreach ($leaveTypes as $type)
                                <option value="{{ $type->id }}" @selected(old('lookup_value_id') == $type->id)>
                                    {{ $type->label[app()->getLocale()] ?? $type->code }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('lookup_value_id')" class="mt-1" />
                    </div>

                    <div class="col-md-6">
                        <x-input-label for="substitute_user_id" :value="__('leaves.field_substitute')" />
                        <select id="substitute_user_id" name="substitute_user_id" class="form-select radius-8 mt-1">
                            <option value="">—</option>
                            @foreach ($colleagues as $colleague)
                                @continue($colleague->id === auth()->id())
                                <option value="{{ $colleague->id }}" @selected(old('substitute_user_id') == $colleague->id)>{{ $colleague->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('substitute_user_id')" class="mt-1" />
                    </div>

                    <div class="col-md-4">
                        <x-input-label for="from_date" :value="__('leaves.field_from_date')" />
                        <x-text-input id="from_date" name="from_date" type="date" class="mt-1 w-100" :value="old('from_date')" required />
                        <x-input-error :messages="$errors->get('from_date')" class="mt-1" />
                    </div>

                    <div class="col-md-4">
                        <x-input-label for="to_date" :value="__('leaves.field_to_date')" />
                        <x-text-input id="to_date" name="to_date" type="date" class="mt-1 w-100" :value="old('to_date')" required />
                        <x-input-error :messages="$errors->get('to_date')" class="mt-1" />
                    </div>

                    <div class="col-md-2">
                        <x-input-label for="start_time" :value="__('leaves.field_start_time')" />
                        <x-text-input id="start_time" name="start_time" type="time" class="mt-1 w-100" :value="old('start_time')" />
                        <x-input-error :messages="$errors->get('start_time')" class="mt-1" />
                    </div>

                    <div class="col-md-2">
                        <x-input-label for="end_time" :value="__('leaves.field_end_time')" />
                        <x-text-input id="end_time" name="end_time" type="time" class="mt-1 w-100" :value="old('end_time')" />
                        <x-input-error :messages="$errors->get('end_time')" class="mt-1" />
                    </div>

                    <div class="col-12">
                        <x-input-label for="description" :value="__('leaves.field_description')" />
                        <textarea id="description" name="description" rows="3" class="form-control radius-8 mt-1">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-1" />
                    </div>

                    <div class="col-12">
                        <x-input-label for="attachment" :value="__('leaves.field_attachment')" />
                        <input id="attachment" name="attachment" type="file" class="form-control radius-8 mt-1">
                        <x-input-error :messages="$errors->get('attachment')" class="mt-1" />
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <x-primary-button>
                        <i class="ri-send-plane-line"></i> {{ __('leaves.action_submit') }}
                    </x-primary-button>
                    <a href="{{ route('leave-requests.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
