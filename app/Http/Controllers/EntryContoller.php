<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Redirect, Validator, Hash, Response, Session, DB;
use App\Models\Entry, App\Models\User;

use Crypt;

use Dompdf\Dompdf;
use Dompdf\Options;

class EntryContoller extends Controller {
	public function initEntries(Request $request){

		// $check_shift = Entry::checkShift();
		$entries = Entry::select('sitting_entries.*','users.name as username')->leftJoin('users','users.id','=','sitting_entries.delete_by');
		if($request->unique_id){
			$entries = $entries->where('sitting_entries.unique_id', 'LIKE', '%'.$request->unique_id.'%');
		}		

		if($request->name){
			$entries = $entries->where('sitting_entries.name', 'LIKE', '%'.$request->name.'%');
		}		
		if($request->mobile_no){
			$entries = $entries->where('sitting_entries.mobile_no', 'LIKE', '%'.$request->mobile_no.'%');
		}		
		if($request->pnr_uid){
			$entries = $entries->where('sitting_entries.pnr_uid', 'LIKE', '%'.$request->pnr_uid.'%');
		}		
		if($request->train_no){
			$entries = $entries->where('sitting_entries.train_no', 'LIKE', '%'.$request->train_no.'%');
		}

		if(Auth::id() != 1){
			$entries = $entries->where('deleted',0);

		}
		$entries = $entries->orderBy('id', "DESC");

		if(Auth::id() != 1){
			$entries = $entries->take(500);
		}
		$entries = $entries->get();

		
		$data = Entry::totalShiftData();

		$pay_types = Entry::payTypes();
		$hours = Entry::hours();

		// $show_pay_types = Entry::showPayTypes();
		// if(sizeof($entries) > 0){
		// 	foreach ($entries as $item) {
		// 		$item->pay_by = isset($item->pay_type)?$show_pay_types[$item->pay_type]:'';
		// 		$item->delete_time = date("d-m-Y h:i A",strtotime($item->delete_time));


		// 	}

		// }

		$data['success'] = true;
		$data['entries'] = $entries;
		$data['pay_types'] = $pay_types;
		$data['hours'] = $hours;
		return Response::json($data, 200, []);
	}	
	
	public function editEntry(Request $request){
		$sitting_entry = Entry::where('id', $request->entry_id)->first();

		if($sitting_entry){
			$sitting_entry->mobile_no = $sitting_entry->mobile_no*1;
			$sitting_entry->train_no = $sitting_entry->train_no*1;
			$sitting_entry->pnr_uid = $sitting_entry->pnr_uid*1;
			$sitting_entry->paid_amount = $sitting_entry->paid_amount*1;
			$sitting_entry->total_amount = $sitting_entry->paid_amount*1;
			// $sitting_entry->no_of_children = $sitting_entry->no_of_children*1;


			$sitting_entry->check_in = date("h:i A",strtotime($sitting_entry->check_in));
			$sitting_entry->check_out = date("h:i A",strtotime($sitting_entry->check_out));
		}

		$data['success'] = true;
		$data['sitting_entry'] = $sitting_entry;
		return Response::json($data, 200, []);
	}
	// public function calCheck(Request $request){
		
	// 	$check_in = $request->check_in;
	// 	$hours_occ = $request->hours_occ;


	// 	$ss_time = strtotime(date("H:i:s",strtotime($check_in)));
	// 	$new_time = date("H:i:s", strtotime('+'.$hours_occ.' hours', $ss_time));

	// 	$data['success'] = true;
	// 	$data['check_out'] = $new_time;
	// 	return Response::json($data, 200, []);
	// }

	public function calCheck(Request $request){
		
		$check_in = $request->check_in;
		$hours_occ = $request->hours_occ;

		$ss_time = strtotime(date("h:i A",strtotime($check_in)));

		$new_time = date("h:i A", strtotime('+'.$hours_occ.' hours', $ss_time));

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
			$total_amount = $request->total_amount;
			if($request->id){
				$group_id = $request->id;
				$entry = Entry::find($request->id);
				$message = "Updated Successfully!";

				if(isset($entry)){
					if($check_shift != $entry->shift){
						$total_amount = $total_amount - $entry->paid_amount;
						$entry = new Entry;
						$message = "Stored Successfully!";
						$entry->unique_id = strtotime('now');
					}
				}

			} else {
				$entry = new Entry;
				$message = "Stored Successfully!";
				$entry->unique_id = strtotime('now');
				
			}

			$entry->name = $request->name;
			$entry->pnr_uid = $request->pnr_uid;
			$entry->mobile_no = $request->mobile_no;
			// $entry->train_no = $request->train_no;
			// $entry->address = $request->address;
			$entry->no_of_adults = $request->no_of_adults ? $request->no_of_adults : 0;
			$entry->no_of_children = $request->no_of_children ? $request->no_of_children : 0;
			$entry->no_of_baby_staff = $request->no_of_baby_staff ? $request->no_of_baby_staff : 0;
			$entry->hours_occ = $request->hours_occ ? $request->hours_occ : 0;
			// $entry->check_in = date("H:i:s",strtotime($request->check_in));
			// $entry->check_out = date("H:i:s",strtotime($request->check_out));



			if($request->id){
				$entry->check_in = date("H:i:s",strtotime($request->check_in));
			}else{
				$entry->check_in = date("H:i:s");
			}

			$entry->seat_no = $request->seat_no;
			$entry->paid_amount = $total_amount;
			$entry->pay_type = $request->pay_type;
			$entry->remarks = $request->remarks;
			$entry->shift = $check_shift;
			$entry->save();
			$no_of_min = $request->hours_occ*60;

			$entry->check_out = date("H:i:s",strtotime("+".$no_of_min." minutes",strtotime($entry->check_in)));

			$check_in_time = strtotime($entry->check_in);
        	$date = Entry::getPDate();
	        $entry->date = $date;
        	
        	
	        $entry->date = $date;
			$entry->added_by = Auth::id();
	        
			$entry->save();

			



			if(!$request->id ){
				// $entry->unique_id = date('Y').000000 + $entry->id;
			}
			$data['id'] = $entry->id;
			$data['success'] = true;
		} else {
			$data['success'] = false;
			$message = $validator->errors()->first();
		}

		return Response::json($data, 200, []);

	}

	public function printReports(){
		$print_data = new \stdClass;
		$data = Entry::totalShiftData();
		$print_data->type = "shift";
		$print_data->total_shift_cash = $data['total_shift_cash']; 
		$print_data->total_shift_upi = $data['total_shift_upi'];
		$print_data->total_collection = $data['total_collection'];
		$print_data->last_hour_upi_total = $data['last_hour_upi_total'];
		$print_data->last_hour_cash_total = $data['last_hour_cash_total'];
		$print_data->last_hour_total = $data['last_hour_total'];
		$print_data->check_shift = $data['check_shift'];
		$print_data->shift_date = $data['shift_date'];
		$this->printFinal($print_data);
	}
	
	public function printPost($id = 0){


        $print_data = DB::table('sitting_entries')->where('id', $id)->first();
		$print_data->type = "silip";
        
        $print_data->total_member = $print_data->no_of_adults + $print_data->no_of_children + $print_data->no_of_baby_staff;
        $print_data->adult_amount = 0;
        $print_data->children_amount = 0;
        $hours = $print_data->hours_occ;

        if($hours > 0) {
            $print_data->adult_amount = $print_data->no_of_adults * 30 * $hours;
            $print_data->children_amount = $print_data->no_of_children * 20 * $hours;
        }
              
		return view('admin.print_sitting',compact('print_data'));
	}

	public function printFinal($print_data){

		// return view('admin.print_sitting', compact('print_data'));
        // $options = new Options();
        // $options->set('isRemoteEnabled', true);

        // $dompdf = new Dompdf($options);

        // define("DOMPDF_UNICODE_ENABLED", true);

        // $html = view('admin.print_page_sitting', compact('print_data'));

        // $dompdf->loadHtml($html);
        // $dompdf->setPaper([0,0,300,405]);
        // $dompdf->render();
        // $dompdf->stream(date("dmY",strtotime("now")).'.pdf',array("Attachment" => false));
    }

    public function delete($id){
    	DB::table('sitting_entries')->where('id',$id)->update([
    		'deleted' => 1,
    		'delete_by' => Auth::id(),
    		'delete_time' => date("Y-m-d H:i:s"),

    	]);

    	$data['success'] = true;
    	$data['message'] = "Successfully";
		
		return Response::json($data, 200, []);
    }

}
