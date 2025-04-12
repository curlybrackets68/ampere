<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\User;
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
                    return '<button class="btn btn-sm btn-primary edit-user" data-id="' . $row->id . '">Edit</button>';
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
            'email' => $email,
        ];

        if (!empty($password)) {
            $data['password'] = $password;
        }

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
}
