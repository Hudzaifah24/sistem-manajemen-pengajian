<?php

namespace Database\Seeders;

use App\Models\Barcode;
use App\Models\Division;
use App\Models\Education;
use App\Models\JobTitle;
use App\Models\Shift;
use App\Models\User;
use Database\Factories\DivisionFactory;
use Database\Factories\EducationFactory;
use Database\Factories\JobTitleFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        (new AdminSeeder)->run();
        foreach (DivisionFactory::$divisions as $value) {
            if (Division::where('name', $value)->exists()) {
                continue;
            }
            Division::create(['name' => $value]);
        }
    }
}
