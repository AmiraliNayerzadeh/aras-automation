<?php

use App\Models\Hr\AttendanceCorrection;
use App\Models\Hr\FaceDeviceEvent;
use App\Models\Organization\Branch;
use App\Models\Organization\Company;
use App\Models\Organization\Department;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Carbon::setTestNow('2026-11-02 18:00:00');
    Storage::fake('public');

    Permission::findOrCreate('face-device-events.view');

    $this->viewer = User::factory()->create(['status' => 'active', 'name' => 'Viewer']);
    $this->viewer->givePermissionTo('face-device-events.view');

    $this->withPhoto = function (string $name, array $attributes = []) {
        $path = UploadedFile::fake()->image('photo.jpg', 900, 600)->store('avatars', 'public');

        return User::factory()->create($attributes + ['status' => 'active', 'name' => $name, 'profile_photo_path' => $path]);
    };

    $this->worked = fn (User $user, string $date, string $in = '08:00:00', string $out = '17:30:00') => collect([$in, $out])->each(
        fn ($time) => FaceDeviceEvent::create(['user_id' => $user->id, 'employee_no' => (string) $user->id, 'event_time' => "{$date} {$time}"])
    );

    $this->download = function (array $query): Spreadsheet {
        $response = $this->actingAs($this->viewer)->get(route('admin.attendance-report.excel', $query + [
            'date_from' => '2026-10-26',
            'date_to' => '2026-10-28',
        ]));

        $response->assertOk();

        return IOFactory::load($response->baseResponse->getFile()->getPathname());
    };
});

afterEach(fn () => Carbon::setTestNow());

test('the report needs the attendance permission', function () {
    $this->actingAs(User::factory()->create(['status' => 'active']))
        ->get(route('admin.attendance-report.excel'))
        ->assertForbidden();
});

test('an employee report is one sheet with photo, details, totals and every day', function () {
    $employee = ($this->withPhoto)('Sara', ['employee_number' => 'E-7']);
    ($this->worked)($employee, '2026-10-27');

    $book = ($this->download)(['user_id' => $employee->id]);
    $sheet = $book->getSheet(0);

    expect($book->getSheetCount())->toBe(1)
        ->and($sheet->getTitle())->toBe('Sara')
        ->and($sheet->getCell('B1')->getValue())->toBe('Sara')
        ->and($sheet->getCell('B2')->getValue())->toContain('E-7')
        ->and($sheet->getDrawingCollection())->toHaveCount(1)
        ->and($sheet->getCell('A6')->getValue())->toBe(1)
        ->and($sheet->getCell('A9')->getValue())->toBe('2026-10-26')
        ->and($sheet->getCell('A10')->getValue())->toBe('2026-10-27')
        ->and($sheet->getCell('A11')->getValue())->toBe('2026-10-28')
        ->and($sheet->getCell('D10')->getValue())->toBe('08:00')
        ->and($sheet->getCell('E10')->getValue())->toBe('17:30')
        ->and(round($sheet->getCell('F10')->getValue() * 1440))->toEqual(570.0)
        ->and($sheet->getStyle('F10')->getNumberFormat()->getFormatCode())->toBe('[h]:mm')
        ->and($sheet->getCell('A4')->getValue())->toBeNull();
});

test('photos are shrunk instead of embedded at full size', function () {
    $employee = ($this->withPhoto)('Sara');

    $drawing = ($this->download)(['user_id' => $employee->id])->getSheet(0)->getDrawingCollection()[0];
    [$width, $height] = getimagesize($drawing->getPath());

    expect($width)->toBe(160)->and($height)->toBe(160);
});

test('a corrected day carries only a written reason into the report', function () {
    $employee = ($this->withPhoto)('Sara');
    AttendanceCorrection::create([
        'user_id' => $employee->id, 'date' => '2026-10-27', 'reason' => 'Power cut',
        'check_in' => '2026-10-27 09:00:00', 'check_out' => '2026-10-27 17:00:00',
    ]);

    $sheet = ($this->download)(['user_id' => $employee->id])->getSheet(0);

    expect($sheet->getCell('D10')->getValue())->toBe('09:00')
        ->and($sheet->getCell('I10')->getValue())->toBe('Power cut');

    AttendanceCorrection::first()->update(['reason' => null]);

    $sheet = ($this->download)(['user_id' => $employee->id])->getSheet(0);

    expect($sheet->getCell('D10')->getValue())->toBe('09:00')
        ->and($sheet->getCell('I10')->getValue())->toBeNull();
});

test('a group report has a summary and a linked sheet for each person', function () {
    $sara = ($this->withPhoto)('Sara');
    $reza = ($this->withPhoto)('Reza');
    ($this->worked)($sara, '2026-10-27');

    $book = ($this->download)([]);
    $names = $book->getSheetNames();
    $summary = $book->getSheet(0);

    expect($names)->toContain('Sara', 'Reza', 'Viewer')
        ->and($names[0])->toBe(__('app.attendance_excel_summary_sheet'))
        ->and($summary->getCell('B5')->getValue())->toBe('Sara')
        ->and($summary->getCell('B5')->getHyperlink()->getUrl())->toBe("sheet://'Sara'!A1")
        ->and($summary->getDrawingCollection())->toHaveCount(3)
        ->and($book->getSheetByName('Reza')->getDrawingCollection())->toHaveCount(1)
        ->and($book->getSheetByName('Reza')->getCell('A4')->getHyperlink()->getUrl())->toContain($names[0]);
});

test('the group report follows the page filters', function () {
    $company = Company::forceCreate(['name' => 'Aras', 'code' => 'A']);
    $branch = Branch::forceCreate(['company_id' => $company->id, 'name' => 'Main', 'code' => 'M']);
    $department = Department::forceCreate(['name' => 'Sales', 'code' => 'S', 'branch_id' => $branch->id]);
    ($this->withPhoto)('Sara', ['department_id' => $department->id]);
    ($this->withPhoto)('Reza');

    expect(($this->download)(['department_id' => $department->id])->getSheetNames())
        ->toBe([__('app.attendance_excel_summary_sheet'), 'Sara']);
});

test('awkward names still make a valid workbook', function () {
    ($this->withPhoto)('Same Name');
    ($this->withPhoto)('same name');
    ($this->withPhoto)('=HYPERLINK("http://evil.test")');
    ($this->withPhoto)('A very long employee name that is well past thirty one characters');
    ($this->withPhoto)('Slash/Back\\Star*Quote\'Q?');

    $book = ($this->download)([]);
    $names = $book->getSheetNames();

    expect(collect($names)->map(fn ($n) => mb_strtolower($n))->unique()->count())->toBe(count($names))
        ->and(collect($names)->every(fn ($n) => mb_strlen($n) <= 31 && ! preg_match('/[\\\\\/?*\[\]:]/', $n)))->toBeTrue();

    $row = collect(range(5, 12))->first(fn ($r) => str_starts_with((string) $book->getSheet(0)->getCell("B{$r}")->getValue(), '=HYPERLINK'));

    expect($book->getSheet(0)->getCell("B{$row}")->getDataType())->toBe(DataType::TYPE_STRING);
});

test('an employee without a photo gets the default picture', function () {
    $employee = User::factory()->create(['status' => 'active', 'name' => 'NoPhoto']);

    expect(($this->download)(['user_id' => $employee->id])->getSheet(0)->getDrawingCollection())->toHaveCount(1);
});

test('a broken photo does not break the report', function () {
    Storage::disk('public')->put('avatars/broken.jpg', 'not an image');
    $employee = User::factory()->create(['status' => 'active', 'name' => 'Broken', 'profile_photo_path' => 'avatars/broken.jpg']);

    expect(($this->download)(['user_id' => $employee->id])->getSheet(0)->getDrawingCollection())->toHaveCount(0);
});

test('the sheet reads right to left in Persian', function () {
    $employee = ($this->withPhoto)('Sara');

    $response = $this->actingAs($this->viewer)->withSession(['locale' => 'fa'])
        ->get(route('admin.attendance-report.excel', ['user_id' => $employee->id, 'date_from' => '2026-10-26', 'date_to' => '2026-10-26']));

    expect(IOFactory::load($response->baseResponse->getFile()->getPathname())->getSheet(0)->getRightToLeft())->toBeTrue();
});

test('an inverted date range is refused', function () {
    $this->actingAs($this->viewer)
        ->get(route('admin.attendance-report.excel', ['date_from' => '2026-10-28', 'date_to' => '2026-10-01']))
        ->assertSessionHasErrors('date_to');
});

test('an inactive or unknown employee has no report', function () {
    $this->actingAs($this->viewer)->get(route('admin.attendance-report.excel', ['user_id' => 999999]))->assertNotFound();
});

test('the report page offers the right download', function () {
    $employee = ($this->withPhoto)('Sara');

    $this->actingAs($this->viewer)->get(route('admin.attendance-report.index'))
        ->assertOk()->assertSee(__('app.attendance_excel_group'));

    $this->actingAs($this->viewer)->get(route('admin.attendance-report.index', ['user_id' => $employee->id]))
        ->assertOk()->assertDontSee(__('app.attendance_excel_group'))->assertSee(__('app.attendance_excel_employee'));
});
