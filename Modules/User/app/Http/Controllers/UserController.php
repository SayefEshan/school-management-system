<?php

namespace Modules\User\Http\Controllers;

use App\Enum\PaginationEnum;
use App\Models\User;
use App\Services\FileManagerService;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\ImportDownloadManager\Service\DownloadImportService;
use Modules\User\Actions\CreateUserAction;
use Modules\User\Actions\UpdateUserAction;
use Modules\User\Actions\ResetPasswordAction;
use Modules\User\Actions\ManageUserAccountAction;
use Modules\User\Actions\UploadUserDocumentAction;

use Modules\User\Data\UserData;
use Modules\User\Data\UserDocumentData;
use Modules\User\Jobs\UserExportJob;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['can:View User'])->only(['index', 'show']);
        $this->middleware(['can:Create User'])->only(['create', 'store']);
        $this->middleware(['can:Edit User'])->only(['edit', 'update']);
        $this->middleware(['can:Delete User'])->only(['destroy', 'manageAccount']);
        $this->middleware(['can:User Password Reset'])->only(['resetPassword']);

    }

    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index(Request $request)
    {
        $query = User::with([
            'roles',
        ]);
        
        // Filter by role
        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('id', $request->role);
            });
        }
        

        
        $users = $query->filter($request->all())
            ->orderBy('id', 'desc')
            ->paginate($request->per_page ?? PaginationEnum::USER_LIST);

        $roles = Role::pluck('name', 'id');

        return view('user::user.index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     */
    public function create()
    {
        $roles = Role::all()->pluck('name', 'id');

        return view('user::user.create', ['roles' => $roles]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return RedirectResponse
     */
    public function store(UserData $userData, CreateUserAction $action)
    {
        $user = request()->user();

        if (!$user->can('Assign Permission')) {
            return redirect()->back()->with('error', 'You do not have assign role permission');
        }

        try {
            // Gender normalization
            if ($userData->gender) {
                $userData->gender = strtolower($userData->gender);
            }

            // Create user
            $newUser = $action->execute($userData);
            


            return redirect()->route('admin.users.index')->with('success', 'User and profiles created successfully');
        } catch (Exception $e) {
            Log::error('user creation error' . $e->getMessage());

            return redirect()->back()->with('error', 'Internal server error');
        }
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function show($id)
    {
        $user = User::with([
            'documents',
            'roles',
        ])->findOrFail($id);
        
        $isModal = request('isModal', false);
        $view = $isModal ? 'user::user.modals.user-view' : 'user::user.view';

        return view($view, compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return RedirectResponse|View
     */
    public function edit(int $id)
    {
        $roles = Role::all()->pluck('name', 'id');
        $user = User::with([
            'roles',
        ])->find($id);

        if (! $user) {
            return redirect()->back()->with('error', 'User Not found');
        }

        return view('user::user.edit', [
            'roles' => $roles,
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return RedirectResponse
     */
    public function update(Request $request, int $id, UpdateUserAction $action)
    {
        // Manual validation using UserData rules with the specific user ID
        $data = $request->validate(UserData::rulesForUpdate($id));
        
        $editableUser = User::findOrFail($id);
        $originalRoles = $editableUser->roles->pluck('id')->toArray();
        $newRoles = $data['roles'] ?? $originalRoles;
        
        if (array_diff($originalRoles, $newRoles) || array_diff($newRoles, $originalRoles)) {
            if (!$request->user()->can('Assign Permission')) {
                return redirect()->back()->with('error', 'You do not have assign role permission');
            }
        }

        try {
            // Preprocess data for DTO
            $data['password'] = null; // Never update password via this endpoint
            if (isset($data['gender'])) {
                $data['gender'] = strtolower($data['gender']);
            }
            if (!isset($data['is_active'])) {
                $data['is_active'] = $editableUser->is_active;
            }

            $userData = UserData::from($data);
            $user = $action->execute($id, $userData);
            


            return redirect()->back()->with('success', 'User and profiles updated successfully');
        } catch (Exception $e) {
            Log::error('user validation error' . $e->getMessage());

            return redirect()->back()->with('error', 'Internal Server Error');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        return redirect()->back()->with('error', 'User Deletion is disabled');
    }

    /**
     * Reset user password
     */
    public function resetPassword($id, ResetPasswordAction $action): ?RedirectResponse
    {
        try {
            $user = User::findOrFail($id);

            $password = $action->execute($user);

            return redirect()->back()->with('success', 'New password ' . $password . ' sent to user email');
        } catch (Exception $e) {
            Log::error($e);

            return redirect()->back()->with('error', 'Internal Server Error');
        }
    }

    /**
     * Manage a user account - reset or delete (only available in non-production environments)
     */
    public function manageAccount(Request $request, int $id, ManageUserAccountAction $action): RedirectResponse
    {
        if (app()->environment('production')) {
            return redirect()->back()->with('error', 'Account management is not available in production environment');
        }

        $validatedData = $request->validate([
            'action' => 'required|in:reset,delete',
        ]);

        $actionType = $validatedData['action'];

        try {
            $user = User::findOrFail($id);

            $result = $action->execute($user, $actionType);

            if ($result) {
                $message = $actionType === 'reset'
                    ? 'User account has been reset successfully'
                    : 'User account has been deleted successfully';

                return redirect()->back()->with('success', $message);
            }

            return redirect()->back()->with('error', 'Failed to ' . $actionType . ' user account');
        } catch (Exception $e) {
            Log::error('Error managing user account: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Internal server error');
        }
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $user = User::findOrFail($id);
        $user->update(['is_active' => $request->is_active]);

        return redirect()->back()->with('success', 'User status updated successfully');
    }

    public function bulkUpload(Request $request): ?RedirectResponse
    {
        $data = $request->validate([
            'users' => 'required|file|mimes:xlsx,xls',
        ]);
        try {
            $file_url = FileManagerService::uploadFile($data['users'], directory: 'uploads/users');
            $importManagerId = DownloadImportService::create(Auth::user(), 'Users Upload', 'Import', $file_url);

            return redirect()->route('download.import.manager.index')->with('success', 'User bulk upload started');
        } catch (Exception $e) {
            Log::error('user bulk upload error' . $e->getMessage());

            return redirect()->back()->with('error', 'Internal server error');
        }
    }

    public function export(Request $request): ?RedirectResponse
    {
        try {
            $importManagerId = DownloadImportService::create(Auth::user(), 'Users Export', 'Download');
            UserExportJob::dispatch($importManagerId, $request->all(), Auth::user());

            return redirect()->route('download.import.manager.index')->with('success', 'Report sent to process');
        } catch (Exception $e) {
            Log::error($e);

            return redirect()->back()->withErrors("Couldn't sent to process");
        }
    }
    public function uploadDocument(Request $request, User $user, UploadUserDocumentAction $action): RedirectResponse
    {        
        try {
            $data = UserDocumentData::from($request);
            $action->execute($user, $data);
            return redirect()->back()->with('success', 'Document uploaded successfully');
        } catch (Exception $e) {
            Log::error('document upload error' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to upload document');
        }
    }


}
