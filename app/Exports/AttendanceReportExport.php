<?php

namespace App\Exports;

use App\Exports\Attendance\AttendanceEmployeeSheet;
use App\Exports\Attendance\AttendancePhotos;
use App\Exports\Attendance\AttendanceSummarySheet;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * An attendance workbook: for one employee just their own sheet; for a group a
 * linked summary sheet followed by every employee's own sheet.
 */
class AttendanceReportExport implements WithMultipleSheets
{
    private const SHEET_TITLE_LIMIT = 31;

    private AttendancePhotos $photos;

    /**
     * @param  Collection<int, array<string, mixed>>  $summary  From AttendanceCalculator::summarize().
     * @param  Collection<int, Collection<int, array<string, mixed>>>  $dailyByUser  Daily rows keyed by user id.
     */
    public function __construct(
        private Collection $summary,
        private Collection $dailyByUser,
        private bool $single,
        private Carbon $dateFrom,
        private Carbon $dateTo,
    ) {
        $this->photos = new AttendancePhotos;
    }

    public function sheets(): array
    {
        $period = __('app.attendance_excel_period', ['from' => $this->dateFrom->toDateString(), 'to' => $this->dateTo->toDateString()]);
        $summaryTitle = $this->single ? null : $this->sheetTitle(__('app.attendance_excel_summary_sheet'), []);
        $titles = $this->employeeSheetTitles($summaryTitle);

        $sheets = $this->summary->map(fn (array $entry) => new AttendanceEmployeeSheet(
            $titles[$entry['user']->id],
            $this->photos,
            $entry['user'],
            $this->dailyByUser[$entry['user']->id],
            $entry,
            $period,
            $summaryTitle,
        ))->all();

        if ($summaryTitle) {
            array_unshift($sheets, new AttendanceSummarySheet($summaryTitle, $this->photos, $this->summary, $titles, $period));
        }

        return $sheets;
    }

    /**
     * @return array<int, string>  Unique sheet title per user id.
     */
    private function employeeSheetTitles(?string $summaryTitle): array
    {
        $titles = [];

        foreach ($this->summary as $entry) {
            /** @var User $user */
            $user = $entry['user'];
            $titles[$user->id] = $this->sheetTitle($user->name, array_filter([...array_values($titles), $summaryTitle]));
        }

        return $titles;
    }

    /**
     * Excel rejects these characters, more than 31 characters, and duplicates
     * ignoring case; two employees with the same name are common enough.
     *
     * @param  array<int, string>  $taken
     */
    private function sheetTitle(string $name, array $taken): string
    {
        $clean = trim(preg_replace('/[\\\\\/?*\[\]:\']+/u', ' ', $name)) ?: '-';
        $title = mb_substr($clean, 0, self::SHEET_TITLE_LIMIT);
        $taken = array_map('mb_strtolower', $taken);

        for ($n = 2; in_array(mb_strtolower($title), $taken, true); $n++) {
            $suffix = " ({$n})";
            $title = mb_substr($clean, 0, self::SHEET_TITLE_LIMIT - mb_strlen($suffix)).$suffix;
        }

        return $title;
    }
}
