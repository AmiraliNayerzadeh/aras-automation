<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductsExport;
use App\Http\Controllers\Controller;
use App\Models\Products\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ProductExportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:products.view'),
        ];
    }

    public function preview(Request $request): View
    {
        return view('exports.preview', [
            'title' => __('products.title_index'),
            'contentView' => 'exports.products._table',
            'contentData' => ['products' => $this->products($request)],
            'withHeader' => $request->boolean('with_header', true),
            'withFooter' => $request->boolean('with_footer', true),
            'withDate' => $request->boolean('with_date', true),
            'withUser' => $request->boolean('with_user', true),
            'pdfUrl' => route('admin.products.export.pdf', $request->query()),
            'excelUrl' => route('admin.products.export.excel', $request->query()),
        ]);
    }

    public function pdf(Request $request): Response
    {
        $pdf = Pdf::loadView('exports.pdf', [
            'title' => __('products.title_index'),
            'contentView' => 'exports.products._table',
            'contentData' => ['products' => $this->products($request)],
            'withHeader' => $request->boolean('with_header', true),
            'withFooter' => $request->boolean('with_footer', true),
            'withDate' => $request->boolean('with_date', true),
            'withUser' => $request->boolean('with_user', true),
        ]);

        return $pdf->download('products.pdf');
    }

    public function excel(Request $request)
    {
        return Excel::download(
            new ProductsExport(ProductController::filtersFromRequest($request)),
            'products.xlsx'
        );
    }

    protected function products(Request $request)
    {
        return Product::query()
            ->with(['category', 'brand'])
            ->filter(ProductController::filtersFromRequest($request))
            ->orderBy('title')
            ->get();
    }
}
