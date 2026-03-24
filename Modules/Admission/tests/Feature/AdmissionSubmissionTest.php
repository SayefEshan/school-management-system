<?php

namespace Modules\Admission\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Admission\Enums\AdmissionStatus;
use Modules\Admission\Models\AcademicYear;
use Modules\Admission\Models\AdmissionApplication;
use Modules\Admission\Models\ClassModel;
use Tests\TestCase;

class AdmissionSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private AcademicYear $academicYear;
    private ClassModel $class;

    protected function setUp(): void
    {
        parent::setUp();
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

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'academic_year_id' => $this->academicYear->id,
            'class_id' => $this->class->id,
            'student_name_bn' => 'রহিম উদ্দিন',
            'student_name_en' => 'Rahim Uddin',
            'date_of_birth' => '2010-05-15',
            'gender' => 'male',
            'father_name_bn' => 'করিম উদ্দিন',
            'father_name_en' => 'Karim Uddin',
            'father_mobile' => '01812345678',
            'present_village' => 'Kamlapur',
            'present_thana' => 'Motijheel',
            'present_district' => 'Dhaka',
            'same_as_present' => true,
        ], $overrides);
    }

    // ---- Submission Tests ----

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_submit_admission_with_complete_data(): void
    {
        $response = $this->postJson('/api/v1/admissions', $this->validPayload([
            'birth_registration_no' => '20101234567890',
            'blood_group' => 'B+',
            'nationality' => 'Bangladeshi',
            'religion' => 'Islam',
            'mobile' => '01712345678',
            'mother_name_bn' => 'ফাতেমা বেগম',
            'mother_name_en' => 'Fatema Begum',
        ]));

        $response->assertStatus(201)
            ->assertJsonPath('error', false)
            ->assertJsonPath('message', 'Admission application submitted successfully.')
            ->assertJsonPath('data.application.status', 'pending')
            ->assertJsonPath('data.application.student_name_en', 'Rahim Uddin')
            ->assertJsonStructure([
                'data' => [
                    'tracking_code',
                    'application' => [
                        'id', 'tracking_code', 'status', 'status_label', 'status_label_bn',
                        'academic_year', 'class', 'student_name_bn', 'student_name_en',
                        'date_of_birth', 'gender', 'father_name_bn', 'father_name_en',
                    ],
                ],
            ]);

        $this->assertDatabaseCount('admission_applications', 1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_generates_unique_tracking_codes(): void
    {
        $this->postJson('/api/v1/admissions', $this->validPayload());
        $this->postJson('/api/v1/admissions', $this->validPayload([
            'student_name_en' => 'Another Student',
        ]));

        $codes = AdmissionApplication::pluck('tracking_code')->toArray();
        $this->assertCount(2, $codes);
        $this->assertCount(2, array_unique($codes));
        $this->assertStringStartsWith('ADM-', $codes[0]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_copies_present_address_to_permanent_when_same_as_present(): void
    {
        $response = $this->postJson('/api/v1/admissions', $this->validPayload([
            'present_village' => 'Kamlapur',
            'present_thana' => 'Motijheel',
            'present_district' => 'Dhaka',
            'present_post_code' => '1217',
            'same_as_present' => true,
        ]));

        $response->assertStatus(201);
        $app = AdmissionApplication::first();
        $this->assertEquals('Kamlapur', $app->permanent_village);
        $this->assertEquals('Motijheel', $app->permanent_thana);
        $this->assertEquals('Dhaka', $app->permanent_district);
        $this->assertEquals('1217', $app->permanent_post_code);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_does_not_require_auth_for_submission(): void
    {
        $response = $this->postJson('/api/v1/admissions', $this->validPayload());
        $response->assertStatus(201);
    }

    // ---- Validation Tests ----

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_mandatory_fields(): void
    {
        $response = $this->postJson('/api/v1/admissions', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'academic_year_id',
                'class_id',
                'student_name_bn',
                'student_name_en',
                'date_of_birth',
                'gender',
                'present_village',
                'present_thana',
                'present_district',
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_gender_values(): void
    {
        $response = $this->postJson('/api/v1/admissions', $this->validPayload([
            'gender' => 'invalid',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['gender']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_blood_group_values(): void
    {
        $response = $this->postJson('/api/v1/admissions', $this->validPayload([
            'blood_group' => 'X+',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['blood_group']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_date_of_birth_is_in_past(): void
    {
        $response = $this->postJson('/api/v1/admissions', $this->validPayload([
            'date_of_birth' => now()->addYear()->format('Y-m-d'),
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date_of_birth']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_class_and_year_exist(): void
    {
        $response = $this->postJson('/api/v1/admissions', $this->validPayload([
            'academic_year_id' => 999,
            'class_id' => 999,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['academic_year_id', 'class_id']);
    }

    // ---- Guardian Conditional Tests ----

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_accepts_father_info_without_guardian(): void
    {
        $response = $this->postJson('/api/v1/admissions', $this->validPayload([
            'father_name_bn' => 'করিম উদ্দিন',
            'father_name_en' => 'Karim Uddin',
        ]));

        $response->assertStatus(201);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_accepts_mother_info_without_guardian(): void
    {
        $response = $this->postJson('/api/v1/admissions', $this->validPayload([
            'father_name_bn' => null,
            'father_name_en' => null,
            'mother_name_bn' => 'ফাতেমা বেগম',
            'mother_name_en' => 'Fatema Begum',
        ]));

        $response->assertStatus(201);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_guardian_when_no_father_or_mother(): void
    {
        $response = $this->postJson('/api/v1/admissions', $this->validPayload([
            'father_name_bn' => null,
            'father_name_en' => null,
            'mother_name_bn' => null,
            'mother_name_en' => null,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['guardian_name', 'guardian_mobile', 'guardian_relation']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_accepts_guardian_only_when_no_parents(): void
    {
        $response = $this->postJson('/api/v1/admissions', $this->validPayload([
            'father_name_bn' => null,
            'father_name_en' => null,
            'mother_name_bn' => null,
            'mother_name_en' => null,
            'guardian_name' => 'আব্দুল হাকিম',
            'guardian_relation' => 'Uncle',
            'guardian_mobile' => '01612345678',
        ]));

        $response->assertStatus(201)
            ->assertJsonPath('data.application.guardian_name', 'আব্দুল হাকিম');
    }

    // ---- File Upload Tests ----

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_accepts_valid_photo_upload(): void
    {
        Storage::fake('public');

        $payload = $this->validPayload();
        $payload['photo'] = UploadedFile::fake()->image('student.jpg', 300, 300)->size(500);

        $response = $this->postJson('/api/v1/admissions', $payload);

        $response->assertStatus(201);
        $app = AdmissionApplication::first();
        $this->assertNotNull($app->photo);
        Storage::disk('public')->assertExists($app->photo);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_rejects_photo_exceeding_2mb(): void
    {
        Storage::fake('public');

        $payload = $this->validPayload();
        $payload['photo'] = UploadedFile::fake()->image('large.jpg')->size(3000); // 3MB

        $response = $this->postJson('/api/v1/admissions', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['photo']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_rejects_invalid_photo_format(): void
    {
        Storage::fake('public');

        $payload = $this->validPayload();
        $payload['photo'] = UploadedFile::fake()->create('document.pdf', 500);

        $response = $this->postJson('/api/v1/admissions', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['photo']);
    }

    /** @test */
    public function it_rejects_signature_exceeding_1mb(): void
    {
        Storage::fake('public');

        $payload = $this->validPayload();
        $payload['student_signature'] = UploadedFile::fake()->image('sig.jpg')->size(1500); // 1.5MB

        $response = $this->postJson('/api/v1/admissions', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['student_signature']);
    }

    // ---- Status Check Tests ----

    /** @test */
    public function it_can_check_status_by_tracking_code(): void
    {
        $this->postJson('/api/v1/admissions', $this->validPayload());
        $app = AdmissionApplication::first();

        $response = $this->getJson("/api/v1/admissions/{$app->tracking_code}/status");

        $response->assertOk()
            ->assertJsonPath('data.tracking_code', $app->tracking_code)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.status_label', 'Pending')
            ->assertJsonStructure([
                'data' => ['tracking_code', 'status', 'status_label', 'status_label_bn', 'submitted_at'],
            ]);
    }

    /** @test */
    public function it_returns_404_for_invalid_tracking_code(): void
    {
        $response = $this->getJson('/api/v1/admissions/ADM-2025-999999/status');

        $response->assertStatus(404)
            ->assertJsonPath('error', true);
    }

    /** @test */
    public function status_check_does_not_require_auth(): void
    {
        $this->postJson('/api/v1/admissions', $this->validPayload());
        $app = AdmissionApplication::first();

        $response = $this->getJson("/api/v1/admissions/{$app->tracking_code}/status");
        $response->assertOk();
    }
}
