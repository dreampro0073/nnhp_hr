<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Redirect, Validator, Hash, Response, Session, DB;
use App\Models\Massage, App\Models\User;
use App\Models\Entry;
use App\Models\Locker;

class LockerController extends Controller {
	
	public function index(){
		return view('admin.locker.index', [
            "sidebar" => "locker",
            "subsidebar" => "locker",
        ]);
	}

	public function initLocker(Request $request){
		
		$l_entries = DB::table('locker_entries')->select('locker_entries.*','users.name as username')->leftJoin('users','users.id','=','locker_entries.delete_by');
		if($request->unique_id){
			$l_entries = $l_entries->where('locker_entries.unique_id', 'LIKE', '%'.$request->unique_id.'%');
		}		

		if($request->name){
			$l_entries = $l_entries->where('locker_entries.name', 'LIKE', '%'.$request->name.'%');
		}		
		if($request->mobile_no){
			$l_entries = $l_entries->where('locker_entries.mobile_no', 'LIKE', '%'.$request->mobile_no.'%');
		}		
		if($request->pnr_uid){
			$l_entries = $l_entries->where('locker_entries.pnr_uid', 'LIKE', '%'.$request->pnr_uid.'%');
		}		
		if($request->train_no){
			$l_entries = $l_entries->where('locker_entries.train_no', 'LIKE', '%'.$request->train_no.'%');
		}

		$date_ar = [date("Y-m-d",strtotime('-1 day')),date("Y-m-d",strtotime("now"))];
		
		if(Auth::id() != 1){
			$l_entries = $l_entries->where('deleted',0);
		}
		$l_entries = $l_entries->where('checkout_status', 0);
		$l_entries = $l_entries->orderBy('id', "DESC")->get();


		$pay_types = Entry::payTypes();
		$days = Entry::days();
		$show_pay_types = Entry::showPayTypes();
		$avail_lockers = Entry::getAvailLockers();

		if(sizeof($l_entries) > 0){
			foreach ($l_entries as $item) {
				$item->pay_by = isset($item->pay_type)?$show_pay_types[$item->pay_type]:'';

				$item->delete_time = date("d-m-Y h:i A",strtotime($item->delete_time));

			}

		}

		$data['success'] = true;
		$data['l_entries'] = $l_entries;
		$data['pay_types'] = $pay_types;
		$data['days'] = $days;
		$data['avail_lockers'] = $avail_lockers;

		return Response::json($data, 200, []);
	}
	public function editLocker(Request $request){
		$l_entry = Locker::where('id', $request->entry_id)->first();

		if($l_entry){
			$l_entry->mobile_no = $l_entry->mobile_no*1;
			$l_entry->train_no = $l_entry->train_no*1;
			$l_entry->pnr_uid = $l_entry->pnr_uid*1;
			$l_entry->paid_amount = $l_entry->paid_amount*1;
			$l_entry->check_in = date("h:i A",strtotime($l_entry->check_in));
			$l_entry->check_out = date("h:i A",strtotime($l_entry->check_out));
		}

		$data['success'] = true;
		$data['l_entry'] = $l_entry;
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
				$entry = Locker::find($request->id);
				$message = "Updated Successfully!";
			} else {
				$entry = new Locker;
				$message = "Stored Successfully!";
				$entry->unique_id = strtotime('now');
				
			}

			
			$entry->name = $request->name;
			$entry->nos = $request->nos;
			$entry->pnr_uid = $request->pnr_uid;
			$entry->mobile_no = $request->mobile_no;
			// $entry->train_no = $request->train_no;
			// $entry->address = $request->address;

			// $entry->check_in = date("H:i:s",strtotime($request->check_in));
			// $entry->check_out = date("H:i:s",strtotime($request->check_out));

			if($request->id){
				$entry->check_in = date("H:i:s",strtotime($request->check_in));
			}else{
				$entry->check_in = date("H:i:s");
			}
			$entry->locker_id = $request->locker_id;
			$entry->no_of_day = $request->no_of_day;
			$entry->pay_type = $request->pay_type;
			$entry->remarks = $request->remarks;
			$entry->paid_amount = $request->paid_amount;
			$entry->save();

			$no_of_min = $request->no_of_day*24*60;

			$entry->check_out = date("H:i:s",strtotime("+".$no_of_min." minutes",strtotime($entry->check_in)));

			$date = date("Y-m-d");
			$checkout_date = date("Y-m-d H:i:s",strtotime("+".$entry->no_of_day.' day',strtotime($date.$entry->check_out)));
			$date = Entry::getPDate();
	        $entry->date = $date;
	        $entry->checkout_date = $checkout_date;

			$entry->shift = $check_shift;
			$entry->added_by = Auth::id();


			$entry->save();

			DB::table('lockers')->where('id',$request->locker_id)->update([
				'status' => 1,
			]);
			
			$data['id'] = $entry->id;
			$data['success'] = true;
		} else {
			$data['success'] = false;
			$message = $validator->errors()->first();
		}

		return Response::json($data, 200, []);

	}

	public function printPost($id = 0){

        $print_data = DB::table('locker_entries')->where('id', $id)->first();
        return view('admin.print_page_locker', compact('print_data'));
	}


    public function checkoutInit(Request $request){

    	$now_time = strtotime(date("Y-m-d H:i:s",strtotime("+5 minutes")));

    	$l_entry = Locker::where('id', $request->entry_id)->first();
    	$checkout_time = strtotime($l_entry->checkout_date);

    	if($checkout_time > $now_time){
    		$data['timeOut'] = false;
    		$entry = Locker::find($request->entry_id);
    		$entry->status = 1; 
    		$entry->checkout_status = 1; 
    		$entry->save();
    		$data['success'] = true;

    		DB::table('lockers')->where('id',$l_entry->locker_id)->update(['status'=>0]);
    
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
    		}

			$l_entry->mobile_no = $l_entry->mobile_no*1;
			$l_entry->train_no = $l_entry->train_no*1;
			$l_entry->pnr_uid = $l_entry->pnr_uid*1;
			$l_entry->paid_amount = $l_entry->paid_amount*1;
			$l_entry->balance = $day*70;
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
    	$entry = Locker::find($request->id);


		$entry->status = 1; 
		$entry->checkout_status = 1;
		$entry->penality = $request->balance;
		$entry->checkout_date = date('Y-m-d H:i:s'); 
		$entry->save();

		$date = Entry::getPDate();


		DB::table('locker_penalty')->insert([
			'locker_entry_id' => $entry->id,
			'penalty_amount' => $request->balance,
			'pay_type' => $request->pay_type,
			'shift' => $check_shift,
			'date' =>$date,
			'current_time' => date("H:i:s"),
			'created_at' => date('Y-m-d H:i:s'),
		]);

		$data['success'] = true;
		return Response::json($data, 200, []);
    }


    public function delete($id){
    	DB::table('locker_entries')->where('id',$id)->update([
    		'deleted' => 1,
    		'delete_by' => Auth::id(),
    		'delete_time' => date("Y-m-d H:i:s"),
    	]);

    	$data['success'] = true;
    	$data['message'] = "Successfully";
		
		return Response::json($data, 200, []);
	}


}
