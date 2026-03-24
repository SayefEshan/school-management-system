<?php

namespace Modules\Admission\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admission\Enums\AdmissionStatus;
use Modules\Admission\Models\AcademicYear;
use Modules\Admission\Models\AdmissionApplication;
use Modules\Admission\Models\ClassModel;
use Tests\TestCase;

class AdminAdmissionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private string $token;
    private AcademicYear $academicYear;
    private ClassModel $class;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_active' => true]);
        $this->token = $this->admin->createToken('test')->plainTextToken;
        $this->academicYear = AcademicYear::create([
            'name' => '2025',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'is_current' => true,
        ]);
        $this->class = ClassModel::create([
            'name' => 'Class 6',
            'numeric_code' => '06',
            'order' => 1,
        ]);
    }

    private function createAdmissionApplication(array $overrides = []): AdmissionApplication
    {
        return AdmissionApplication::create(array_merge([
            'tracking_code' => 'ADM-2025-' . str_pad(AdmissionApplication::count() + 1, 6, '0', STR_PAD_LEFT),
            'academic_year_id' => $this->academicYear->id,
            'class_id' => $this->class->id,
            'student_name_bn' => 'রহিম উদ্দিন',
            'student_name_en' => 'Rahim Uddin',
            'date_of_birth' => '2010-05-15',
            'gender' => 'male',
            'father_name_bn' => 'করিম উদ্দিন',
            'father_name_en' => 'Karim Uddin',
            'present_village' => 'Kamlapur',
            'present_thana' => 'Motijheel',
            'present_district' => 'Dhaka',
            'status' => AdmissionStatus::PENDING,
        ], $overrides));
    }

    // ---- List Tests ----

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_list_all_applications(): void
    {
        $this->createAdmissionApplication();
        $this->createAdmissionApplication(['student_name_en' => 'Another Student']);

        $response = $this->getJson('/api/v1/admin/admissions', [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 2)
            ->assertJsonCount(2, 'data.applications');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_filter_by_status(): void
    {
        $this->createAdmissionApplication(['status' => AdmissionStatus::PENDING]);
        $this->createAdmissionApplication(['status' => AdmissionStatus::ACCEPTED, 'student_name_en' => 'Accepted Student']);

        $response = $this->getJson('/api/v1/admin/admissions?status=accepted', [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.applications.0.status', 'accepted');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_filter_by_class(): void
    {
        $class2 = ClassModel::create(['name' => 'Class 7', 'numeric_code' => '07', 'order' => 2]);
        $this->createAdmissionApplication();
        $this->createAdmissionApplication(['class_id' => $class2->id, 'student_name_en' => 'Class 7 Student']);

        $response = $this->getJson("/api/v1/admin/admissions?class_id={$class2->id}", [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_search_by_name(): void
    {
        $this->createAdmissionApplication(['student_name_en' => 'Rahim Uddin']);
        $this->createAdmissionApplication(['student_name_en' => 'Karim Hasan']);

        $response = $this->getJson('/api/v1/admin/admissions?search=Rahim', [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.applications.0.student_name_en', 'Rahim Uddin');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_search_by_tracking_code(): void
    {
        $app = $this->createAdmissionApplication();

        $response = $this->getJson("/api/v1/admin/admissions?search={$app->tracking_code}", [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_paginates_results(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->createAdmissionApplication(['student_name_en' => "Student {$i}"]);
        }

        $response = $this->getJson('/api/v1/admin/admissions?per_page=10', [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 30)
            ->assertJsonPath('data.pagination.last_page', 3)
            ->assertJsonCount(10, 'data.applications');
    }

    // ---- View Tests ----

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_view_single_application(): void
    {
        $app = $this->createAdmissionApplication();

        $response = $this->getJson("/api/v1/admin/admissions/{$app->id}", [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertOk()
            ->assertJsonPath('data.tracking_code', $app->tracking_code)
            ->assertJsonPath('data.student_name_en', 'Rahim Uddin');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_404_for_nonexistent_application(): void
    {
        $response = $this->getJson('/api/v1/admin/admissions/999', [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertStatus(404);
    }

    // ---- Accept Tests ----

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_accept_pending_application(): void
    {
        $app = $this->createAdmissionApplication();

        $response = $this->postJson("/api/v1/admin/admissions/{$app->id}/accept", [
            'notes' => 'Good application',
        ], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'accepted');

        $app->refresh();
        $this->assertEquals(AdmissionStatus::ACCEPTED, $app->status);
        $this->assertEquals($this->admin->id, $app->reviewed_by);
        $this->assertNotNull($app->reviewed_at);
        $this->assertEquals('Good application', $app->review_notes);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_accept_under_review_application(): void
    {
        $app = $this->createAdmissionApplication(['status' => AdmissionStatus::UNDER_REVIEW]);

        $response = $this->postJson("/api/v1/admin/admissions/{$app->id}/accept", [], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'accepted');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_cannot_accept_already_accepted_application(): void
    {
        $app = $this->createAdmissionApplication(['status' => AdmissionStatus::ACCEPTED]);

        $response = $this->postJson("/api/v1/admin/admissions/{$app->id}/accept", [], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertStatus(422);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_cannot_accept_rejected_application(): void
    {
        $app = $this->createAdmissionApplication(['status' => AdmissionStatus::REJECTED]);

        $response = $this->postJson("/api/v1/admin/admissions/{$app->id}/accept", [], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertStatus(422);
    }

    // ---- Reject Tests ----

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_reject_pending_application(): void
    {
        $app = $this->createAdmissionApplication();

        $response = $this->postJson("/api/v1/admin/admissions/{$app->id}/reject", [
            'reason' => 'Documents incomplete',
            'notes' => 'Missing birth certificate',
        ], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        $app->refresh();
        $this->assertEquals(AdmissionStatus::REJECTED, $app->status);
        $this->assertEquals('Documents incomplete', $app->rejection_reason);
        $this->assertEquals('Missing birth certificate', $app->review_notes);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_reason_for_rejection(): void
    {
        $app = $this->createAdmissionApplication();

        $response = $this->postJson("/api/v1/admin/admissions/{$app->id}/reject", [], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_cannot_reject_already_accepted_application(): void
    {
        $app = $this->createAdmissionApplication(['status' => AdmissionStatus::ACCEPTED]);

        $response = $this->postJson("/api/v1/admin/admissions/{$app->id}/reject", [
            'reason' => 'Changed mind',
        ], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertStatus(422);
    }

    // ---- Mark Under Review Tests ----

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_mark_pending_as_under_review(): void
    {
        $app = $this->createAdmissionApplication();

        $response = $this->postJson("/api/v1/admin/admissions/{$app->id}/review", [], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'under_review');

        $app->refresh();
        $this->assertEquals(AdmissionStatus::UNDER_REVIEW, $app->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_cannot_mark_accepted_as_under_review(): void
    {
        $app = $this->createAdmissionApplication(['status' => AdmissionStatus::ACCEPTED]);

        $response = $this->postJson("/api/v1/admin/admissions/{$app->id}/review", [], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertStatus(422);
    }

    // ---- Auth Tests ----

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/admissions')->assertStatus(401);
        $this->getJson('/api/v1/admin/admissions/1')->assertStatus(401);
        $this->postJson('/api/v1/admin/admissions/1/accept')->assertStatus(401);
        $this->postJson('/api/v1/admin/admissions/1/reject')->assertStatus(401);
        $this->postJson('/api/v1/admin/admissions/1/review')->assertStatus(401);
    }
}
