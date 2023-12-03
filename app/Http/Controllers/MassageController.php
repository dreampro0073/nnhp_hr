<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Redirect, Validator, Hash, Response, Session, DB;
use App\Models\Massage, App\Models\User;
use App\Models\Entry;


use Crypt;

use Dompdf\Dompdf;
use Dompdf\Options;

class MassageController extends Controller {
	
	public function massage(){
		return view('admin.massage.index', [
            "sidebar" => "massage",
            "subsidebar" => "massage",
        ]);
	}

	public function initMassage(Request $request){
		

		$m_entries = DB::table('massage_entries');

		if($request->unique_id){
			$m_entries = $m_entries->where('massage_entries.unique_id', 'LIKE', '%'.$request->unique_id.'%');
		}	


		if(Auth::id() != 1){
			$m_entries = $m_entries->where('deleted',0);
		}
		$m_entries = $m_entries->orderBy('id','DESC')->get();

		$show_pay_types = Entry::showPayTypes();
		if(sizeof($m_entries) > 0){
			foreach ($m_entries as $item) {
				$item->pay_by = isset($item->pay_type)?$show_pay_types[$item->pay_type]:'';
				$item->delete_time = date("d-m-Y h:i A",strtotime($item->delete_time));
			}

		}

		$pay_types = Entry::payTypes();
		$data['success'] = true;
		$data['m_entries'] = $m_entries;
		$data['pay_types'] = $pay_types;

		return Response::json($data,200,array());
	}
	public function editMassage(Request $request){
		$m_entry = Massage::where('id', $request->m_id)->first();

		if($m_entry){
			$m_entry->paid_amount = $m_entry->paid_amount*1;
			$m_entry->in_time = date("H:i A",strtotime($m_entry->in_time));
			$m_entry->out_time = date("H:i A",strtotime($m_entry->out_time));
		}

		$data['success'] = true;
		$data['m_entry'] = $m_entry;
		return Response::json($data, 200, []);
	}
	public function changeTime(Request $request){
		
		$in_time = $request->in_time;


		$time_period = $request->time_period;

		$ss_time = strtotime(date("h:i A",strtotime($in_time)));

		$new_time = date("h:i A", strtotime('+'.$time_period.' minutes', $ss_time));

		$data['success'] = true;
		$data['out_time'] = $new_time;
		return Response::json($data, 200, []);

		
	}
	public function checkMC(Request $request){
			
		// $in_time  = date("h:i A",strtotime("+10 minutes"));
		// $in_time  = date("h:i A",strtotime("+2 minutes"));

		// $now_time = "20:19:00";

		// dd($now_time);
		$in_time = '';	
		$now_time = date("H:i:s",strtotime("+2 minutes"));


		$check1 = DB::table('massage_entries')->where('chair_no',1)->where('out_time','>',$now_time)->where('date',date("Y-m-d"))->orderBy('id','DESC')->first();

		$check2= DB::table('massage_entries')->where('chair_no',2)->where('out_time','>',$now_time)->where('date',date("Y-m-d"))->orderBy('id','DESC')->first();

		// dd($check1);

		if(!$check1 && !$check2){
			$in_time = $now_time;
			$chair_no = 1;
		}else if($check1 && !$check2){
			$in_time =  $now_time;
			$chair_no = 2;
		}elseif(!$check1 && $check2){
			$in_time =  $now_time;
			$chair_no = 1;
		}elseif($check1 && $check2){
			if(strtotime($check1->out_time) < strtotime($check2->out_time)){
				$in_time = date("H:i:s",strtotime("+2 minutes",strtotime($check1->out_time)));
				$chair_no = 1;
			}else{
				$in_time = date("H:i:s",strtotime("+2 minutes",strtotime($check2->out_time)));
				$chair_no = 2;
			}
		}

		$data['success'] = true;
		$data['in_time'] = date("h:i A",strtotime($in_time));
		$data['chair_no'] = $chair_no;
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
				$entry = Massage::find($request->id);
				$message = "Updated Successfully!";
			} else {
				$entry = new Massage;
				$message = "Stored Successfully!";
				$entry->unique_id = strtotime('now');
				
			}

			$entry->name = $request->name;
			$entry->in_time = date("H:i:s",strtotime($request->in_time));
			$entry->out_time = date("H:i:s",strtotime($request->out_time));
			$entry->paid_amount = $request->paid_amount;
			$entry->pay_type = $request->pay_type;
			$entry->remarks = $request->remarks;
			$entry->date = date("Y-m-d");
			$entry->time_period = $request->time_period;
			$entry->chair_no = $request->chair_no;
			$entry->shift = $check_shift;
			$entry->added_by = Auth::id();

			$date = Entry::getPDate();
	        $entry->date = $date;

			$entry->save();

			
			$data['id'] = $entry->id;
			$data['success'] = true;
		} else {
			$data['success'] = false;
			$message = $validator->errors()->first();
		}

		return Response::json($data, 200, []);

	}

	public function printPost($id = 0){

        $print_data = DB::table('massage_entries')->where('id', $id)->first();
        return view('admin.print_page_massage', compact('print_data'));

		
		// $this->printFinal($print_data);
	}

	// public function printFinal($print_data){

 //        $options = new Options();
 //        $options->set('isRemoteEnabled', true);

 //        $dompdf = new Dompdf($options);

 //        define("DOMPDF_UNICODE_ENABLED", true);

 //        $html = view('admin.print_page_massage', compact('print_data'));

 //        $dompdf->loadHtml($html);
 //        $dompdf->setPaper([0,0,230,280]);
 //        $dompdf->render();
 //        $dompdf->stream(date("dmY",strtotime("now")).'.pdf',array("Attachment" => false));
 //    }


	public function delete($id){
    	DB::table('massage_entries')->where('id',$id)->update([
    		'deleted' => 1,
    		'delete_by' => Auth::id(),
    		'delete_time' => date("Y-m-d H:i:s"),

    	]);

    	$data['success'] = true;
    	$data['message'] = "Successfully";
		
		return Response::json($data, 200, []);
    }


}
