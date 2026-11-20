<?php

namespace App\Http\Controllers\Merchandising;

use App\Enums\MerchandisingDivision;
use App\Exports\MerchandisingReportExport;
use App\Http\Controllers\Controller;
use App\Models\Merchandising\Store;
use App\Models\Merchandising\VisitItem;
use App\Models\User;
use App\Services\Merchandising\MerchandisingOptions;
use App\Services\Merchandising\MerchandisingReport;
use App\Support\ImageThumbnails;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Reports are in English whatever the viewer's language, as agreed.
 */
class ReportController extends Controller implements HasMiddleware
{
    /** A PDF past this many visits gets too slow and too large to be useful. */
    public const PDF_VISIT_LIMIT = 150;

    public static function middleware(): array
    {
        return [new Middleware('permission:merchandising.view_all')];
    }

    public function __construct(private readonly MerchandisingOptions $options) {}

    public function index(Request $request, MerchandisingReport $report): View
    {
        $this->validateFilters($request);
        $report->build($request);

        return view('merchandising.reports.index', $this->viewData($report) + [
            'merchandisers' => User::permission('merchandising.create')->orderBy('name')->get(['id', 'name']),
            'regions' => Store::whereNotNull('region')->distinct()->orderBy('region')->pluck('region'),
            'selectedStore' => $request->filled('store_id') ? Store::withTrashed()->find($request->input('store_id')) : null,
        ]);
    }

    public function pdf(Request $request, MerchandisingReport $report, ImageThumbnails $thumbnails): Response
    {
        $this->validateFilters($request);
        $report->build($request);
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        $pdf = Pdf::loadView('merchandising.reports.pdf', $this->viewData($report) + [
            'withPhotos' => $request->boolean('photos', true),
            'thumbnails' => $thumbnails,
            'visitLimit' => self::PDF_VISIT_LIMIT,
        ])->setPaper('a4');

        return $pdf->download('merchandising-'.$report->from->toDateString().($report->from->isSameDay($report->to) ? '' : '_'.$report->to->toDateString()).'.pdf');
    }

    public function excel(Request $request, MerchandisingReport $report): BinaryFileResponse
    {
        $this->validateFilters($request);
        $report->build($request);

        return Excel::download(
            new MerchandisingReportExport($report, $this->labels()),
            'merchandising-'.$report->from->toDateString().'_'.$report->to->toDateString().'.xlsx'
        );
    }

    /**
     * Also switches to English: controllers are built before the locale
     * middleware runs, so this can't live in the constructor.
     */
    private function validateFilters(Request $request): void
    {
        app()->setLocale('en');
        Carbon::setLocale('en');

        $request->validate([
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'division' => ['nullable', Rule::in(MerchandisingDivision::values())],
            'user_id' => ['nullable', 'integer'],
            'store_id' => ['nullable', 'integer'],
            'store_type' => ['nullable', Rule::in(Store::TYPES)],
            'region' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', Rule::in(['unverified'])],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(MerchandisingReport $report): array
    {
        $labels = $this->labels();

        return [
            'report' => $report,
            'kpis' => $report->kpis(),
            'labels' => $labels,
            'stock' => $report->distribution('stock_status', VisitItem::STOCK_STATUSES),
            'shelf' => $report->distribution('shelf_position', VisitItem::SHELF_POSITIONS),
            'priceTags' => $report->distribution('price_tag', VisitItem::PRICE_TAGS),
            'issueCounts' => $report->tally('issues'),
            'actionCounts' => $report->tally('actions', onVisits: true),
            'feedbackCounts' => $report->tally('store_feedback', onVisits: true),
            'perDay' => $report->visitsPerDay(),
            'topOut' => $report->topOutOfStock(),
            'byMerchandiser' => $report->byMerchandiser(),
            'divisionLabel' => isset($report->filters['division']) ? MerchandisingDivision::from($report->filters['division'])->label() : 'All divisions',
        ];
    }

    /**
     * English labels for every code a report can show.
     *
     * @return array<string, array<string, string>>
     */
    private function labels(): array
    {
        return [
            'issues' => $this->options->lookup('merch_issue', 'en', activeOnly: false),
            'actions' => $this->options->lookup('merch_action', 'en', activeOnly: false),
            'feedback' => $this->options->lookup('merch_feedback', 'en', activeOnly: false),
            'stock' => collect(VisitItem::STOCK_STATUSES)->mapWithKeys(fn ($k) => [$k => __('merchandising.stock_'.$k)])->all(),
            'shelf' => collect(VisitItem::SHELF_POSITIONS)->mapWithKeys(fn ($k) => [$k => __('merchandising.shelf_'.$k)])->all(),
            'price_tag' => collect(VisitItem::PRICE_TAGS)->mapWithKeys(fn ($k) => [$k => __('merchandising.price_tag_'.$k)])->all(),
        ];
    }
}
