@php($category = $category ?? null)

<div class="row g-3">
    <div class="col-md-6">
        <x-input-label for="title" :value="__('products.field_title')" />
        <x-text-input id="title" name="title" class="mt-1 w-100" :value="old('title', $category?->title)" required autofocus />
        <x-input-error :messages="$errors->get('title')" class="mt-1" />
    </div>

    <div class="col-md-6">
        <x-input-label for="parent_id" :value="__('products.field_parent_category')" />
        <select id="parent_id" name="parent_id" class="form-select mt-1">
            <option value="">{{ __('products.no_parent') }}</option>
            @foreach ($categories as $option)
                <option value="{{ $option->id }}" @selected(old('parent_id', $category?->parent_id) == $option->id)>{{ $option->title }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('parent_id')" class="mt-1" />
    </div>

    <div class="col-md-3">
        <x-input-label for="sort_order" :value="__('app.field_sort_order')" />
        <x-text-input id="sort_order" name="sort_order" type="number" class="mt-1 w-100" :value="old('sort_order', $category?->sort_order ?? 0)" />
        <x-input-error :messages="$errors->get('sort_order')" class="mt-1" />
    </div>

    <div class="col-12">
        <x-input-label for="description" :value="__('products.field_description')" />
        <textarea id="description" name="description" rows="3" class="form-control mt-1">{{ old('description', $category?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-1" />
    </div>

    <div class="col-12 form-check">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $category?->is_active ?? true))>
        <label for="is_active" class="form-check-label">{{ __('app.field_active') }}</label>
    </div>
</div>

<div class="mt-24 d-flex gap-2">
    <x-primary-button>{{ __('app.save') }}</x-primary-button>
    <a href="{{ route('admin.product-categories.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.cancel') }}</a>
</div>
