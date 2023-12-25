<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Redirect, Validator, Hash, Response, Session, DB;
use App\Models\Massage, App\Models\User;
use App\Models\Entry;
use App\Models\CloakRoom;

<<<<<<< HEAD:app/Http/Controllers/CloakRoomController.php
class CloakRoomController extends Controller {	
=======
class LockerController extends Controller {	
>>>>>>> 195b1d102ab728f04b99cb71ab36dad375becfcc:app/Http/Controllers/LockerController.php
	public function index(){
		return view('admin.cloackrooms.index', [
            "sidebar" => "cloackrooms",
            "subsidebar" => "cloackrooms",
        ]);
	}
	
	public function initLocker(Request $request){
<<<<<<< HEAD:app/Http/Controllers/CloakRoomController.php
		$l_entries = DB::table('cloakroom_entries')->select('cloakroom_entries.*','users.name as username')->leftJoin('users','users.id','=','cloakroom_entries.delete_by');
=======
		$l_entries = DB::table('locker_entries')->select('locker_entries.*','users.name as username')->leftJoin('users','users.id','=','locker_entries.delete_by');
>>>>>>> 195b1d102ab728f04b99cb71ab36dad375becfcc:app/Http/Controllers/LockerController.php
		if($request->unique_id){
			$l_entries = $l_entries->where('cloakroom_entries.unique_id', 'LIKE', '%'.$request->unique_id.'%');
		}		

		if($request->name){
			$l_entries = $l_entries->where('cloakroom_entries.name', 'LIKE', '%'.$request->name.'%');
		}		
		if($request->mobile_no){
			$l_entries = $l_entries->where('cloakroom_entries.mobile_no', 'LIKE', '%'.$request->mobile_no.'%');
		}		
		if($request->pnr_uid){
			$l_entries = $l_entries->where('cloakroom_entries.pnr_uid', 'LIKE', '%'.$request->pnr_uid.'%');
		}		
		
		if(Auth::user()->priv != 1){
			$l_entries = $l_entries->where('deleted',0);
		}
		$l_entries = $l_entries->where('checkout_status', 0);
		$l_entries = $l_entries->orderBy('id', "DESC")->get();


		$pay_types = Entry::payTypes();
		$days = Entry::days();
		$show_pay_types = Entry::showPayTypes();
<<<<<<< HEAD:app/Http/Controllers/CloakRoomController.php
		
=======
		$avail_lockers = Entry::getAvailLockers();
>>>>>>> 195b1d102ab728f04b99cb71ab36dad375becfcc:app/Http/Controllers/LockerController.php

		$data['success'] = true;
		$data['l_entries'] = $l_entries;
		$data['pay_types'] = $pay_types;
		$data['days'] = $days;
		

		return Response::json($data, 200, []);
	}
	public function editLocker(Request $request){
		$l_entry = CloakRoom::where('id', $request->entry_id)->first();

		$sl_lockers = [];

		if($l_entry){
			$l_entry->mobile_no = $l_entry->mobile_no*1;
			$l_entry->train_no = $l_entry->train_no*1;
			$l_entry->pnr_uid = $l_entry->pnr_uid*1;
			$l_entry->paid_amount = $l_entry->paid_amount*1;
			$l_entry->check_in = date("d-m-Y",strtotime($l_entry->date))." ".date("h:i A",strtotime($l_entry->check_in));
			$l_entry->check_out = date("d-m-Y h:i A",strtotime($l_entry->checkout_date));
<<<<<<< HEAD:app/Http/Controllers/CloakRoomController.php
=======
			$sl_lockers = explode(',', $l_entry->locker_ids);
>>>>>>> 195b1d102ab728f04b99cb71ab36dad375becfcc:app/Http/Controllers/LockerController.php
		}

		$data['success'] = true;
		$data['l_entry'] = $l_entry;
<<<<<<< HEAD:app/Http/Controllers/CloakRoomController.php
		
=======
		$data['sl_lockers'] = $sl_lockers;
		return Response::json($data, 200, []);
	}
	public function calCheck(Request $request){
		
		$check_in = $request->check_in;
		$no_of_day = $request->no_of_day;

		$hours = 24*$no_of_day;
		$ss_time = strtotime(date("h:i A",strtotime($check_in)));
		$new_time = date("h:i A", strtotime('+'.$hours.' hours', $ss_time));

		$data['success'] = true;
		$data['check_out'] = $new_time;
>>>>>>> 195b1d102ab728f04b99cb71ab36dad375becfcc:app/Http/Controllers/LockerController.php
		return Response::json($data, 200, []);
	}
	
	public function store(Request $request){

		$check_shift = Entry::checkShift();


		$cre = [
			'name'=>$request->name,
		];

		$rules = [
			'name'=>'required',
		];

		$validator = Validator::make($cre,$rules);

		if($validator->passes()){
			if($request->id){
				$group_id = $request->id;
				$entry = CloakRoom::find($request->id);
				$message = "Updated Successfully!";
			} else {
				$entry = new CloakRoom;
				$message = "Stored Successfully!";
				$entry->unique_id = strtotime('now');
				
			}

			
			$entry->name = $request->name;
			$entry->no_of_bag = $request->no_of_bag;
			$entry->pnr_uid = $request->pnr_uid;
			$entry->mobile_no = $request->mobile_no;

			if($request->id){
				$entry->check_in = date("H:i:s",strtotime($request->check_in));
			}else{
				$entry->check_in = date("H:i:s");
			}
			$entry->no_of_day = $request->no_of_day;
			$entry->pay_type = $request->pay_type;
			$entry->remarks = $request->remarks;
			$entry->paid_amount = $request->paid_amount;
			$entry->save();

			$entry->locker_ids = implode(',',$request->sl_lockers);

			$no_of_min = $request->no_of_day*24*60;

			// $entry->check_out = date("H:i:s",strtotime("+".$no_of_min." minutes",strtotime($entry->check_in)));

			$date = Entry::getPDate();
			$c_date = $date." ".$entry->check_in;
			$checkin_date = date("Y-m-d H:i:s",strtotime($c_date));
			$checkout_date = date("Y-m-d H:i:s",strtotime("+".$entry->no_of_day.' day',strtotime($checkin_date)));

	        $entry->date = $date;
	        $entry->checkin_date = $checkin_date;
	        $entry->checkout_date = $checkout_date;

			$entry->shift = $check_shift;
			$entry->added_by = Auth::id();
			$entry->save();

<<<<<<< HEAD:app/Http/Controllers/CloakRoomController.php
=======
			DB::table('lockers')->whereIn('id',$request->sl_lockers)->update([
				'status' => 1,
			]);
			
>>>>>>> 195b1d102ab728f04b99cb71ab36dad375becfcc:app/Http/Controllers/LockerController.php
			$data['id'] = $entry->id;
			$data['success'] = true;
		} else {
			$data['success'] = false;
			$message = $validator->errors()->first();
		}

		return Response::json($data, 200, []);

	}

	public function printPost($id = 0){

        $print_data = DB::table('cloakroom_entries')->where('id', $id)->first();
        return view('admin.print_page_cloack', compact('print_data'));
	}


    public function checkoutInit(Request $request){

    	$now_time = strtotime(date("Y-m-d H:i:s",strtotime("+5 minutes")));

    	$l_entry = CloakRoom::where('id', $request->entry_id)->first();
    	$checkout_time = strtotime($l_entry->checkout_date);

    	if($checkout_time > $now_time){
    		$data['timeOut'] = false;
    		$entry = CloakRoom::find($request->entry_id);
    		$entry->status = 1; 
    		$entry->checkout_status = 1; 
    		$entry->save();
    		$data['success'] = true;

    
    	} else {
    		$str_day = ($now_time - $checkout_time)/(60 * 60 * 24);
    		$day =0;
    		if($str_day > 0 && $str_day <= 1){
    			$day = 1;
    		}else if($str_day > 1 && $str_day <= 2){
    			$day = 2;
    		}if($str_day > 2 && $str_day <= 3){
    			$day = 3;
    		}if($str_day > 3 && $str_day <= 4){
    			$day = 4;
    		}if($str_day > 4 && $str_day <= 5){
    			$day = 5;
    		}if($str_day > 5 && $str_day <= 6){
    			$day = 6;
    		}if($str_day > 6 && $str_day <= 7){
    			$day = 7;
    		}if($str_day > 7 && $str_day <= 8){
    			$day = 8;
    		}if($str_day > 8 && $str_day <= 9){
    			$day = 9;
    		}if($str_day > 9 && $str_day <= 10){
    			$day = 10;
    		}if($str_day > 10 && $str_day <= 11){
    			$day = 11;
    		}if($str_day > 11 && $str_day <= 12){
    			$day = 12;
    		}if($str_day > 12 && $str_day <= 13){
    			$day = 13;
    		}if($str_day > 13 && $str_day <= 14){
    			$day = 14;
    		}if($str_day > 14 && $str_day <= 15){
    			$day = 15;
    		}if($str_day > 15 && $str_day <= 16){
    			$day = 16;
    		}if($str_day > 16 && $str_day <= 17){
    			$day = 17;
    		}if($str_day > 17 && $str_day <= 18){
    			$day = 18;
    		}if($str_day > 18 && $str_day <= 19){
    			$day = 19;
    		}if($str_day > 19 && $str_day <= 20){
    			$day = 20;
    		}if($str_day > 20 && $str_day <= 21){
    			$day = 21;
    		}if($str_day > 21 && $str_day <= 22){
    			$day = 22;
    		}if($str_day > 22 && $str_day <= 23){
    			$day = 23;
    		}

    		$locker_ids = explode(',', $request->locker_ids);

			$l_entry->mobile_no = $l_entry->mobile_no*1;
			$l_entry->train_no = $l_entry->train_no*1;
			$l_entry->pnr_uid = $l_entry->pnr_uid*1;
			$l_entry->paid_amount = $l_entry->paid_amount*1;
<<<<<<< HEAD:app/Http/Controllers/CloakRoomController.php
			$l_entry->balance = $day*70*$l_entry->no_of_bag;
=======
			$l_entry->balance = $day*70*sizeof($locker_ids);
>>>>>>> 195b1d102ab728f04b99cb71ab36dad375becfcc:app/Http/Controllers/LockerController.php
			$l_entry->total_balance = $l_entry->paid_amount+$l_entry->balance;
			$l_entry->day = $day;

			
			$data['l_entry'] = $l_entry;
			$data['success'] = true;
			$data['timeOut'] = true;
		}

		return Response::json($data, 200, []);
    }

    public function checkoutStore(Request $request){
    	$check_shift = Entry::checkShift();
    	$entry = CloakRoom::find($request->id);


		$entry->status = 1; 
		$entry->checkout_status = 1;
		$entry->penality = $request->balance;
		$entry->checkout_date = date('Y-m-d H:i:s'); 
		$entry->save();

		$date = Entry::getPDate();


		DB::table('cloakroom_penalty')->insert([
			'cloakroom_id' => $entry->id,
			'penalty_amount' => $request->balance,
			'pay_type' => $request->pay_type,
			'shift' => $check_shift,
			'date' =>$date,
			'added_by' =>Auth::id(),
			'current_time' => date("H:i:s"),
			'created_at' => date('Y-m-d H:i:s'),
		]);

		$locker_ids = explode(',', $request->locker_ids);

		DB::table('lockers')->whereIn('id',$locker_ids)->update(['status'=>0]);
		$data['success'] = true;
		return Response::json($data, 200, []);
    }
    
    public function delete($id){
    	DB::table('cloakroom_entries')->where('id',$id)->update([
    		'deleted' => 1,
    		'delete_by' => Auth::id(),
    		'delete_time' => date("Y-m-d H:i:s"),
    	]);

    	$data['success'] = true;
    	$data['message'] = "Successfully";

		return Response::json($data, 200, []);
	}


}
