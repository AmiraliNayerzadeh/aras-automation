<?php

namespace App\Exports;

use App\Models\Merchandising\VisitItem;
use App\Services\Merchandising\MerchandisingReport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Visits, one row per product check, and the summary figures, in English.
 */
class MerchandisingReportExport implements WithMultipleSheets
{
    /**
     * @param  array<string, array<string, string>>  $labels
     */
    public function __construct(private MerchandisingReport $report, private array $labels) {}

    public function sheets(): array
    {
        $label = fn (string $group, ?string $code) => $code ? ($this->labels[$group][$code] ?? $code) : '';
        $list = fn (string $group, ?array $codes) => collect($codes ?? [])->map(fn ($c) => $label($group, $c))->implode(', ');

        $summary = [['Period', $this->report->periodLabel()]];

        foreach ($this->report->kpis() as $key => $value) {
            $summary[] = [ucfirst(str_replace('_', ' ', $key)), $value ?? '—'];
        }

        $visits = [['Date', 'Time', 'Merchandiser', 'Store', 'Store codes', 'Region', 'Products checked', 'Out of stock', 'Actions', 'Store feedback', 'Order needed', 'Note', 'Photos']];

        foreach ($this->report->visits as $visit) {
            $items = $this->report->itemsOf($visit);
            $visits[] = [
                $visit->visited_at->format('Y-m-d'), $visit->visited_at->format('H:i'), $visit->user?->name,
                $visit->store?->name, $visit->store?->codes(), $visit->store?->region,
                $items->count(), $items->whereIn('stock_status', MerchandisingReport::OUT_STATUSES)->count(),
                $list('actions', $visit->actions), $list('feedback', $visit->store_feedback),
                $visit->needs_order ? 'Yes' : 'No', $visit->note, $visit->media->count(),
            ];
        }

        $items = [['Date', 'Merchandiser', 'Store', 'Product code', 'Product', 'Line', 'Division', 'Stock', 'Shelf position', 'Price tag', 'Issues', 'Order']];

        foreach ($this->report->items as $item) {
            /** @var VisitItem $item */
            $visit = $item->visit;
            $items[] = [
                $visit->visited_at->format('Y-m-d H:i'), $visit->user?->name, $visit->store?->name,
                $item->product?->sku, $item->displayName(), $item->product?->line, strtoupper((string) $item->division),
                $label('stock', $item->stock_status), $label('shelf', $item->shelf_position), $label('price_tag', $item->price_tag),
                $list('issues', $item->issues), $item->needs_order ? 'Yes' : '',
            ];
        }

        return [
            $this->sheet('Summary', $summary, false),
            $this->sheet('Visits', $visits),
            $this->sheet('Product checks', $items),
        ];
    }

    /**
     * @param  list<list<mixed>>  $rows
     */
    private function sheet(string $title, array $rows, bool $header = true): object
    {
        return new class($title, $rows, $header) implements FromArray, ShouldAutoSize, WithEvents, WithStrictNullComparison, WithTitle
        {
            public function __construct(private string $sheetTitle, private array $rows, private bool $header) {}

            public function array(): array
            {
                return [];
            }

            public function title(): string
            {
                return $this->sheetTitle;
            }

            public function registerEvents(): array
            {
                return [AfterSheet::class => function (AfterSheet $event) {
                    $sheet = $event->sheet->getDelegate();

                    // Written cell by cell as explicit strings/numbers, so a
                    // store name starting with "=" can't become a formula.
                    foreach ($this->rows as $r => $row) {
                        foreach (array_values($row) as $c => $value) {
                            $cell = [$c + 1, $r + 1];
                            is_int($value) || is_float($value)
                                ? $sheet->setCellValue($cell, $value)
                                : $sheet->setCellValueExplicit($cell, (string) $value, DataType::TYPE_STRING);
                        }
                    }

                    if ($this->header && $this->rows) {
                        $range = 'A1:'.$sheet->getHighestColumn().'1';
                        $sheet->getStyle($range)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
                        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('487FFF');
                        $sheet->freezePane('A2');
                        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
                    }

                    foreach ($sheet->getColumnIterator() as $column) {
                        $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
                    }
                }];
            }
        };
    }
}
