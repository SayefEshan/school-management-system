<?php

namespace Modules\RolePermission\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use App\Enum\PaginationEnum;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Modules\RolePermission\Services\RolePermissionService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class RolePermissionController extends Controller
{
    protected $roleSerivce;

    public function __construct()
    {
        $this->roleSerivce = new RolePermissionService();

        $this->middleware(['can:View Role'])->only(['index', 'show', 'permissionMatrix']);
        $this->middleware(['can:Create Role'])->only(['create', 'store', 'cloneRole']);
        $this->middleware(['can:Edit Role'])->only(['edit', 'update']);
        $this->middleware(['can:Assign Permission'])->only(['assignPermissionPage', 'assignPermissionStore', 'managePermissions', 'deletePermission', 'syncPermissions', 'createPermission', 'bulkDeletePermissions', 'getPermissionRoles', 'updatePermissionMatrix']);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $roles = Role::withCount('permissions');

        if ($request->filled('name')) {
            $roles->where('name', 'LIKE', '%' . $request->name . '%');
        }

        $roles = $roles->paginate($request->per_page ?? PaginationEnum::DEFAULT_PAGINATE);

        return view('rolepermission::index', [
            'roles' => $roles
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('rolepermission::create', [
            'role_prefix' => "System",
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Factory|\Illuminate\Contracts\View\View|Application|JsonResponse|RedirectResponse|View
     */
    public function store(Request $request)
    {
        $role_name = $request->role_name;

        $request->merge([
            'role_name' => $role_name,
        ]);

        $validator = Validator::make($request->all(), [
            'role_name' => 'required|unique:roles,name',
        ], [
            'role_name.unique' => 'Role name already taken',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $this->roleSerivce->createOrUpdateRole($request, 'System');
            return redirect()->route('role.index')->with('success', 'Role created successfully');
        } catch (Exception $e) {
            Log::error($e);
            return redirect()->route('role.index')->with('error', 'Internal Server Error');
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('rolepermission::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('rolepermission::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Factory|\Illuminate\Contracts\View\View|Application|RedirectResponse|View
     */
    public function update(Request $request, $id)
    {
        $role_name = $request->role_name;

        $request->merge([
            'role_name' => $role_name,
        ]);

        $validator = Validator::make($request->all(), [
            'role_name' => 'required|unique:roles,name,' . $id,
        ], [
            'role_name.unique' => 'Role name already taken',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $this->roleSerivce->createOrUpdateRole($request, 'System', $id);
            return redirect()->route('role.index')->with('success', 'Role updated successfully');
        } catch (Exception $e) {
            Log::error($e);
            return redirect()->route('role.index')->with('error', 'Internal Server Error');
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function assignPermissionPage($id)
    {
        $role = Role::where('id', $id)->first();
        if (!$role) {
            return redirect()->back()->with('error', 'Invalid Role');
        }

        $all_permissions = Permission::select('id', 'name', 'module_name')
            ->orderBy('id', 'asc')
            ->get()
            ->groupBy('module_name');

        return view('rolepermission::assign_permission', [
            'role' => $role,
            'all_permissions' => $all_permissions,
        ]);
    }

    public function assignPermissionStore(Request $request, $id): ?RedirectResponse
    {
        try {
            $this->roleSerivce->assignPermissionToRole($request, $id);
            return redirect()->back()->with('success', 'Permission assigned successfully');
        } catch (Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'Internal server error');
        }
    }

    /**
     * Display the permission management page
     * @param Request $request
     * @return Renderable
     */
    public function managePermissions(Request $request)
    {
        $permissionsQuery = Permission::select('id', 'name', 'module_name', 'description')
            ->withCount('roles')
            ->orderBy('module_name', 'asc')
            ->orderBy('name', 'asc');

        // Apply filters if provided
        if ($request->filled('name')) {
            $permissionsQuery->where('name', 'LIKE', '%' . $request->name . '%');
        }

        if ($request->filled('module')) {
            $permissionsQuery->where('module_name', $request->module);
        }

        // Get paginated permissions
        $permissions = $permissionsQuery->paginate($request->per_page ?? PaginationEnum::DEFAULT_PAGINATE);

        // Get all unique module names for the filter dropdown
        $modules = Permission::select('module_name')->distinct()->pluck('module_name');

        return view('rolepermission::manage_permissions', [
            'permissions' => $permissions,
            'modules' => $modules
        ]);
    }

    /**
     * Delete a permission
     * @param int $id
     * @return RedirectResponse
     */
    public function deletePermission($id)
    {
        try {
            $permission = Permission::find($id);

            if (!$permission) {
                return redirect()->back()->with('error', 'Permission not found');
            }

            // Check if the permission is assigned to any role
            $roles = Role::with('permissions')->get();
            foreach ($roles as $role) {
                if ($role->hasPermissionTo($permission->name)) {
                    $role->revokePermissionTo($permission->name);
                }
            }

            $permission->delete();
            return redirect()->back()->with('success', 'Permission deleted successfully');
        } catch (Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'Error deleting permission: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete permissions
     * @param Request $request
     * @return RedirectResponse
     */
    public function bulkDeletePermissions(Request $request)
    {
        try {
            $permissionIds = $request->permission_ids;

            if (!$permissionIds || count($permissionIds) === 0) {
                return redirect()->back()->with('error', 'No permissions selected');
            }

            $permissions = Permission::whereIn('id', $permissionIds)->get();

            // Revoke the permissions from all roles that have them
            $roles = Role::with('permissions')->get();

            foreach ($permissions as $permission) {
                foreach ($roles as $role) {
                    if ($role->hasPermissionTo($permission->name)) {
                        $role->revokePermissionTo($permission->name);
                    }
                }
                $permission->delete();
            }

            return redirect()->back()->with('success', count($permissionIds) . ' permissions deleted successfully');
        } catch (Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'Error deleting permissions: ' . $e->getMessage());
        }
    }

    /**
     * Create a new permission
     * @param Request $request
     * @return RedirectResponse
     */
    public function createPermission(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'permission_name' => 'required|unique:permissions,name',
                'module_name' => 'required_without:new_module_name',
                'new_module_name' => 'required_if:module_name,new',
            ], [
                'permission_name.unique' => 'This permission name already exists',
                'module_name.required_without' => 'Please select a module or create a new one',
                'new_module_name.required_if' => 'Please enter a name for the new module',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $moduleName = $request->module_name;

            // If creating a new module
            if ($moduleName === 'new' && $request->filled('new_module_name')) {
                $moduleName = $request->new_module_name;
            }

            // Create the permission
            Permission::create([
                'name' => $request->permission_name,
                'module_name' => $moduleName,
                'guard_name' => 'web',
                'description' => $request->description,
            ]);

            return redirect()->back()->with('success', 'Permission created successfully');
        } catch (Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'Error creating permission: ' . $e->getMessage());
        }
    }

    /**
     * Get roles that have a specific permission
     * @param int $id
     * @return JsonResponse
     */
    public function getPermissionRoles($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            $roles = $permission->roles()->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'roles' => $roles
            ]);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving roles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync permissions by running the PermissionSeeder
     * @return RedirectResponse
     */
    public function syncPermissions()
    {
        try {
            // Reset cached roles and permissions
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            // Run the PermissionSeeder with force flag to work in production
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\PermissionSeeder',
                '--force' => true // Add force flag to bypass production warning
            ]);

            return redirect()->back()->with('success', 'Permissions synced successfully');
        } catch (Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'Error syncing permissions: ' . $e->getMessage());
        }
    }

    /**
     * Clone a role with all its permissions
     * @param int $id
     * @return RedirectResponse
     */
    public function cloneRole($id)
    {
        try {
            $sourceRole = Role::findOrFail($id);
            $permissions = $sourceRole->permissions()->pluck('name')->toArray();

            // Create a new role with a unique name
            $newRoleName = $sourceRole->name . ' - Copy';
            $counter = 1;

            // Ensure the new role name is unique
            while (Role::where('name', $newRoleName)->exists()) {
                $counter++;
                $newRoleName = $sourceRole->name . ' - Copy ' . $counter;
            }

            // Create the new role
            $newRole = Role::create([
                'name' => $newRoleName,
                'guard_name' => 'web'
            ]);

            // Assign the same permissions
            $newRole->syncPermissions($permissions);

            return redirect()->route('role.index')->with('success', 'Role cloned successfully as "' . $newRoleName . '"');
        } catch (Exception $e) {
            Log::error($e);
            return redirect()->route('role.index')->with('error', 'Error cloning role: ' . $e->getMessage());
        }
    }

    /**
     * Display the permission matrix view
     * @param Request $request
     * @return Renderable
     */
    public function permissionMatrix(Request $request)
    {
        // Get roles with pagination
        $rolesQuery = Role::orderBy('name', 'asc');
        $roles = $rolesQuery->paginate($request->per_page ?? 10);

        // Get all permissions, optionally filtered by module
        $permissionsQuery = Permission::select('id', 'name', 'module_name', 'description')
            ->orderBy('module_name', 'asc')
            ->orderBy('name', 'asc');

        if ($request->filled('module')) {
            $permissionsQuery->where('module_name', $request->module);
        }

        $permissions = $permissionsQuery->get();
        $groupedPermissions = $permissions->groupBy('module_name');

        // Get all unique module names for the filter
        $modules = Permission::select('module_name')->distinct()->pluck('module_name');

        // Build a lookup array of role permissions
        $rolePermissions = [];
        foreach ($roles as $role) {
            $rolePermissions[$role->id] = [];
            $rolePermissionIds = $role->permissions->pluck('id')->toArray();

            foreach ($permissions as $permission) {
                $rolePermissions[$role->id][$permission->id] = in_array($permission->id, $rolePermissionIds);
            }
        }

        return view('rolepermission::matrix', [
            'roles' => $roles,
            'groupedPermissions' => $groupedPermissions,
            'rolePermissions' => $rolePermissions,
            'modules' => $modules
        ]);
    }

    /**
     * Update permissions from the matrix view
     * @param Request $request
     * @return RedirectResponse
     */
    public function updatePermissionMatrix(Request $request)
    {
        try {
            $matrix = $request->matrix ?? [];

            DB::beginTransaction();

            foreach ($matrix as $roleId => $permissions) {
                $role = Role::findOrFail($roleId);

                // Get the IDs of the permissions that should be assigned to this role
                $permissionIds = array_keys($permissions);

                // Sync the role's permissions
                $role->permissions()->sync($permissionIds);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Permissions updated successfully');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return redirect()->back()->with('error', 'Error updating permissions: ' . $e->getMessage());
        }
    }
}
