@php($product = $product ?? null)

<div class="row g-3">
    <div class="col-md-8">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="bg-neutral-100 radius-8 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 90px; height: 90px;">
                @if ($product?->image_url)
                    <img src="{{ $product->image_url }}" alt="" class="w-100 h-100 object-fit-cover radius-8">
                @else
                    <i class="ri-image-2-line text-3xl text-neutral-400"></i>
                @endif
            </div>
            <div class="flex-grow-1">
                <x-input-label for="image" :value="__('products.field_image')" />
                <input id="image" name="image" type="file" accept="image/*" class="form-control radius-8 mt-1">
                <x-input-error :messages="$errors->get('image')" class="mt-1" />
            </div>
        </div>
    </div>

    <div class="col-md-4">
        @if ($product?->barcode)
            <x-input-label :value="__('products.field_barcode')" />
            <div class="border radius-8 p-8 mt-1 bg-white" style="max-width: 100%; overflow: hidden;">
                {!! \App\Support\Barcode::svg($product->barcode, 1.5, 40) !!}
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <x-input-label for="sku" :value="__('products.field_sku')" />
        <x-text-input id="sku" name="sku" class="mt-1 w-100" :value="old('sku', $product?->sku)" required />
        <x-input-error :messages="$errors->get('sku')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="barcode" :value="__('products.field_barcode')" />
        <x-text-input id="barcode" name="barcode" class="mt-1 w-100" :value="old('barcode', $product?->barcode)" />
        <x-input-error :messages="$errors->get('barcode')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="unit" :value="__('products.field_unit')" />
        <x-text-input id="unit" name="unit" class="mt-1 w-100" :value="old('unit', $product?->unit)" />
        <x-input-error :messages="$errors->get('unit')" class="mt-1" />
    </div>

    <div class="col-md-6">
        <x-input-label for="title" :value="__('products.field_title')" />
        <x-text-input id="title" name="title" class="mt-1 w-100" :value="old('title', $product?->title)" required autofocus />
        <x-input-error :messages="$errors->get('title')" class="mt-1" />
    </div>

    <div class="col-md-6">
        <x-input-label for="subtitle" :value="__('products.field_subtitle')" />
        <x-text-input id="subtitle" name="subtitle" class="mt-1 w-100" :value="old('subtitle', $product?->subtitle)" />
        <x-input-error :messages="$errors->get('subtitle')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="category_id" :value="__('products.field_category')" />
        <select id="category_id" name="category_id" class="form-select mt-1">
            <option value="">—</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product?->category_id) == $category->id)>{{ $category->title }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('category_id')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="brand_id" :value="__('products.field_brand')" />
        <select id="brand_id" name="brand_id" class="form-select mt-1">
            <option value="">—</option>
            @foreach ($brands as $brand)
                <option value="{{ $brand->id }}" @selected(old('brand_id', $product?->brand_id) == $brand->id)>{{ $brand->title }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('brand_id')" class="mt-1" />
    </div>

    <div class="col-md-2">
        <x-input-label for="package_quantity" :value="__('products.field_package_quantity')" />
        <x-text-input id="package_quantity" name="package_quantity" type="number" class="mt-1 w-100" :value="old('package_quantity', $product?->package_quantity)" />
        <x-input-error :messages="$errors->get('package_quantity')" class="mt-1" />
    </div>

    <div class="col-md-2">
        <x-input-label for="price" :value="__('products.field_price')" />
        <x-text-input id="price" name="price" type="number" step="0.01" class="mt-1 w-100" :value="old('price', $product?->price)" />
        <x-input-error :messages="$errors->get('price')" class="mt-1" />
    </div>

    <div class="col-12">
        <x-input-label for="description" :value="__('products.field_description')" />
        <textarea id="description" name="description" rows="3" class="form-control mt-1">{{ old('description', $product?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-1" />
    </div>

    <div class="col-12 form-check">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $product?->is_active ?? true))>
        <label for="is_active" class="form-check-label">{{ __('app.field_active') }}</label>
    </div>
</div>

<div class="mt-24 d-flex gap-2">
    <x-primary-button>{{ __('app.save') }}</x-primary-button>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.cancel') }}</a>
</div>
