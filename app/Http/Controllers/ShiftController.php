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



class ShiftController extends Controller {
	
	public function index(){
		return view('admin.shift.index', [
            "sidebar" => "shift",
            "subsidebar" => "shift",
        ]);
		
	}

	public function init(){

		$current_shift = Entry::checkShift();
		$shitting_data = Entry::totalShiftData();
		$massage_data = Massage::totalShiftData();
		$cloack_data = CloakRoom::totalShiftData();
		
		$data['shitting_data'] = $shitting_data;
		// dd($data);
		$data['massage_data'] = $massage_data;
		$data['cloack_data'] = $cloack_data;
	
		$data['total_shift_upi'] = $shitting_data['total_shift_upi'] + $massage_data['total_shift_upi'] + $cloack_data['total_shift_upi'];
        $data['total_shift_cash'] = $shitting_data['total_shift_cash'] + $massage_data['total_shift_cash'] + $cloack_data['total_shift_cash'];
        $data['total_collection'] = $shitting_data['total_collection'] + $massage_data['total_collection'] + $cloack_data['total_collection'];

        $data['last_hour_upi_total'] = $shitting_data['last_hour_upi_total'] + $massage_data['last_hour_upi_total'] + $cloack_data['last_hour_upi_total'];
        $data['last_hour_cash_total'] = $shitting_data['last_hour_cash_total'] + $massage_data['last_hour_cash_total'] + $cloack_data['last_hour_cash_total'];
        $data['last_hour_total'] = $shitting_data['last_hour_total'] + $massage_data['last_hour_total'] + $cloack_data['last_hour_total'];
        
        $data['check_shift'] = $current_shift;
        $data['shift_date'] = $shitting_data['shift_date'];

        $data['previous_data'] = '';

		$data['success'] = true;
		return Response::json($data, 200, []);
	}
	

	public function print($type =1){
		$current_shift = Entry::checkShift($type);
		$current_shift = Entry::checkShift(2);
		$shitting_data = Entry::totalShiftData();
		$massage_data = Massage::totalShiftData();

		$total_shift_upi = $shitting_data['total_shift_upi'] + $massage_data['total_shift_upi'];
        $total_shift_cash = $shitting_data['total_shift_cash'] + $massage_data['total_shift_cash'];
        $total_collection = $shitting_data['total_collection'] + $massage_data['total_collection'];

		
        return view('admin.print_shift',[
        	'shitting_data'=>$shitting_data,
        	'massage_data'=>$massage_data,
        	'total_shift_upi'=>$total_shift_upi,
        	'total_shift_cash'=>$total_shift_cash,
        	'total_collection'=>$total_collection,
        ]);
	}

}
