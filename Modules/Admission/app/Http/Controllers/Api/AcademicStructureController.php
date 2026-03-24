<?php

namespace Modules\Admission\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Admission\Models\AcademicYear;
use Modules\Admission\Models\ClassModel;
use Modules\Admission\Models\Section;

class AcademicStructureController extends Controller
{
    // ---- Academic Years ----

    public function academicYears(): JsonResponse
    {
        try {
            $years = AcademicYear::active()->latest('start_date')->get();
            return apiResponse(true, 'Academic Years', $years);
        } catch (Exception $e) {
            Log::error('Failed to fetch academic years: ' . $e->getMessage(), ['exception' => $e]);
            return apiResponse(false, 'Something went wrong.', code: 500);
        }
    }

    public function storeAcademicYear(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:50', 'unique:academic_years,name'],
                'name_bn' => ['nullable', 'string', 'max:50'],
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date', 'after:start_date'],
                'is_current' => ['nullable', 'boolean'],
            ]);

            $data['created_by'] = auth()->id();

            // If marking as current, unset others
            if (!empty($data['is_current'])) {
                AcademicYear::where('is_current', true)->update(['is_current' => false]);
            }

            $year = AcademicYear::create($data);
            return apiResponse(true, 'Academic Year created successfully.', $year, code: 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Failed to create academic year: ' . $e->getMessage(), ['exception' => $e]);
            return apiResponse(false, 'Something went wrong.', code: 500);
        }
    }

    // ---- Classes ----

    public function classes(): JsonResponse
    {
        try {
            $classes = ClassModel::active()->ordered()
                ->with(['sections' => fn($q) => $q->active()])
                ->get();
            return apiResponse(true, 'Classes', $classes);
        } catch (Exception $e) {
            Log::error('Failed to fetch classes: ' . $e->getMessage(), ['exception' => $e]);
            return apiResponse(false, 'Something went wrong.', code: 500);
        }
    }

    public function storeClass(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:100'],
                'name_bn' => ['nullable', 'string', 'max:100'],
                'numeric_code' => ['required', 'string', 'size:2', 'unique:classes,numeric_code'],
                'order' => ['nullable', 'integer'],
            ]);

            $data['created_by'] = auth()->id();
            $class = ClassModel::create($data);
            return apiResponse(true, 'Class created successfully.', $class, code: 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Failed to create class: ' . $e->getMessage(), ['exception' => $e]);
            return apiResponse(false, 'Something went wrong.', code: 500);
        }
    }

    // ---- Sections ----

    public function sections(int $classId): JsonResponse
    {
        try {
            $class = ClassModel::findOrFail($classId);
            $sections = $class->sections()->active()->get();
            return apiResponse(true, 'Sections', $sections);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return apiResponse(false, 'Class not found.', code: 404);
        } catch (Exception $e) {
            Log::error('Failed to fetch sections: ' . $e->getMessage(), ['exception' => $e]);
            return apiResponse(false, 'Something went wrong.', code: 500);
        }
    }

    public function storeSection(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'class_id' => ['required', 'exists:classes,id'],
                'name' => ['required', 'string', 'max:50'],
                'name_bn' => ['nullable', 'string', 'max:50'],
                'capacity' => ['nullable', 'integer', 'min:1', 'max:200'],
            ]);

            $data['created_by'] = auth()->id();
            $section = Section::create($data);
            return apiResponse(true, 'Section created successfully.', $section, code: 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Failed to create section: ' . $e->getMessage(), ['exception' => $e]);
            return apiResponse(false, 'Something went wrong.', code: 500);
        }
    }
}
