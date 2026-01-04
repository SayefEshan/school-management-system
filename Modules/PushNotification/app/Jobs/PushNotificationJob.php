<?php

namespace Modules\PushNotification\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Modules\PushNotification\Notifications\PushNotification;

class PushNotificationJob implements ShouldQueue
{
    use Queueable;

    protected string $title;
    protected string $body;
    protected ?array $data;

    /**
     * Create a new job instance.
     */
    public function __construct(string $title, string $body, ?array $data)
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        set_time_limit(0);

        try {
            $data = [
                'title' => $this->title,
                'body' => $this->body,
                'data' => $this->data,
            ];
            $users = User::all();
            $users->each(function ($user) use ($data) {
                $user->notify(new PushNotification($data['title'], $data['body'], $data['data'] ?? null));
            });
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
    }
}
