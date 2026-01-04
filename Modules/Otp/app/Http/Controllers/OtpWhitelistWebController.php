<?php

namespace Modules\Otp\Http\Controllers;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Otp\Models\OtpWhitelist;
use Modules\User\Rules\PhoneNumber;

class OtpWhitelistWebController extends Controller
{
    public function __construct()
    {
        $this->middleware(['can:View OTP Whitelist'])->only(['index', 'show']);
        $this->middleware(['can:Create OTP Whitelist'])->only(['create', 'store']);
        $this->middleware(['can:Edit OTP Whitelist'])->only(['edit', 'update']);
        $this->middleware(['can:Delete OTP Whitelist'])->only(['destroy']);
    }

    /**
     * Display a listing of the whitelist entries.
     * @return Renderable
     */
    public function index(Request $request): View
    {
        $whitelists = OtpWhitelist::query();
        $whitelists->orderBy('id', 'desc');
        $whitelists = $whitelists->paginate($request->per_page ?? PaginationEnum::DEFAULT_PAGINATE);

        return view('otp::whitelist.index', [
            'whitelists' => $whitelists,
        ]);
    }

    /**
     * Show the form for creating a new whitelist entry.
     * @return Renderable
     */
    public function create(): View
    {
        return view('otp::whitelist.create');
    }

    /**
     * Store a newly created whitelist entry in storage.
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'recipient_type' => 'required|in:email,phone',
            'recipient' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('recipient_type') === 'email') {
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $fail('The recipient must be a valid email address.');
                        }
                    } else {
                        $phoneRule = new PhoneNumber();
                        $phoneRule->validate($attribute, $value, $fail);
                    }
                }
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
            return redirect()->back()
                ->withInput()
                ->with('error', 'This recipient is already whitelisted');
        }

        // Set default value for is_active if not provided
        $data['is_active'] = $data['is_active'] ?? true;

        OtpWhitelist::create($data);
        return redirect()->route('otp-whitelist.index')
            ->with('success', 'Whitelist entry created successfully');
    }

    /**
     * Display the specified whitelist entry.
     * @param int $id
     * @return Renderable
     */
    public function show(int $id): View
    {
        $whitelist = OtpWhitelist::findOrFail($id);
        return view('otp::whitelist.show', [
            'whitelist' => $whitelist
        ]);
    }

    /**
     * Show the form for editing the specified whitelist entry.
     * @param int $id
     * @return Renderable
     */
    public function edit(int $id): View
    {
        $whitelist = OtpWhitelist::findOrFail($id);
        return view('otp::whitelist.edit', [
            'whitelist' => $whitelist
        ]);
    }

    /**
     * Update the specified whitelist entry in storage.
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $whitelist = OtpWhitelist::findOrFail($id);

        $data = $request->validate([
            'recipient_type' => 'sometimes|in:email,phone',
            'recipient' => [
                'sometimes',
                function ($attribute, $value, $fail) use ($request, $whitelist) {
                    $type = $request->input('recipient_type', $whitelist->recipient_type);
                    if ($type === 'email') {
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $fail('The recipient must be a valid email address.');
                        }
                    } else {
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
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Another entry already exists for this recipient');
            }
        }

        $whitelist->update($data);
        return redirect()->route('otp-whitelist.index')
            ->with('success', 'Whitelist entry updated successfully');
    }

    /**
     * Remove the specified whitelist entry from storage.
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        $whitelist = OtpWhitelist::findOrFail($id);
        $whitelist->delete();

        return redirect()->route('otp-whitelist.index')
            ->with('success', 'Whitelist entry deleted successfully');
    }
}
