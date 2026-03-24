<?php

namespace Modules\Admission\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admission\Models\AcademicYear;
use Modules\Admission\Models\ClassModel;
use Modules\Admission\Models\Section;
use Tests\TestCase;

class AcademicStructureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_active' => true]);
        $this->token = $this->admin->createToken('test')->plainTextToken;
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_list_academic_years(): void
    {
        AcademicYear::create([
            'name' => '2025',
            'name_bn' => '২০২৫',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'is_current' => true,
        ]);

        $response = $this->getJson('/api/v1/academic/years', [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertOk()
            ->assertJsonPath('error', false)
            ->assertJsonPath('data.0.name', '2025')
            ->assertJsonPath('data.0.name_bn', '২০২৫')
            ->assertJsonPath('data.0.is_current', true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_academic_year(): void
    {
        $response = $this->postJson('/api/v1/academic/years', [
            'name' => '2026',
            'name_bn' => '২০২৬',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => false,
        ], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('error', false)
            ->assertJsonPath('data.name', '2026');

        $this->assertDatabaseHas('academic_years', ['name' => '2026']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_prevents_duplicate_academic_year_names(): void
    {
        AcademicYear::create([
            'name' => '2025',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
        ]);

        $response = $this->postJson('/api/v1/academic/years', [
            'name' => '2025',
            'start_date' => '2025-06-01',
            'end_date' => '2025-12-31',
        ], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertStatus(422);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setting_current_year_unsets_previous(): void
    {
        $year2025 = AcademicYear::create([
            'name' => '2025',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'is_current' => true,
        ]);

        $this->postJson('/api/v1/academic/years', [
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $this->assertFalse($year2025->fresh()->is_current);
        $this->assertDatabaseHas('academic_years', ['name' => '2026', 'is_current' => true]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_list_classes_with_sections(): void
    {
        $class = ClassModel::create([
            'name' => 'Class 6',
            'name_bn' => 'ষষ্ঠ শ্রেণী',
            'numeric_code' => '06',
            'order' => 1,
        ]);

        Section::create([
            'class_id' => $class->id,
            'name' => 'A',
            'name_bn' => 'ক',
            'capacity' => 60,
        ]);

        $response = $this->getJson('/api/v1/academic/classes', [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Class 6')
            ->assertJsonPath('data.0.name_bn', 'ষষ্ঠ শ্রেণী')
            ->assertJsonCount(1, 'data.0.sections');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_class(): void
    {
        $response = $this->postJson('/api/v1/academic/classes', [
            'name' => 'Class 7',
            'name_bn' => 'সপ্তম শ্রেণী',
            'numeric_code' => '07',
            'order' => 2,
        ], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Class 7')
            ->assertJsonPath('data.numeric_code', '07');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_prevents_duplicate_class_numeric_codes(): void
    {
        ClassModel::create([
            'name' => 'Class 6',
            'numeric_code' => '06',
            'order' => 1,
        ]);

        $response = $this->postJson('/api/v1/academic/classes', [
            'name' => 'Class Six',
            'numeric_code' => '06',
        ], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertStatus(422);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_list_sections_for_a_class(): void
    {
        $class = ClassModel::create([
            'name' => 'Class 6',
            'numeric_code' => '06',
            'order' => 1,
        ]);

        Section::create([
            'class_id' => $class->id,
            'name' => 'A',
            'name_bn' => 'ক',
            'capacity' => 60,
        ]);
        Section::create([
            'class_id' => $class->id,
            'name' => 'B',
            'name_bn' => 'খ',
            'capacity' => 60,
        ]);

        $response = $this->getJson("/api/v1/academic/classes/{$class->id}/sections", [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function it_can_create_section(): void
    {
        $class = ClassModel::create([
            'name' => 'Class 6',
            'numeric_code' => '06',
            'order' => 1,
        ]);

        $response = $this->postJson('/api/v1/academic/sections', [
            'class_id' => $class->id,
            'name' => 'C',
            'name_bn' => 'গ',
            'capacity' => 50,
        ], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'C');

        $this->assertDatabaseHas('sections', [
            'class_id' => $class->id,
            'name' => 'C',
            'capacity' => 50,
        ]);
    }

    /** @test */
    public function it_returns_404_for_sections_of_nonexistent_class(): void
    {
        $response = $this->getJson('/api/v1/academic/classes/999/sections', [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function academic_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/academic/years')->assertStatus(401);
        $this->getJson('/api/v1/academic/classes')->assertStatus(401);
        $this->postJson('/api/v1/academic/years', [])->assertStatus(401);
        $this->postJson('/api/v1/academic/classes', [])->assertStatus(401);
        $this->postJson('/api/v1/academic/sections', [])->assertStatus(401);
    }
}
