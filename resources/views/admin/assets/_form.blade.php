@php($asset = $asset ?? null)

<div class="row g-3">
    <div class="col-12">
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="d-flex align-items-center justify-content-center bg-neutral-100 radius-8" style="width: 64px; height: 64px;">
                @if ($asset?->image_url)
                    <img src="{{ $asset->image_url }}" alt="" class="w-100 h-100 object-fit-cover radius-8">
                @else
                    <i class="ri-archive-2-line text-3xl text-neutral-400"></i>
                @endif
            </div>
            <div class="flex-grow-1">
                <x-input-label for="image" :value="__('assets.field_image')" />
                <input id="image" name="image" type="file" accept="image/*" class="form-control radius-8 mt-1">
                <x-input-error :messages="$errors->get('image')" class="mt-1" />
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <x-input-label for="title" :value="__('assets.field_title')" />
        <x-text-input id="title" name="title" class="mt-1 w-100" :value="old('title', $asset?->title)" required autofocus />
        <x-input-error :messages="$errors->get('title')" class="mt-1" />
    </div>

    <div class="col-md-6">
        <x-input-label for="category_id" :value="__('assets.field_category')" />
        <select id="category_id" name="category_id" class="form-select radius-8 mt-1">
            <option value="">—</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $asset?->category_id) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('category_id')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="serial_number" :value="__('assets.field_serial_number')" />
        <x-text-input id="serial_number" name="serial_number" class="mt-1 w-100" :value="old('serial_number', $asset?->serial_number)" />
        <x-input-error :messages="$errors->get('serial_number')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="purchase_date" :value="__('assets.field_purchase_date')" />
        <x-text-input id="purchase_date" name="purchase_date" type="date" class="mt-1 w-100" :value="old('purchase_date', $asset?->purchase_date?->toDateString())" />
        <x-input-error :messages="$errors->get('purchase_date')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="purchase_price" :value="__('assets.field_purchase_price')" />
        <x-text-input id="purchase_price" name="purchase_price" type="number" step="0.01" min="0" class="mt-1 w-100" :value="old('purchase_price', $asset?->purchase_price)" />
        <x-input-error :messages="$errors->get('purchase_price')" class="mt-1" />
    </div>

    <div class="col-md-6">
        <x-input-label for="status" :value="__('assets.field_status')" />
        <select id="status" name="status" class="form-select radius-8 mt-1" required>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $asset?->status ?? 'in_storage') === $status)>{{ __('assets.status_'.$status) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-1" />
    </div>

    <div class="col-12">
        <x-input-label for="description" :value="__('assets.field_description')" />
        <textarea id="description" name="description" rows="3" class="form-control mt-1">{{ old('description', $asset?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-1" />
    </div>
</div>

<div class="mt-24 d-flex gap-2">
    <x-primary-button>{{ __('app.save') }}</x-primary-button>
    <a href="{{ route('admin.assets.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.cancel') }}</a>
</div>
