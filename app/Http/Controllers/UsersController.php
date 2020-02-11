<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Adldap\Laravel\Facades\Adldap;

class UsersController extends Controller
{


    public function index()
    {

        try {

            if(Adldap::auth()->attempt('user', 'password')){

                $getCommonName = Adldap::search()->users()->find('xxxx@nu.ac.th');
                dd($getCommonName);

               return "True";
            }else{
                return "False";
            }


        } catch (\ErrorException $e) {
            // Catch LDAP Bind Errors.
            return $e;
        }
    }



}
