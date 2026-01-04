<?php

namespace Modules\Otp\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Otp\Models\OtpWhitelist;
use Modules\User\Rules\PhoneNumber;

class OtpWhitelistController extends Controller
{
    /**
     * Display a listing of whitelisted OTPs.
     */
    public function index(): JsonResponse
    {
        $whitelists = OtpWhitelist::all();
        return apiResponse(true, 'OTP Whitelist entries', $whitelists);
    }

    /**
     * Store a newly created whitelist entry.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'recipient_type' => 'required|in:email,phone',
            'recipient' => [
                'required',
                $request->input('recipient_type') === 'email'
                    ? 'email'
                    : new PhoneNumber
            ],
            'fixed_otp' => 'required|string|size:6|regex:/^[0-9]+$/',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:255',
        ]);

        // Check for duplicate entries
        $existing = OtpWhitelist::where('recipient_type', $data['recipient_type'])
            ->where('recipient', $data['recipient'])
            ->first();

        if ($existing) {
            return apiResponse(false, 'This recipient is already whitelisted', null, code: 400);
        }

        $whitelist = OtpWhitelist::create($data);
        return apiResponse(true, 'Whitelist entry created successfully', $whitelist, code: 201);
    }

    /**
     * Display the specified whitelist entry.
     */
    public function show(int $id): JsonResponse
    {
        $whitelist = OtpWhitelist::find($id);

        if (!$whitelist) {
            return apiResponse(false, 'Whitelist entry not found', null, code: 404);
        }

        return apiResponse(true, 'Whitelist entry details', $whitelist);
    }

    /**
     * Update the specified whitelist entry.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $whitelist = OtpWhitelist::find($id);

        if (!$whitelist) {
            return apiResponse(false, 'Whitelist entry not found', null, code: 404);
        }

        $data = $request->validate([
            'recipient_type' => 'sometimes|in:email,phone',
            'recipient' => [
                'sometimes',
                function ($attribute, $value, $fail) use ($request, $whitelist) {
                    $type = $request->input('recipient_type', $whitelist->recipient_type);
                    if ($type === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $fail('The recipient must be a valid email address.');
                    } elseif ($type === 'phone') {
                        $phoneRule = new PhoneNumber();
                        $phoneRule->validate($attribute, $value, $fail);
                    }
                }
            ],
            'fixed_otp' => 'sometimes|string|size:6|regex:/^[0-9]+$/',
            'is_active' => 'sometimes|boolean',
            'description' => 'nullable|string|max:255',
        ]);

        // Check for duplicate entries if recipient or type is changing
        if (isset($data['recipient']) || isset($data['recipient_type'])) {
            $type = $data['recipient_type'] ?? $whitelist->recipient_type;
            $recipient = $data['recipient'] ?? $whitelist->recipient;

            $existing = OtpWhitelist::where('recipient_type', $type)
                ->where('recipient', $recipient)
                ->where('id', '!=', $id)
                ->first();

            if ($existing) {
                return apiResponse(false, 'Another entry already exists for this recipient', null, code: 400);
            }
        }

        $whitelist->update($data);
        return apiResponse(true, 'Whitelist entry updated successfully', $whitelist);
    }

    /**
     * Remove the specified whitelist entry.
     */
    public function destroy(int $id): JsonResponse
    {
        $whitelist = OtpWhitelist::find($id);

        if (!$whitelist) {
            return apiResponse(false, 'Whitelist entry not found', null, code: 404);
        }

        $whitelist->delete();
        return apiResponse(true, 'Whitelist entry deleted successfully', null);
    }
}
