<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class EmployeeSeeder extends Seeder
{
    /**
     * @var array<int, string>
     */
    protected array $employees = [
        1 => 'MAHDI HOSSEINI',
        2 => 'ALIREZA AHMADPOUR',
        3 => 'MORTEZA HOKMABADI',
        4 => 'HASMIK',
        5 => 'LUSINE AGHAMALIAN',
        6 => 'ARUSIK VIRABYAN',
        7 => 'ANI AVETISIYAN',
        8 => 'LILIT HAKHINYAN',
        9 => 'MERY ISAKHANYAN',
        10 => 'ASTXIK HOVHANNISSYAN',
        11 => 'ASHOT PASHINYAN',
        12 => 'VAHAG GHAZARYAN',
        13 => 'MILENI SHAHBAZIAN',
        14 => 'ARTUR IVANYAN',
        15 => 'ELENA RAHIMI',
        16 => 'NAZELI RATOOS',
        17 => 'AMIRALI NAYERZADEH',
        18 => 'YALDA KARIMI',
    ];

    public function run(): void
    {
        foreach ($this->employees as $code => $name) {
            $user = User::updateOrCreate(
                ['employee_number' => (string) $code],
                [
                    'name' => $name,
                    'email' => "emp{$code}@aras-automation.local",
                    'password' => 'Aras@1234',
                    'status' => 'active',
                    'locale' => 'en',
                ]
            );

            $photoPath = $this->storePhoto($code);

            if ($photoPath) {
                $user->update(['profile_photo_path' => $photoPath]);
            }

            if (! $user->email_verified_at) {
                $user->forceFill(['email_verified_at' => Carbon::now()])->save();
            }

            if (! $user->hasRole('employee')) {
                $user->assignRole('employee');
            }
        }
    }

    protected function storePhoto(int $code): ?string
    {
        $source = database_path("seeders/images/employees/{$code}.jpg");

        if (! is_file($source)) {
            return null;
        }

        $path = "employees/{$code}.jpg";

        Storage::disk('public')->put($path, file_get_contents($source));

        return $path;
    }
}
