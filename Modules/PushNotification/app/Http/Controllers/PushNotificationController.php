<?php

namespace Modules\PushNotification\Http\Controllers;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\PushNotification\Jobs\PushNotificationJob;
use Modules\PushNotification\Models\PushNotification;

class PushNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $notifications = PushNotification::query();
        $notifications = $notifications->orderBy('id', 'desc')->paginate($request->per_page ?? PaginationEnum::DEFAULT_PAGINATE);
        return view('pushnotification::push-notification.index', compact('notifications'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pushnotification::push-notification.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'data' => 'nullable|array',
        ]);

        PushNotification::create($data);

        PushNotificationJob::dispatch($data['title'], $data['body'], $data['data'] ?? null);
        return redirect()->route('push.notification.index')->with('success', 'Push notification created successfully.');
    }
}
