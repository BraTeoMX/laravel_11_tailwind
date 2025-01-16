<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Log;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /** index login page */
    public function login()
    {
        return view('auth.login');
    }

    /** login page to check database table users */
    public function authenticate(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);
        try {
            $username = $request->email;
            $password = $request->password;

            $dt         = Carbon::now();
            $todayDate  = $dt->toDayDateTimeString();

            if (Auth::attempt(['email'=> $username,'password'=> $password])) {
                /** get session */
                $user = Auth::User();
                Session::put('name', $user->name);
                Session::put('email', $user->email);
                Session::put('status', $user->status);

                $updateLastLogin = ['last_login' => $todayDate,];
                User::where('email',$username)->update($updateLastLogin);
                Toastr::success('Login successfully :)','Success');
                return redirect()->intended('home');
            } else {
                Toastr::error('fail, WRONG USERNAME OR PASSWORD :)','Error');
                return redirect('login');
            }
        }catch(\Exception $e) {
            Log::info($e);
            DB::rollback();
            Toastr::error('Add new employee fail :)','Error');
            return redirect()->back();
        }
    }

    /** page logout */
    public function logoutPage()
    {
        return view('auth.logout');
    }

    /** logout and forget session */
    public function logout(Request $request)
    {
        // forget login session
        $request->session()->forget('name');
        $request->session()->forget('email');
        $request->session()->forget('status');
        $request->session()->flush();
        Auth::logout();
        Toastr::success('Logout successfully :)','Success');
        return redirect('logout/page');
    }
}
