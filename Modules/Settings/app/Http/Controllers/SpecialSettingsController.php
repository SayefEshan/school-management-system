<?php

namespace Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\SendSmsJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Modules\Settings\Models\Setting;

class SpecialSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['can:Edit Special Setting'])->only([
            'privacyPolicy',
            'updatePrivacyPolicy',
            'smsGateways',
            'updateSmsGateways',
            'sendTestSMS',
            'emailMailers',
            'updateEmailMailers',
            'sendTestEmail'
        ]);
    }

    /**
     * Display the privacy policy settings form
     */
    public function privacyPolicy()
    {
        $setting = Setting::where('key', 'privacy_policy')->first();
        return view('settings::special.privacy-policy', compact('setting'));
    }

    /**
     * Update the privacy policy setting
     */
    public function updatePrivacyPolicy(Request $request): RedirectResponse
    {
        $request->validate([
            'privacy_policy' => 'required|string',
        ]);

        $setting = Setting::where('key', 'privacy_policy')->first();
        if ($setting) {
            $setting->value = $request->input('privacy_policy');
            $setting->save();
        }

        return redirect()->back()->with('success', 'Privacy Policy updated successfully');
    }

    /**
     * Display the SMS gateways settings form
     */
    public function smsGateways()
    {
        $smsGateways = Setting::where('key', 'sms_gateways')->first();
        $smsGateway = Setting::where('key', 'sms_gateway')->first();

        return view('settings::special.sms-gateways', compact('smsGateways', 'smsGateway'));
    }

    /**
     * Update the SMS gateways settings
     */
    public function updateSmsGateways(Request $request): RedirectResponse
    {
        try {
            // Update sms_gateways setting
            $smsGateways = Setting::where('key', 'sms_gateways')->first();
            if ($smsGateways) {
                $gateways = $request->input('sms_gateways');

                // Process the parameters to convert keys and values arrays to associative arrays
                $gatewaysArray = [];
                foreach ($gateways as &$gateway) {
                    // Handle params conversion
                    if (isset($gateway['VALUE']['params']['keys']) && isset($gateway['VALUE']['params']['values'])) {
                        $keys = $gateway['VALUE']['params']['keys'];
                        $values = $gateway['VALUE']['params']['values'];

                        // Create an associative array from the keys and values
                        $params = [];
                        for ($i = 0; $i < count($keys); $i++) {
                            if (isset($keys[$i]) && $keys[$i] !== '') {
                                $params[$keys[$i]] = $values[$i] ?? '';
                            }
                        }

                        // Replace the keys and values arrays with the associative array
                        $gateway['VALUE']['params'] = $params;
                    }

                    // Handle headers conversion
                    if (isset($gateway['VALUE']['headers']['keys']) && isset($gateway['VALUE']['headers']['values'])) {
                        $keys = $gateway['VALUE']['headers']['keys'];
                        $values = $gateway['VALUE']['headers']['values'];

                        // Create an associative array from the keys and values
                        $headers = [];
                        for ($i = 0; $i < count($keys); $i++) {
                            if (isset($keys[$i]) && $keys[$i] !== '') {
                                $headers[$keys[$i]] = $values[$i] ?? '';
                            }
                        }

                        // Replace the keys and values arrays with the associative array
                        $gateway['VALUE']['headers'] = $headers;
                    }

                    // Ensure required structure and add to array
                    if (isset($gateway['TYPE']) && isset($gateway['VALUE'])) {
                        $gatewaysArray[] = $gateway;
                    }
                }

                $smsGateways->value = json_encode($gatewaysArray);
                $smsGateways->save();
            }

            // Update sms_gateway setting (selected gateway)
            $smsGateway = Setting::where('key', 'sms_gateway')->first();
            if ($smsGateway) {
                $smsGateway->value = $request->input('sms_gateway');
                $smsGateway->save();
            }

            return redirect()->back()->with('success', 'SMS Gateways updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating SMS Gateways: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating SMS Gateways: ' . $e->getMessage());
        }
    }

    /**
     * Display the email mailers settings form
     */
    public function emailMailers()
    {
        $emailMailers = Setting::where('key', 'email_mailers')->first();
        $emailMailer = Setting::where('key', 'email_mailer')->first();

        return view('settings::special.email-mailers', compact('emailMailers', 'emailMailer'));
    }

    /**
     * Update the email mailers settings
     */
    public function updateEmailMailers(Request $request): RedirectResponse
    {
        try {
            // Update email_mailers setting
            $emailMailers = Setting::where('key', 'email_mailers')->first();
            if ($emailMailers) {
                $mailers = $request->input('email_mailers');

                // Convert to array format (removing numeric keys)
                $mailersArray = [];
                foreach ($mailers as $mailer) {
                    // Ensure required structure
                    if (isset($mailer['TYPE']) && isset($mailer['VALUE'])) {
                        $mailersArray[] = $mailer;
                    }
                }

                $emailMailers->value = json_encode($mailersArray);
                $emailMailers->save();
            }

            // Update email_mailer setting (selected mailer)
            $emailMailer = Setting::where('key', 'email_mailer')->first();
            if ($emailMailer) {
                $emailMailer->value = $request->input('email_mailer');
                $emailMailer->save();
            }

            return redirect()->back()->with('success', 'Email Mailers updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating Email Mailers: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating Email Mailers: ' . $e->getMessage());
        }
    }

    /**
     * Send a test SMS
     */
    public function sendTestSMS(Request $request)
    {
        $data = $request->validate([
            'mobile_no' => ['required']
        ]);

        $phone = $data['mobile_no'];
        $message = 'This is a test message from ' . config('app.name');
        SendSmsJob::dispatch($message, $phone);

        return response()->json(['message' => 'SMS Job executed successfully. See logs for more details.']);
    }

    /**
     * Send a test email
     */
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
}
