<?php

namespace App\Exports\Attendance;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * One employee's period: photo and details, the totals, then one row per day.
 */
class AttendanceEmployeeSheet extends AttendanceSheet
{
    private const STATUS_COLORS = [
        'present' => 'DDF3E4',
        'absent' => 'FDE2E2',
        'on_leave' => 'DCEAFD',
        'remote' => 'EAE2FB',
        'day_off' => 'EBECEF',
        'incomplete' => 'FDEBD3',
    ];

    private const HEADER_ROW = 8;

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $totals  This employee's entry from AttendanceCalculator::summarize().
     */
    public function __construct(
        string $sheetTitle,
        AttendancePhotos $photos,
        private User $employee,
        private Collection $rows,
        private array $totals,
        private string $period,
        private ?string $summaryTitle,
    ) {
        parent::__construct($sheetTitle, $photos);
    }

    protected function build(Worksheet $sheet): void
    {
        foreach (['A' => 14, 'B' => 14, 'C' => 12, 'D' => 11, 'E' => 11, 'F' => 11, 'G' => 11, 'H' => 11, 'I' => 36] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $this->writeHead($sheet);
        $this->writeTotals($sheet);
        $lastRow = $this->writeDays($sheet);

        $sheet->freezePane('A'.(self::HEADER_ROW + 1));
        $sheet->setAutoFilter('A'.self::HEADER_ROW.':I'.$lastRow);
    }

    private function writeHead(Worksheet $sheet): void
    {
        $sheet->mergeCells('A1:A3');

        foreach ([1, 2, 3] as $row) {
            $sheet->mergeCells("B{$row}:I{$row}");
            $sheet->getRowDimension($row)->setRowHeight(24);
            $sheet->getStyle("B{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }

        $this->photo($sheet, $this->employee, 'A1', 90, 4);

        $this->text($sheet, 'B1', $this->employee->name);
        $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(14);

        $this->text($sheet, 'B2', collect([
            $this->employee->employee_number ? __('app.field_employee_number').': '.$this->employee->employee_number : null,
            $this->employee->department?->name,
        ])->filter()->implode('  ·  '));
        $this->text($sheet, 'B3', $this->period);
        $sheet->getStyle('B2:B3')->getFont()->getColor()->setRGB(self::MUTED);

        if ($this->summaryTitle) {
            $sheet->mergeCells('A4:C4');
            $this->text($sheet, 'A4', __('app.attendance_excel_back_to_summary'));
            $this->link($sheet, 'A4', $this->summaryTitle);
        }
    }

    private function writeTotals(Worksheet $sheet): void
    {
        $counts = [
            'A' => ['app.status_present', $this->totals['present_days']],
            'B' => ['app.status_absent', $this->totals['absent_days']],
            'C' => ['app.status_on_leave', $this->totals['on_leave_days']],
            'D' => ['app.status_remote', $this->totals['remote_days']],
            'E' => ['app.status_incomplete', $this->totals['incomplete_days']],
        ];

        foreach ($counts as $column => [$label, $value]) {
            $this->text($sheet, "{$column}5", __($label));
            $sheet->setCellValue("{$column}6", $value);
        }

        $durations = [
            'F' => ['app.field_total_hours', $this->totals['total_minutes']],
            'G' => ['app.field_overtime', $this->totals['total_overtime_minutes']],
            'H' => ['app.field_shortfall', $this->totals['total_shortfall_minutes']],
        ];

        foreach ($durations as $column => [$label, $minutes]) {
            $this->text($sheet, "{$column}5", __($label));
            $this->duration($sheet, "{$column}6", $minutes);
        }

        $this->styleBody($sheet, 'A5:H6');
        $sheet->getStyle('A5:H5')->getFont()->setBold(true);
        $sheet->getStyle('A5:H5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E4F1FF');
        $sheet->getStyle('A6:H6')->getFont()->setSize(12)->setBold(true);
        $sheet->getRowDimension(6)->setRowHeight(22);
    }

    private function writeDays(Worksheet $sheet): int
    {
        $headings = [
            'A' => 'app.log_date', 'B' => 'app.attendance_excel_weekday', 'C' => 'app.field_status',
            'D' => 'app.field_check_in', 'E' => 'app.field_check_out', 'F' => 'app.field_duration',
            'G' => 'app.field_overtime', 'H' => 'app.field_shortfall', 'I' => 'app.attendance_excel_note',
        ];

        foreach ($headings as $column => $label) {
            $this->text($sheet, $column.self::HEADER_ROW, __($label));
        }

        $this->styleHeader($sheet, 'A'.self::HEADER_ROW.':I'.self::HEADER_ROW);
        $sheet->getRowDimension(self::HEADER_ROW)->setRowHeight(24);

        $row = self::HEADER_ROW;

        foreach ($this->rows as $day) {
            $row++;

            $this->text($sheet, "A{$row}", $day['date']);
            $this->text($sheet, "B{$row}", Carbon::parse($day['date'])->translatedFormat('l'));
            $this->text($sheet, "C{$row}", __('app.status_'.$day['status']));
            $this->text($sheet, "D{$row}", $day['check_in']?->format('H:i') ?? '—');
            $this->text($sheet, "E{$row}", $day['check_out']?->format('H:i') ?? '—');
            $this->duration($sheet, "F{$row}", $day['worked_minutes']);
            $this->duration($sheet, "G{$row}", $day['overtime_minutes'] ?: null);
            $this->duration($sheet, "H{$row}", $day['shortfall_minutes'] ?: null);

            if ($day['correction']?->reason) {
                $this->text($sheet, "I{$row}", $day['correction']->reason);
            }

            $sheet->getStyle("C{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB(self::STATUS_COLORS[$day['status']] ?? 'FFFFFF');
        }

        $this->styleBody($sheet, 'A'.(self::HEADER_ROW + 1).":I{$row}");
        $sheet->getStyle('I'.(self::HEADER_ROW + 1).":I{$row}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_GENERAL)->setWrapText(true);

        return $row;
    }
}
