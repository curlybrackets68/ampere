<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\SystemLogs;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('auth.index');
    }

    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required',
        ]);

        $user = User::where($request->only('name', 'password'))->first();

        if ($user) {
            auth()->login($user);
            session([
                'user_id' => $user->id,
                'name' => $user->name,
                'user_name' => $user->user_name,
            ]);
            $this->generateSecretFile($user->id);
            SystemLogs::create([
                'inquiry_id' => 0,
                'type' => '0',
                'type_id' => $user->id,
                'remark'     => 'User Login ',
                'action_id'  => 4,
                'created_by' => auth()->id(),
            ]);
            return redirect()->route('dashboard')->withSuccess('Login success.');
        } else {
            return redirect()->back()->withInput()->withError('Invalid Credentials !!!');
        }
    }

    public function logout()
    {
        $secretPath = base_path('app/Secrets/');
        $userFile = $secretPath . '/'.Auth::id().'.php';
        if (File::exists($userFile)) {
            File::delete($userFile);
        }
        SystemLogs::create([
            'inquiry_id' => 0,
            'type' => '0',
            'type_id' => Auth::id(),
            'remark'     => 'User Logout',
            'action_id'  => 5,
            'created_by' => auth()->id(),
        ]);
        session()->flush();
        Auth::logout();
        return redirect()->route('auth.show-login');
    }

    function showAdminLogin()
    {
        return view('admin.auth.index');
    }
    function adminLogin(Request $request)
    {
        $request->validate([
            'user_name' => 'required',
            'password' => 'required',
        ]);

        $user = Admin::where($request->only('user_name', 'password'))->first();

        if ($user) {
            Auth::guard('admin')->login($user);
            return redirect()->route('admin.dashboard')->withSuccess('Login success.');
        } else {
            return redirect()->back()->withInput()->withError('Invalid Credentials !!!');
        }
    }

    public function adminLogout()
    {
        Auth::logout();
        return redirect()->route('admin-login');
    }

}
