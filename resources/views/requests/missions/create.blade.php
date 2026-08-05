<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('missions.title_new_request') }}</h2>
    </x-slot>

    <div class="card radius-12">
        <div class="card-body">
            <form method="POST" action="{{ route('mission-requests.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <x-input-label for="lookup_value_id" :value="__('missions.field_mission_type')" />
                        <select id="lookup_value_id" name="lookup_value_id" class="form-select radius-8 mt-1" required>
                            <option value="">—</option>
                            @foreach ($missionTypes as $type)
                                <option value="{{ $type->id }}" @selected(old('lookup_value_id') == $type->id)>
                                    {{ $type->label[app()->getLocale()] ?? $type->code }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('lookup_value_id')" class="mt-1" />
                    </div>

                    <div class="col-md-6">
                        <x-input-label for="destination" :value="__('missions.field_destination')" />
                        <x-text-input id="destination" name="destination" class="mt-1 w-100" :value="old('destination')" required />
                        <x-input-error :messages="$errors->get('destination')" class="mt-1" />
                    </div>

                    <div class="col-md-6">
                        <x-input-label for="from_date" :value="__('missions.field_from_date')" />
                        <x-text-input id="from_date" name="from_date" type="date" class="mt-1 w-100" :value="old('from_date')" required />
                        <x-input-error :messages="$errors->get('from_date')" class="mt-1" />
                    </div>

                    <div class="col-md-6">
                        <x-input-label for="to_date" :value="__('missions.field_to_date')" />
                        <x-text-input id="to_date" name="to_date" type="date" class="mt-1 w-100" :value="old('to_date')" required />
                        <x-input-error :messages="$errors->get('to_date')" class="mt-1" />
                    </div>

                    <div class="col-12">
                        <x-input-label for="purpose" :value="__('missions.field_purpose')" />
                        <textarea id="purpose" name="purpose" rows="3" class="form-control radius-8 mt-1" required>{{ old('purpose') }}</textarea>
                        <x-input-error :messages="$errors->get('purpose')" class="mt-1" />
                    </div>

                    <div class="col-md-6">
                        <x-input-label for="outbound_transport" :value="__('missions.field_outbound_transport')" />
                        <x-text-input id="outbound_transport" name="outbound_transport" class="mt-1 w-100" :value="old('outbound_transport')" />
                        <x-input-error :messages="$errors->get('outbound_transport')" class="mt-1" />
                    </div>

                    <div class="col-md-6">
                        <x-input-label for="return_transport" :value="__('missions.field_return_transport')" />
                        <x-text-input id="return_transport" name="return_transport" class="mt-1 w-100" :value="old('return_transport')" />
                        <x-input-error :messages="$errors->get('return_transport')" class="mt-1" />
                    </div>

                    <div class="col-md-6">
                        <x-input-label for="estimated_cost" :value="__('missions.field_estimated_cost')" />
                        <x-text-input id="estimated_cost" name="estimated_cost" type="number" step="0.01" min="0" class="mt-1 w-100" :value="old('estimated_cost')" />
                        <x-input-error :messages="$errors->get('estimated_cost')" class="mt-1" />
                    </div>

                    <div class="col-md-6">
                        <x-input-label for="currency" :value="__('missions.field_currency')" />
                        <x-text-input id="currency" name="currency" class="mt-1 w-100" maxlength="3" placeholder="USD" :value="old('currency')" />
                        <x-input-error :messages="$errors->get('currency')" class="mt-1" />
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <x-primary-button>
                        <i class="ri-send-plane-line"></i> {{ __('missions.action_submit') }}
                    </x-primary-button>
                    <a href="{{ route('mission-requests.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
