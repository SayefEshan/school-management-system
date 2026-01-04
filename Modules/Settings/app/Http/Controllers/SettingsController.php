<?php

namespace Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\SendSmsJob;
use App\Services\FileManagerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Settings\Models\Setting;
use Modules\User\Rules\PhoneNumber;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{

    public function __construct()
    {
        $this->middleware(['can:Edit System Setting'])->only(['index', 'store']);
        $this->middleware(['can:Developer Setting'])->only([
            'manage',
            'create',
            'storeNew',
            'edit',
            'update',
            'destroy',
            'export',
            'import',
            'importForm',
            'bulkUpdate',
            'bulkDelete'
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Define special settings that will be handled separately
        $specialSettings = ['privacy_policy', 'sms_gateways', 'sms_gateway', 'email_mailers', 'email_mailer'];

        // Get all settings except the special ones
        $settings = Setting::enabled()
            ->whereNotIn('key', $specialSettings)
            ->orderBy('id', 'asc')
            ->get()
            ->groupBy('group');

        return view('settings::index', compact('settings', 'specialSettings'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Define special settings that should be skipped
        $specialSettings = ['privacy_policy', 'sms_gateways', 'sms_gateway', 'email_mailers', 'email_mailer'];

        // Only get regular settings, excluding special ones
        $settings = Setting::whereNotIn('key', $specialSettings)->get();

        foreach ($settings as $setting) {
            if ($setting->type === 'file' || $setting->type === 'image') {
                if ($request->hasFile($setting->key)) {
                    $value = FileManagerService::uploadFile(
                        $request->file($setting->key),
                        $setting->value ?? null,
                        'settings'
                    );
                    $setting->value = getUrlFromPath($value);
                    $setting->save();
                }
                continue;
            }

            $newValue = $request->input($setting->key);

            if ($setting->type === 'boolean' && $setting->value === (bool)$newValue) {
                continue;
            }
            $setting->value = $request->input($setting->key);
            $setting->save();
        }
        return redirect()->back()->with('success', 'Settings updated successfully');
    }

    /**
     * Display a list of settings for management (create, edit, delete).
     */
    public function manage()
    {
        $settings = Setting::orderBy('group')->orderBy('key')->get();
        return view('settings::manage', compact('settings'));
    }

    /**
     * Show the form for creating a new setting.
     */
    public function create()
    {
        // Get existing groups for dropdown
        $groups = Setting::distinct('group')->pluck('group')->toArray();
        $types = ['text', 'textarea', 'file', 'image', 'integer', 'float', 'boolean', 'select', 'multi-select', 'array', 'json'];

        return view('settings::create', compact('groups', 'types'));
    }

    /**
     * Store a newly created setting.
     */
    public function storeNew(Request $request): RedirectResponse
    {
        // Modify request data to ensure boolean fields are properly handled
        $data = $request->all();
        $data['is_visible'] = $request->has('is_visible');
        $data['is_required'] = $request->has('is_required');

        $validator = validator($data, [
            'key' => 'required|string|max:255|unique:settings,key',
            'group' => 'required|string|max:255',
            'type' => 'required|string|in:text,textarea,file,image,integer,float,boolean,select,multi-select,array,json',
            'description' => 'nullable|string',
            'is_visible' => 'boolean',
            'is_required' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $setting = new Setting();
        $setting->key = $request->key;
        $setting->group = $request->group;
        $setting->type = $request->type;
        $setting->description = $request->description;
        $setting->is_visible = $request->has('is_visible');
        $setting->is_required = $request->has('is_required');

        // Handle options for select and multi-select
        if ($request->type === 'select' || $request->type === 'multi-select') {
            $options = [];
            $optionKeys = $request->input('option_keys', []);
            $optionValues = $request->input('option_values', []);

            foreach ($optionKeys as $index => $key) {
                if (isset($optionValues[$index]) && !empty($key)) {
                    $options[$key] = $optionValues[$index];
                }
            }

            $setting->options = json_encode($options);
        }

        // Handle initial value based on type
        if ($request->type === 'boolean') {
            $setting->value = $request->has('value_boolean') ? '1' : '0';
        } elseif ($request->type === 'json' || $request->type === 'array') {
            $setting->value = '[]'; // Default empty array/object
        } else {
            $setting->value = $request->input('value_' . $request->type, '');
        }

        $setting->save();

        return redirect()->route('system_settings.manage')->with('success', 'Setting created successfully');
    }

    /**
     * Show the form for editing a setting.
     */
    public function edit(Setting $setting)
    {
        // Get existing groups for dropdown
        $groups = Setting::distinct('group')->pluck('group')->toArray();
        $types = ['text', 'textarea', 'file', 'image', 'integer', 'float', 'boolean', 'select', 'multi-select', 'array', 'json'];

        return view('settings::edit', compact('setting', 'groups', 'types'));
    }

    /**
     * Update the specified setting.
     */
    public function update(Request $request, Setting $setting): RedirectResponse
    {
        // Modify request data to ensure boolean fields are properly handled
        $data = $request->all();
        $data['is_visible'] = $request->has('is_visible');
        $data['is_required'] = $request->has('is_required');

        $validator = validator($data, [
            'group' => 'required|string|max:255',
            'type' => 'required|string|in:text,textarea,file,image,integer,float,boolean,select,multi-select,array,json',
            'description' => 'nullable|string',
            'is_visible' => 'boolean',
            'is_required' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $setting->group = $request->group;
        $setting->type = $request->type;
        $setting->description = $request->description;
        $setting->is_visible = $request->has('is_visible');
        $setting->is_required = $request->has('is_required');

        // Handle options for select and multi-select
        if ($request->type === 'select' || $request->type === 'multi-select') {
            $options = [];
            $optionKeys = $request->input('option_keys', []);
            $optionValues = $request->input('option_values', []);

            foreach ($optionKeys as $index => $key) {
                if (isset($optionValues[$index]) && !empty($key)) {
                    $options[$key] = $optionValues[$index];
                }
            }

            $setting->options = json_encode($options);
        }

        // Handle value based on type
        if ($request->type === 'file' || $request->type === 'image') {
            if ($request->hasFile('value_' . $request->type)) {
                $value = FileManagerService::uploadFile(
                    $request->file('value_' . $request->type),
                    $setting->value ?? null,
                    'settings'
                );
                $setting->value = getUrlFromPath($value);
            }
        } elseif ($request->type === 'boolean') {
            $setting->value = $request->has('value_boolean') ? '1' : '0';
        } elseif ($request->filled('value_' . $request->type)) {
            $setting->value = $request->input('value_' . $request->type);
        }

        $setting->save();

        return redirect()->route('system_settings.manage')->with('success', 'Setting updated successfully');
    }

    /**
     * Remove the specified setting.
     */
    public function destroy(Setting $setting): RedirectResponse
    {
        $setting->delete();
        return redirect()->route('system_settings.manage')->with('success', 'Setting deleted successfully');
    }

    public function sendTestSMS(Request $request)
    {
        $data = $request->validate([
            'mobile_no' => ['required', new PhoneNumber]
        ]);

        $phone = $data['mobile_no'];
        $message = 'This is a test message from ' . config('app.name');
        SendSmsJob::dispatch($message, $phone);

        return response()->json(['message' => 'SMS Job executed successfully. See logs for more details.']);
    }

    public function sendTestEmail(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email']
        ]);

        try {
            $email = $data['email'];
            $subject = 'This is a test email from ' . config('app.name');
            $message = 'This is a test message from ' . config('app.name');
            Mail::raw($message, static function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Email Job failed ' . $e->getMessage()]);
        }

        return response()->json(['message' => 'Email Job executed successfully']);
    }

    /**
     * Show the import form
     */
    public function importForm()
    {
        return view('settings::import');
    }

    /**
     * Export settings to JSON file
     */
    public function export(Request $request)
    {
        try {
            // You can add filters here if needed (by group, etc.)
            $group = $request->input('group');
            $settings = DB::table('settings')
                ->select(
                    'id',
                    'key',
                    'group',
                    'type',
                    'description',
                    'is_visible',
                    'is_required',
                    'is_disabled',
                    'value',
                    'options'
                )
                ->orderBy('group')
                ->orderBy('key');

            if ($group) {
                $settings = $settings->where('group', $group);
            }

            $settings = $settings->get();
            $exportData = [];

            foreach ($settings as $setting) {
                $data = [
                    'key' => $setting->key,
                    'group' => $setting->group,
                    'type' => $setting->type,
                    'description' => $setting->description,
                    'is_visible' => (bool)$setting->is_visible,
                    'is_required' => (bool)$setting->is_required,
                    'is_disabled' => (bool)$setting->is_disabled,
                ];

                // Handle different types of values correctly
                $value = $setting->value;
                if ($setting->type === 'json') {
                    try {
                        // Check if already an array (already decoded by model accessors)
                        if (is_array($value)) {
                            $data['value'] = json_encode($value);
                        } else if (is_string($value)) {
                            // Try to decode and re-encode if it's a string
                            $decoded = json_decode($value, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $data['value'] = json_encode($decoded);
                            } else {
                                // If it's not valid JSON, store as a regular string
                                $data['value'] = $value;
                                Log::warning("Setting {$setting->key} has invalid JSON value: " . json_last_error_msg());
                            }
                        } else {
                            // For any other type, just encode it
                            $data['value'] = json_encode($value);
                        }
                    } catch (\Exception $e) {
                        // If there's an exception, store as a regular string
                        $data['value'] = is_string($value) ? $value : json_encode([]);
                        Log::warning("Setting {$setting->key} failed to decode JSON value: " . $e->getMessage());
                    }
                } else if ($setting->type === 'array' || $setting->type === 'multi-select') {
                    // For array types, ensure it's properly formatted
                    try {
                        if (is_array($value)) {
                            $data['value'] = implode(',', $value);
                        } else if (is_string($value)) {
                            // First check if it's a JSON string that might represent an array
                            if ($this->isJson($value)) {
                                $arrayValue = json_decode($value, true);
                                $data['value'] = is_array($arrayValue) ? implode(',', $arrayValue) : $value;
                            } else {
                                // If it's just a regular string, use it as is
                                $data['value'] = $value;
                            }
                        } else {
                            // For any other type, convert to string
                            $data['value'] = (string)$value;
                        }
                    } catch (\Exception $e) {
                        $data['value'] = is_string($value) ? $value : '';
                        Log::warning("Setting {$setting->key} failed to process array value: " . $e->getMessage());
                    }
                } else {
                    // For other types, just use the value directly
                    $data['value'] = $value;
                }

                // Add options if they exist
                if (!empty($setting->options)) {
                    // Handle options safely
                    try {
                        if (is_array($setting->options)) {
                            // If it's already an array, encode it
                            $data['options'] = json_encode($setting->options);
                        } else if (is_string($setting->options)) {
                            // If it's a string, check if valid JSON
                            $decoded = json_decode($setting->options, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $data['options'] = $setting->options;
                            } else {
                                // If it's invalid JSON, encode it as a JSON string
                                $data['options'] = json_encode(['value' => $setting->options]);
                                Log::warning("Setting {$setting->key} has invalid JSON options: " . json_last_error_msg());
                            }
                        } else {
                            // For any other type, just encode it
                            $data['options'] = json_encode($setting->options);
                        }
                    } catch (\Exception $e) {
                        // If there's an exception, create a fallback object
                        $data['options'] = json_encode(['error' => 'Invalid options data']);
                        Log::warning("Setting {$setting->key} options couldn't be processed: " . $e->getMessage());
                    }
                }

                $exportData[] = $data;
            }

            $filename = 'settings_export_' . date('Y-m-d_His') . '.json';

            // Use json_encode with error checking and proper options
            $jsonContent = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            // Check for JSON encoding errors
            if (json_last_error() !== JSON_ERROR_NONE) {
                $error = json_last_error_msg();
                Log::error('Settings export JSON encoding error: ' . $error);
                throw new \Exception('JSON encoding error: ' . $error);
            }

            return response($jsonContent)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            Log::error('Settings export error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error exporting settings: ' . $e->getMessage());
        }
    }

    /**
     * Check if a string is valid JSON
     */
    private function isJson($string)
    {
        if (!is_string($string)) {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Import settings from JSON file
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'settings_file' => 'required|file|mimes:json',
                'import_mode' => 'required|in:merge,overwrite',
            ]);

            $file = $request->file('settings_file');
            $importMode = $request->input('import_mode');

            // Read and validate JSON content
            $jsonContent = file_get_contents($file->getPathname());

            // Try to detect and fix common JSON issues
            $jsonContent = $this->sanitizeJsonContent($jsonContent);

            $settings = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON parsing error: ' . json_last_error_msg() . ', content sample: ' . substr($jsonContent, 0, 100));
                return redirect()->back()->with('error', 'Invalid JSON file: ' . json_last_error_msg() . '. Please check the file format.');
            }

            // Check if the JSON is an array of settings
            if (!is_array($settings) || empty($settings)) {
                return redirect()->back()->with('error', 'The JSON file must contain an array of settings.');
            }

            // Check if the first element is an array with key property (to handle different export formats)
            if (!isset($settings[0]['key'])) {
                // Try to handle case where the JSON might be wrapped in an extra object
                if (isset($settings['data']) && is_array($settings['data'])) {
                    $settings = $settings['data'];
                } else {
                    return redirect()->back()->with('error', 'The JSON file does not contain valid settings format. Each setting must have at least a "key" property.');
                }
            }

            // Validate the structure of each setting
            $validator = Validator::make(['settings' => $settings], [
                'settings' => 'required|array',
                'settings.*.key' => 'required|string|max:255',
                'settings.*.group' => 'required|string|max:255',
                'settings.*.type' => 'required|string|in:text,textarea,file,image,integer,float,boolean,select,multi-select,array,json',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', 'Invalid settings format: ' . $validator->errors()->first());
            }

            // Process the settings
            $imported = 0;
            $skipped = 0;
            $errors = [];

            foreach ($settings as $settingData) {
                try {
                    $key = $settingData['key'];
                    $existingSetting = Setting::where('key', $key)->first();

                    // Handle the import based on the mode
                    if ($existingSetting) {
                        if ($importMode === 'overwrite') {
                            // Update existing setting
                            $this->updateSettingFromImport($existingSetting, $settingData);
                            $imported++;
                        } else {
                            // In merge mode, skip existing settings
                            $skipped++;
                        }
                    } else {
                        // Create new setting
                        $this->createSettingFromImport($settingData);
                        $imported++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error importing setting '{$settingData['key']}': " . $e->getMessage();
                    Log::error("Settings import error for key {$settingData['key']}: " . $e->getMessage());
                }
            }

            $message = "Successfully imported {$imported} settings.";
            if ($skipped > 0) {
                $message .= " Skipped {$skipped} existing settings.";
            }
            if (!empty($errors)) {
                $message .= " Encountered " . count($errors) . " errors.";
            }

            return redirect()->route('system_settings.manage')
                ->with('success', $message)
                ->with('import_errors', $errors);
        } catch (\Exception $e) {
            Log::error('Settings import error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error importing settings: ' . $e->getMessage());
        }
    }

    /**
     * Try to fix common JSON syntax issues
     */
    private function sanitizeJsonContent($content)
    {
        // Remove BOM if present
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        // Remove comments (not valid in JSON but sometimes present)
        $content = preg_replace('!/\*.*?\*/!s', '', $content);
        $content = preg_replace('!//.*!', '', $content);

        // Remove trailing commas in arrays and objects (invalid in JSON)
        $content = preg_replace('/,\s*([\]}])/m', '$1', $content);

        return $content;
    }

    /**
     * Create a new setting from import data
     */
    private function createSettingFromImport($data)
    {
        $setting = new Setting();
        $setting->key = $data['key'];
        $setting->group = $data['group'];
        $setting->type = $data['type'];
        $setting->description = $data['description'] ?? null;
        $setting->is_visible = $data['is_visible'] ?? true;
        $setting->is_required = $data['is_required'] ?? true;
        $setting->is_disabled = $data['is_disabled'] ?? false;

        // Handle options
        if (isset($data['options'])) {
            if (is_string($data['options'])) {
                $setting->options = $data['options'];
            } else {
                $setting->options = json_encode($data['options']);
            }
        }

        // Handle value based on type
        if ($data['type'] === 'json' && isset($data['value'])) {
            if (is_string($data['value'])) {
                // Value is already a JSON string
                $setting->value = $data['value'];
            } else {
                // Value is an object, encode it
                $setting->value = json_encode($data['value']);
            }
        } else {
            $setting->value = $data['value'] ?? null;
        }

        $setting->save();
        return $setting;
    }

    /**
     * Update an existing setting from import data
     */
    private function updateSettingFromImport($setting, $data)
    {
        $setting->group = $data['group'];
        $setting->type = $data['type'];

        if (isset($data['description'])) {
            $setting->description = $data['description'];
        }

        if (isset($data['is_visible'])) {
            $setting->is_visible = $data['is_visible'];
        }

        if (isset($data['is_required'])) {
            $setting->is_required = $data['is_required'];
        }

        if (isset($data['is_disabled'])) {
            $setting->is_disabled = $data['is_disabled'];
        }

        // Handle options
        if (isset($data['options'])) {
            if (is_string($data['options'])) {
                $setting->options = $data['options'];
            } else {
                $setting->options = json_encode($data['options']);
            }
        }

        // Handle value based on type
        if (isset($data['value'])) {
            if ($data['type'] === 'json') {
                if (is_string($data['value'])) {
                    // Value is already a JSON string
                    $setting->value = $data['value'];
                } else {
                    // Value is an object, encode it
                    $setting->value = json_encode($data['value']);
                }
            } else {
                $setting->value = $data['value'];
            }
        }

        $setting->save();
        return $setting;
    }

    /**
     * Update multiple settings at once (bulk operations)
     */
    public function bulkUpdate(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'action' => 'required|in:visibility,group',
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:settings,id',
            ]);

            $action = $request->input('action');
            $ids = $request->input('ids');

            if (empty($ids)) {
                return redirect()->back()->with('error', 'No settings selected');
            }

            $settings = Setting::whereIn('id', $ids)->get();
            $count = count($settings);

            if ($action === 'visibility') {
                $request->validate(['visibility' => 'required|boolean']);
                $visibility = (bool)$request->input('visibility');

                foreach ($settings as $setting) {
                    $setting->is_visible = $visibility;
                    $setting->save();
                }

                $message = $visibility
                    ? "Made {$count} settings visible"
                    : "Made {$count} settings invisible";
            } elseif ($action === 'group') {
                $request->validate(['group' => 'required|string|max:255']);
                $group = $request->input('group');

                foreach ($settings as $setting) {
                    $setting->group = $group;
                    $setting->save();
                }

                $message = "Moved {$count} settings to the \"{$group}\" group";
            } else {
                return redirect()->back()->with('error', 'Invalid action');
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error("Bulk update error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating settings: ' . $e->getMessage());
        }
    }

    /**
     * Delete multiple settings at once
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:settings,id',
            ]);

            $ids = $request->input('ids');

            if (empty($ids)) {
                return redirect()->back()->with('error', 'No settings selected');
            }

            $count = Setting::whereIn('id', $ids)->count();
            Setting::whereIn('id', $ids)->delete();

            return redirect()->back()->with('success', "{$count} settings deleted successfully");
        } catch (\Exception $e) {
            Log::error("Bulk delete error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error deleting settings: ' . $e->getMessage());
        }
    }
}
