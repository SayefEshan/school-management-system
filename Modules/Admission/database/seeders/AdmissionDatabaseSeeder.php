<?php

namespace Modules\Admission\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Admission\Models\AcademicYear;
use Modules\Admission\Models\ClassModel;
use Modules\Admission\Models\Section;

class AdmissionDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAcademicYears();
        $this->seedClasses();
        $this->seedSections();
    }

    private function seedAcademicYears(): void
    {
        $years = [
            [
                'name' => '2025',
                'name_bn' => '২০২৫',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'is_current' => true,
            ],
            [
                'name' => '2026',
                'name_bn' => '২০২৬',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'is_current' => false,
            ],
        ];

        foreach ($years as $year) {
            AcademicYear::firstOrCreate(['name' => $year['name']], $year);
        }
    }

    private function seedClasses(): void
    {
        $classes = [
            ['name' => 'Class 6', 'name_bn' => 'ষষ্ঠ শ্রেণী', 'numeric_code' => '06', 'order' => 1],
            ['name' => 'Class 7', 'name_bn' => 'সপ্তম শ্রেণী', 'numeric_code' => '07', 'order' => 2],
            ['name' => 'Class 8', 'name_bn' => 'অষ্টম শ্রেণী', 'numeric_code' => '08', 'order' => 3],
            ['name' => 'Class 9', 'name_bn' => 'নবম শ্রেণী', 'numeric_code' => '09', 'order' => 4],
            ['name' => 'Class 10', 'name_bn' => 'দশম শ্রেণী', 'numeric_code' => '10', 'order' => 5],
        ];

        foreach ($classes as $class) {
            ClassModel::firstOrCreate(['numeric_code' => $class['numeric_code']], $class);
        }
    }

    private function seedSections(): void
    {
        $sections = [
            'A' => 'ক',
            'B' => 'খ',
            'C' => 'গ',
        ];

        $classes = ClassModel::all();

        foreach ($classes as $class) {
            foreach ($sections as $nameEn => $nameBn) {
                Section::firstOrCreate(
                    ['class_id' => $class->id, 'name' => $nameEn],
                    [
                        'class_id' => $class->id,
                        'name' => $nameEn,
                        'name_bn' => $nameBn,
                        'capacity' => 60,
                    ]
                );
            }
        }
    }
}
