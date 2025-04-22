<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\User;
use App\Models\UserRight;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminController extends Controller
{
    function dashboard()
    {
        return view("admin.dashboard");
    }

    function modules(Request $request)
    {
        if ($request->ajax()) {

            $modules = Module::query();

            return DataTables::of($modules)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-sm btn-primary edit-module" data-id="' . $row->id . '" data-name="' . $row->name . '" data-config="' . $row->config_key . '">Edit</button>';
                })
                ->make(true);
        }


        return view("admin.modules");
    }

    function addEditModule(Request $request)
    {
        $moduleId = $request->moduleId;
        $moduleName = $request->moduleName;
        $configKey = $request->configKey;

        $data = [
            'name' => $moduleName,
            'config_key' => $configKey,
            'status' => 1
        ];

        $save = Module::updateOrCreate(['id' => $moduleId], $data);
        if ($save) {
            $msg = $moduleId ? 'Module Edit' : 'Module Add';
            return response()->json(['status' => 1, 'msg' => $msg . ' Successfully']);
        } else {
            return response()->json(['status' => 0, 'msg' => '']);
        }
    }

    function userList(Request $request)
    {
        if ($request->ajax()) {
            $users = User::query();

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-sm btn-primary edit-user" data-id="' . $row->id . '">Edit</button>
                        <a href="' . route('admin.rights', ['id' => $row->id]) . '" class="btn btn-sm btn-success">Assign</a>
                    ';
                })
                ->make(true);
        }

        return view('admin.users');
    }

    function addEditUser(Request $request)
    {
        $userId = $request->userId;
        $fullName = $request->fullName;
        $shortName = $request->shortName;
        $password = $request->password;
        $mobile = $request->mobile;
        $email = $request->email;

        $data = [
            'user_name' => $fullName,
            'name' => $shortName,
            'mobile' => $mobile,
            'email' => $email ?? '',
            'password' => $password
        ];

        $save = User::updateOrCreate(['id' => $userId], $data);

        if ($save) {
            $msg = $userId ? 'User Edit' : 'User Add';
            return response()->json(['status' => 1, 'msg' => $msg . ' Successfully']);
        } else {
            return response()->json(['status' => 0, 'msg' => 'Something went wrong']);
        }
    }

    function editUser($id)
    {
        $user = User::find($id);
        return response()->json($user);
    }

    function userRights($id)
    {
        $user = User::find($id);
        $rights = UserRight::where('user_id', $id)->get()->keyBy('module_id');
        $modules = Module::where('status', 1)->get();
        return view('admin.rights')->with(compact('user', 'rights', 'modules'));
    }

    function saveUserRights(Request $request)
    {
        $userId = $request->user_id;
        $permissions = $request->permissions;

        if (!empty($permissions)) {
            UserRight::where('user_id', $userId)->delete();

            $modulePermissions = [];

            foreach ($permissions as $value) {
                $rights = explode('_', $value);
                $action = $rights[0];
                $moduleId = $rights[1];

                if (!isset($modulePermissions[$moduleId])) {
                    $modulePermissions[$moduleId] = [
                        'user_id' => $userId,
                        'module_id' => $moduleId,
                        'role_add' => 0,
                        'role_edit' => 0,
                        'role_delete' => 0,
                        'role_view' => 0,
                        'role_viewAll' => 0,
                    ];
                }

                $columnName = 'role_' . $action;
                $modulePermissions[$moduleId][$columnName] = 1;
            }

            foreach ($modulePermissions as $data) {
                UserRight::create($data);
            }
        }

        return redirect()->route('admin.users')->withSuccess("Rights Assigned Successfully");
    }
}
