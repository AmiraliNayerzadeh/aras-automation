<?php

namespace App\Exports\Attendance;

use App\Models\User;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Shared look of the attendance workbook's sheets. Sheets are laid out cell by
 * cell because they carry photos and header blocks; text always goes in as an
 * explicit string, so a name starting with "=" can't run as a formula.
 */
abstract class AttendanceSheet implements WithEvents, WithTitle
{
    protected const PRIMARY = '487FFF';

    protected const BORDER = 'D1D5DB';

    protected const MUTED = '6B7280';

    /** Stored as a fraction of a day so Excel sums it natively, past 24 hours. */
    protected const DURATION_FORMAT = '[h]:mm';

    public function __construct(protected string $sheetTitle, protected AttendancePhotos $photos) {}

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Tahoma')->setSize(10);
                $sheet->setRightToLeft(app()->getLocale() === 'fa');
                $sheet->setShowGridlines(false);

                $this->build($sheet);
            },
        ];
    }

    abstract protected function build(Worksheet $sheet): void;

    protected function text(Worksheet $sheet, string $cell, ?string $value): void
    {
        $sheet->setCellValueExplicit($cell, (string) $value, DataType::TYPE_STRING);
    }

    protected function duration(Worksheet $sheet, string $cell, int|float|null $minutes): void
    {
        if ($minutes !== null) {
            $sheet->setCellValue($cell, $minutes / 1440);
        }

        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(self::DURATION_FORMAT);
    }

    protected function styleHeader(Worksheet $sheet, string $range): void
    {
        $style = $sheet->getStyle($range);
        $style->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::PRIMARY);
        $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
    }

    protected function styleBody(Worksheet $sheet, string $range): void
    {
        $style = $sheet->getStyle($range);
        $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::BORDER);
        $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
    }

    protected function styleTotal(Worksheet $sheet, string $range): void
    {
        $style = $sheet->getStyle($range);
        $style->getFont()->setBold(true);
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E4F1FF');
    }

    protected function link(Worksheet $sheet, string $cell, string $targetSheet): void
    {
        $sheet->getCell($cell)->getHyperlink()->setUrl("sheet://'".str_replace("'", "''", $targetSheet)."'!A1");
        $sheet->getStyle($cell)->getFont()->setUnderline(true)->getColor()->setRGB(self::PRIMARY);
    }

    protected function photo(Worksheet $sheet, User $user, string $cell, int $size, int $offset): void
    {
        $path = $this->photos->for($user);

        if (! $path) {
            return;
        }

        $drawing = new Drawing;
        $drawing->setName($user->name);
        $drawing->setPath($path);
        $drawing->setResizeProportional(false);
        $drawing->setWidth($size);
        $drawing->setHeight($size);
        $drawing->setCoordinates($cell);
        $drawing->setOffsetX($offset);
        $drawing->setOffsetY($offset);
        $drawing->setWorksheet($sheet);
    }
}
