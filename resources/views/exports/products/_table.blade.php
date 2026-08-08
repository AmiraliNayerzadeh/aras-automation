<table class="table table-hover align-middle mb-0">
    <thead>
        <tr>
            <th>{{ __('products.field_sku') }}</th>
            <th>{{ __('products.field_title') }}</th>
            <th>{{ __('products.field_category') }}</th>
            <th>{{ __('products.field_brand') }}</th>
            <th>{{ __('products.field_price') }}</th>
            <th>{{ __('products.field_stock') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($products as $product)
            <tr>
                <td>{{ $product->sku }}</td>
                <td>{{ $product->title }}</td>
                <td>{{ $product->category?->title ?? '—' }}</td>
                <td>{{ $product->brand?->title ?? '—' }}</td>
                <td>{{ $product->price !== null ? number_format((float) $product->price, 2) : '—' }}</td>
                <td>{{ rtrim(rtrim(number_format((float) ($product->stock_balances_sum_quantity ?? 0), 2), '0'), '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="6">{{ __('exports.no_records') }}</td></tr>
        @endforelse
    </tbody>
</table>
