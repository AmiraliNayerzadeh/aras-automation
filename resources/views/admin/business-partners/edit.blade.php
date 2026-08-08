<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('business-partners.title_edit') }} — {{ $businessPartner->name }}</h2>
    </x-slot>

    <div class="card radius-12 mb-24">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.business-partners.update', $businessPartner) }}">
                @csrf
                @method('put')
                @include('admin.business-partners._form')
            </form>
        </div>
    </div>

    <div class="card radius-12 mb-24">
        <div class="card-header bg-body">{{ __('business-partners.contacts_title') }}</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('business-partners.field_contact_name') }}</th>
                        <th>{{ __('business-partners.field_position') }}</th>
                        <th>{{ __('app.field_phone') }}</th>
                        <th>{{ __('app.field_email') }}</th>
                        <th>{{ __('business-partners.field_is_primary') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($businessPartner->contacts as $contact)
                        <tr>
                            <td>{{ $contact->name }}</td>
                            <td>{{ $contact->position ?? '—' }}</td>
                            <td>{{ $contact->phone ?? '—' }}</td>
                            <td>{{ $contact->email ?? '—' }}</td>
                            <td>
                                @if ($contact->is_primary)
                                    <span class="badge text-sm fw-semibold px-16 py-6 radius-4 text-success-600 bg-success-100">{{ __('business-partners.field_is_primary') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('business-partners.no_contacts') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card radius-12">
        <div class="card-header bg-body">{{ __('app.create_new') }}</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.business-partners.contacts.store', $businessPartner) }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <x-input-label for="contact_name" :value="__('business-partners.field_contact_name')" />
                        <x-text-input id="contact_name" name="name" class="mt-1 w-100" required />
                    </div>
                    <div class="col-md-2">
                        <x-input-label for="position" :value="__('business-partners.field_position')" />
                        <x-text-input id="position" name="position" class="mt-1 w-100" />
                    </div>
                    <div class="col-md-2">
                        <x-input-label for="contact_phone" :value="__('app.field_phone')" />
                        <x-text-input id="contact_phone" name="phone" class="mt-1 w-100" />
                    </div>
                    <div class="col-md-3">
                        <x-input-label for="contact_email" :value="__('app.field_email')" />
                        <x-text-input id="contact_email" name="email" type="email" class="mt-1 w-100" />
                    </div>
                    <div class="col-md-2 d-flex align-items-end form-check ps-3">
                        <div>
                            <input type="checkbox" id="is_primary" name="is_primary" value="1" class="form-check-input">
                            <label for="is_primary" class="form-check-label">{{ __('business-partners.field_is_primary') }}</label>
                        </div>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                <div class="mt-3">
                    <x-primary-button>{{ __('app.save') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-24">
        <a href="{{ route('admin.business-partners.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.back') }}</a>
    </div>
</x-app-layout>
