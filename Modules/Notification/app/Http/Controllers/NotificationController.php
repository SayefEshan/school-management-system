<?php

namespace Modules\Notification\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Notification\Models\Notification;
use Modules\Notification\Http\Resources\NotificationResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\View\View|JsonResponse|AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id);

        if ($request->has('read')) {
            $isRead = filter_var($request->input('read'), FILTER_VALIDATE_BOOLEAN);
            if ($isRead) {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        if ($request->wantsJson() || $request->is('api/*')) {
            return apiResponse(true, 'Notifications fetched successfully', NotificationResource::collection($notifications));
        }

        return view('notification::index', compact('notifications'));
    }

    /**
     * Show the specified resource.
     *
     * @param string $id
     * @return \Illuminate\View\View|JsonResponse
     */
    public function show($id)
    {
        $notification = Notification::findOrFail($id);

        // Mark notification as read when viewed
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        if (request()->wantsJson() || request()->is('api/*')) {
            return apiResponse(true, 'Notification fetched successfully', new NotificationResource($notification));
        }

        $isModal = request('isModal', false);
        $view = $isModal ? 'notification::modals.notification-view' : 'notification::show';

        return view($view, compact('notification'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse|JsonResponse
     */
    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        if (request()->wantsJson() || request()->is('api/*')) {
            return apiResponse(true, 'Notification deleted successfully');
        }

        return redirect()->route('notification.index')
            ->with('success', 'Notification deleted successfully');
    }

    /**
     * Mark the specified notification as read.
     *
     * @param string $id
     * @return JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->markAsRead();

        if (request()->wantsJson() || request()->is('api/*')) {
            return apiResponse(true, 'Notification marked as read', new NotificationResource($notification));
        }

        return redirect()->back()->with('success', 'Notification marked as read');
    }

    /**
     * Mark the specified notification as unread.
     *
     * @param string $id
     * @return JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function markAsUnread($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->markAsUnread();

        if (request()->wantsJson() || request()->is('api/*')) {
            return apiResponse(true, 'Notification marked as unread', new NotificationResource($notification));
        }

        return redirect()->back()->with('success', 'Notification marked as unread');
    }

    /**
     * Mark all notifications as read.
     *
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();

        Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if (request()->wantsJson() || request()->is('api/*')) {
            return apiResponse(true, 'All notifications marked as read');
        }

        return redirect()->back()->with('success', 'All notifications marked as read');
    }

    /**
     * Get notification counts.
     *
     * @return JsonResponse
     */
    public function counts(): JsonResponse
    {
        $user = Auth::user();
        $counts = [
            'total' => Notification::where('notifiable_type', get_class($user))
                ->where('notifiable_id', $user->id)
                ->count(),
            'unread' => Notification::where('notifiable_type', get_class($user))
                ->where('notifiable_id', $user->id)
                ->whereNull('read_at')
                ->count(),
        ];

        return apiResponse(true, 'Notification counts fetched successfully', $counts);
    }
}
