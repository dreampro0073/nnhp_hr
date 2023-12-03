<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

use Redirect, Validator, Hash, Response, Session, DB,DateTime;

use App\Models\User;
use Crypt;

use Dompdf\Dompdf;
use Dompdf\Options;

class UserController extends Controller {

    public function index(){


        return Redirect::to('admin/dashboard');
        
        return view('index');
    }

   
    public function print(){
        return view('admin.print_page');
    }

   

  
    public function testx(){
        $start_date = "02-01-2024";
    }

	public function login(){    
		return view('login');
	}


	public function postLogin(Request $request){
		$cre = ["email"=>$request->input("email"),"password"=>$request->input("password")];
		$rules = ["email"=>"required","password"=>"required"];
		$validator = Validator::make($cre,$rules);
		
        if($validator->passes()){

			
            if(Auth::attempt($cre)){
                
                return Redirect::to('/admin/sitting');

			} else {
                return Redirect::back()->withInput()->with('failure','Invalid username or password');
			}

		} else {
            return Redirect::back()->withErrors($validator)->withInput();
		}

	}
    public function changePassword(){
        return view('update_password');
    }

    public function updatePassword(Request $request){
        $cre = ["old_password"=>$request->old_password,"new_password"=>$request->new_password,"confirm_password"=>$request->confirm_password];
        $rules = ["old_password"=>'required',"new_password"=>'required|min:5',"confirm_password"=>'required|same:new_password'];
        $old_password = Hash::make($request->old_password);
        $validator = Validator::make($cre,$rules);
        if ($validator->passes()) { 
            if (Hash::check($request->old_password, Auth::user()->password )) {
                $password = Hash::make($request->new_password);
                $user = User::find(Auth::id());
                $user->password = $password;
                $user->password_check = $request->new_password;
                $user->save();
                
                return Redirect::back()->with('success', 'Password changed successfully ');
                
            } else {
                return Redirect::back()->withInput()->with('failure', 'Old password does not match.');
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        return Redirect::back()->withErrors($validator)->withInput()->with('failure','Unauthorised Access or Invalid Password');
    }
    
}