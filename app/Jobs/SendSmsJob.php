<?php

namespace App\Jobs;

use App\Models\SmsLog;
use App\Traits\MyGuzzleClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendSmsJob implements ShouldQueue
{
    use Queueable, MyGuzzleClient;

    protected string $message;
    protected string $phone;

    /**
     * Create a new job instance.
     */
    public function __construct(string $message, string $phone)
    {
        $this->message = $message;
        $this->phone = $phone;
    }

    private function getSelectedSmsGateway(): ?array
    {
        $smsGateways = getSystemSetting('sms_gateways');
        if (is_array($smsGateways) === false) {
            return null;
        }
        $selectedSmsGateway = getSystemSetting('sms_gateway');
        foreach ($smsGateways as $gateway) {
            if ($gateway['TYPE'] === $selectedSmsGateway) {
                return $gateway;
            }
        }
        return null;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $smsSetting = $this->getSelectedSmsGateway();
            if ($smsSetting === null) {
                Log::channel('daily_sms')->error("SMS Gateway not found: " . json_encode(['status' => 'failed', 'phone' => $this->phone, 'message' => $this->message], JSON_THROW_ON_ERROR));
                return;
            }

            if ($smsSetting['TYPE'] === 'LOG') {
                Log::channel('daily_sms')->info("Local SMS LOG: " . json_encode(['status' => 'success', 'phone' => $this->phone, 'message' => $this->message]));
                SmsLog::create([
                    'status' => 'success',
                    'phone' => $this->phone,
                    'message' => $this->message,
                    'response' => 'LOG DRIVER'
                ]);
                return;
            }

            $smsSetting = $smsSetting['VALUE'];

            $url = $smsSetting['endpoint'] ?? '';
            $method = $smsSetting['method'] ?? '';
            $mobileKey = $smsSetting['mobile_key'] ?? '';
            $messageKey = $smsSetting['message_key'] ?? '';
            $extraParams = $smsSetting['params'] ?? [];
            $headers = $smsSetting['headers'] ?? [];

            if (!empty($smsSetting['mobile_prefix'])) {
                $this->phone = $smsSetting['mobile_prefix'] . $this->phone;
            }

            if (empty($url) || empty($method) || empty($mobileKey) || empty($messageKey)) {
                Log::channel('daily_sms')->error("SMS Gateway settings are not correct: " . json_encode(['status' => 'failed', 'phone' => $this->phone, 'message' => $this->message], JSON_THROW_ON_ERROR));
                return;
            }
        } catch (\Exception $e) {
            Log::channel('daily_sms')->error("Error Getting SMS Settings: " . $e->getMessage() . " Data: Message: " . $this->message . " Phone: " . $this->phone);
            return;
        }

        try {
            $params = [];
            $params[$mobileKey] = $this->phone;
            $params[$messageKey] = $this->message;
            foreach ($extraParams as $key => $value) {
                if ($key === 'csms_id') { // csms_id is a random string due to ssl wireless sms gateway
                    $params[$key] = 'app_' . Str::random(10);
                } else {
                    $params[$key] = $value;
                }
            }

            $header_data = [];
            // Add headers from SMS gateway settings
            if (!empty($headers) && is_array($headers)) {
                foreach ($headers as $key => $value) {
                    $header_data[$key] = $value;
                }
            }

            $result = $method === 'POST'
                ? $this->guzzle_post_call($params, $url, $header_data, $params)
                : $this->guzzle_get_call($url, $header_data, $params);
        } catch (\Exception $e) {
            Log::channel('daily_sms')->error("Error while sending sms: " . $e->getMessage() . " Data: Message: " . $this->message . " Phone: " . $this->phone);
            return;
        }

        try {
            $data = [
                'status' => $result === false ? 'failed' : 'success',
                'phone' => $this->phone,
                'message' => $this->message,
                'response' => json_encode($result, JSON_THROW_ON_ERROR),
            ];
            $smsData = SmsLog::create($data);
        } catch (\Exception $e) {
            Log::channel('daily_sms')->error("Error while saving sms log: " . $e->getMessage() . " Data: Message: " . $this->message . " Phone: " . $this->phone);
        }

        Log::channel('daily_sms')->info("SMS sent successfully: " . json_encode($smsData ?? ($result ?? null), JSON_THROW_ON_ERROR));
    }
}
