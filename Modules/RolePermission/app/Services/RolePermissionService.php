<?php

namespace Modules\RolePermission\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class RolePermissionService
{

    public function createOrUpdateRole($request, $type, $id = false)
    {
        try {
            if ($id) {
                $role = Role::where('id', '=', $id);

                $role = $role->first();


            } else {
                $role = new Role();
            }
            $role->name = $request->role_name;

            if ($id) {
                $role->update();
            } else {
                $role->save();
            }

//            $activity_message_type = $id ? "activity_massages.update" : "activity_massages.create";
//            $description = __($activity_message_type, ['user' => \auth()->user()->name, 'details' => "Role", 'time' => Carbon::now()->isoFormat('h:mm:ss a, MMMM Do YYYY')]);
//            ActivityLogs::createActivityLog($id ? 'Role Update' : 'Role Create', ActivityLogActionTypeEnum::User_Involved_Action, 'Role', $role->id, $description);


        } catch (Exception $e) {
            Log::error($e);
            throw new Exception($e->getMessage());
        }
    }

    public function assignPermissionToRole($request, $id)
    {
        try {

            $role = Role::where('id', $id)->first();
            if (!$role) {
                throw new Exception('Invalid Role , Role not found!');
            }
            $permissions = [];
            if ($request->has('permission')) {
                foreach ($request->permission as $key => $value) {
                    $permissions[] = $key;
                }
            }

            DB::beginTransaction();
            $role->syncPermissions($permissions);
            DB::commit();

//            $activity_message = "Permission assign to role " . $role->name;
//            $description = __('activity_massages.common', ['user' => \auth()->user()->name, 'details' => $activity_message, 'time' => Carbon::now()->isoFormat('h:mm:ss a, MMMM Do YYYY')]);
//            ActivityLogs::createActivityLog("Permission assign", ActivityLogActionTypeEnum::User_Involved_Action, 'Role', $role->id, $description);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            throw new Exception($e->getMessage());
        }
    }

}
