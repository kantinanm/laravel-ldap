<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Adldap\Laravel\Facades\Adldap;
use App\User;
use Illuminate\Http\Request;

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



    public function username() {
        //return config('adldap_auth.usernames.eloquent');
        return 'username';
    }


    public function showLoginForm()
    {
        return view('auth.login');
    }


    public function postLogin(Request $request)
    {
        //dd("tet");
        $rules = [
            'username' => 'required|min:6',
            'password' => 'required|min:3'
        ];
        $this->validate($request, $rules);


        // Form validated, check if the authentication matches
       /* if (Auth::attempt($request->input(), true)) {
            $user = Auth::user();
            // @todo: Sync the groups
            return redirect()->intended('dashboard');
        }*/

        try {
            if (Adldap::auth()->attempt($request->username, $request->password, $bindAsUser = true)) {
                dump(Auth::user()); // the result always null
                dd('Credentials were correct');
            } else {
                dd('Credentials were incorrect');
            }
    
        } catch (\Adldap\Auth\UsernameRequiredException $e) {
            dd('The user didn\'t supply a username');
        } catch (\Adldap\Auth\PasswordRequiredException $e) {
            dd('The user didn\'t supply a password');
        }


    }

    
    protected function attemptLogin(Request $request)
    {
        //dd("test");
        $credentials = $request->only($this->username(), 'password');
        $username = $credentials[$this->username()];
        $password = $credentials['password'];
        //dd(env('ADLDAP_USER_FORMAT'));

        $user_format = env('ADLDAP_USER_FORMAT');
        $userdn = sprintf($user_format, $username);
        //$userdn ="nu\\". $username;
        //dd("userdn"+$userdn);
        //if (Adldap::auth()->attempt($userdn, $password, $bindAsUser = true)) {
        if (Adldap::auth()->attempt($username, $password, $bindAsUser = true)) {
            //dd("true");

            $user = \App\User::where($this->username(), $username)->first();

            if (!$user) {

                $user = new \App\User();
                $user->username = $username;
                $user->password = '';
                //$user->email = $username.'@nu.ac.th';

                $sync_attrs = $this->retrieveSyncAttributes($username);
                foreach ($sync_attrs as $field => $value) {
                    $user->$field = $value !== null ? $value : '';
                }
            }

            $this->guard()->login($user, true);
            return true;
        }
        // the user doesn't exist in the LDAP server or the password is wrong
        // log error
        //dd("false");
        return false;
    }
    

    protected function retrieveSyncAttributes($username)
    {
        $ldapuser = Adldap::search()->where(env('ADLDAP_USER_ATTRIBUTE'), '=', $username)->first();
        if (!$ldapuser) {
            // log error
            return false;
        }
        
        $ldapuser_attrs = null;

        $attrs = [];

        foreach (config('adldap_auth.sync_attributes') as $local_attr => $ldap_attr) {
            if ($local_attr == 'username') {
                continue;
            }

            $method = 'get' . $ldap_attr;
            if (method_exists($ldapuser, $method)) {
                $attrs[$local_attr] = $ldapuser->$method();
                continue;
            }

            if ($ldapuser_attrs === null) {
                $ldapuser_attrs = self::accessProtected($ldapuser, 'attributes');
            }

            if (!isset($ldapuser_attrs[$ldap_attr])) {
                // an exception could be thrown
                $attrs[$local_attr] = null;
                continue;
            }

            if (!is_array($ldapuser_attrs[$ldap_attr])) {
                $attrs[$local_attr] = $ldapuser_attrs[$ldap_attr];
            }

            if (count($ldapuser_attrs[$ldap_attr]) == 0) {
                // an exception could be thrown
                $attrs[$local_attr] = null;
                continue;
            }

            $attrs[$local_attr] = $ldapuser_attrs[$ldap_attr][0];
            //$attrs[$local_attr] = implode(',', $ldapuser_attrs[$ldap_attr]);
        }

        return $attrs;
    }

    protected static function accessProtected($obj, $prop)
    {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);
        return $property->getValue($obj);
    }









}
