<?php

namespace App\Exports\Attendance;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * The group workbook's first sheet: one row per employee, each name linking to
 * that employee's own sheet.
 */
class AttendanceSummarySheet extends AttendanceSheet
{
    private const HEADER_ROW = 4;

    private const ROW_HEIGHT_POINTS = 42;

    /**
     * @param  Collection<int, array<string, mixed>>  $summary
     * @param  array<int, string>  $employeeSheets  Sheet title per user id.
     */
    public function __construct(
        string $sheetTitle,
        AttendancePhotos $photos,
        private Collection $summary,
        private array $employeeSheets,
        private string $period,
    ) {
        parent::__construct($sheetTitle, $photos);
    }

    protected function build(Worksheet $sheet): void
    {
        $widths = ['A' => 9, 'B' => 26, 'C' => 12, 'D' => 18, 'E' => 9, 'F' => 9, 'G' => 9, 'H' => 9, 'I' => 9, 'J' => 11, 'K' => 11, 'L' => 11, 'M' => 11];

        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->mergeCells('A1:M1');
        $sheet->mergeCells('A2:M2');
        $this->text($sheet, 'A1', __('app.title_attendance_report'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $this->text($sheet, 'A2', $this->period);
        $sheet->getStyle('A2')->getFont()->getColor()->setRGB(self::MUTED);

        $headings = [
            'A' => 'app.attendance_excel_photo', 'B' => 'app.field_employee', 'C' => 'app.field_employee_number',
            'D' => 'app.field_department', 'E' => 'app.status_present', 'F' => 'app.status_absent',
            'G' => 'app.status_on_leave', 'H' => 'app.status_remote', 'I' => 'app.status_incomplete',
            'J' => 'app.field_total_hours', 'K' => 'app.field_avg_daily_hours', 'L' => 'app.field_overtime',
            'M' => 'app.field_shortfall',
        ];

        foreach ($headings as $column => $label) {
            $this->text($sheet, $column.self::HEADER_ROW, __($label));
        }

        $this->styleHeader($sheet, 'A'.self::HEADER_ROW.':M'.self::HEADER_ROW);
        $sheet->getRowDimension(self::HEADER_ROW)->setRowHeight(32);

        $row = self::HEADER_ROW;

        foreach ($this->summary as $entry) {
            $row++;
            $user = $entry['user'];

            $sheet->getRowDimension($row)->setRowHeight(self::ROW_HEIGHT_POINTS);
            $this->photo($sheet, $user, "A{$row}", 48, 5);

            $this->text($sheet, "B{$row}", $user->name);
            $this->link($sheet, "B{$row}", $this->employeeSheets[$user->id]);
            $this->text($sheet, "C{$row}", $user->employee_number);
            $this->text($sheet, "D{$row}", $user->department?->name);

            foreach (['E' => 'present_days', 'F' => 'absent_days', 'G' => 'on_leave_days', 'H' => 'remote_days', 'I' => 'incomplete_days'] as $column => $key) {
                $sheet->setCellValue("{$column}{$row}", $entry[$key]);
            }

            $this->duration($sheet, "J{$row}", $entry['total_minutes']);
            $this->duration($sheet, "K{$row}", $entry['avg_minutes']);
            $this->duration($sheet, "L{$row}", $entry['total_overtime_minutes']);
            $this->duration($sheet, "M{$row}", $entry['total_shortfall_minutes']);
        }

        $total = $row + 1;
        $sheet->mergeCells("A{$total}:D{$total}");
        $this->text($sheet, "A{$total}", __('app.attendance_excel_total'));

        foreach (['E' => 'present_days', 'F' => 'absent_days', 'G' => 'on_leave_days', 'H' => 'remote_days', 'I' => 'incomplete_days'] as $column => $key) {
            $sheet->setCellValue("{$column}{$total}", $this->summary->sum($key));
        }

        $this->duration($sheet, "J{$total}", $this->summary->sum('total_minutes'));
        $this->duration($sheet, "K{$total}", null);
        $this->duration($sheet, "L{$total}", $this->summary->sum('total_overtime_minutes'));
        $this->duration($sheet, "M{$total}", $this->summary->sum('total_shortfall_minutes'));

        $this->styleBody($sheet, 'A'.(self::HEADER_ROW + 1).":M{$total}");
        $this->styleTotal($sheet, "A{$total}:M{$total}");

        $sheet->freezePane('C'.(self::HEADER_ROW + 1));
        $sheet->setAutoFilter('B'.self::HEADER_ROW.":M{$row}");
    }
}
