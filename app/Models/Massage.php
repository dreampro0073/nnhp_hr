<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

use DB;
use App\Models\Entry;


class Massage extends Model
{

    protected $table = 'massage_entries';

        public static function totalShiftData(){
        $check_shift = Entry::checkShift();
        
        $total_shift_cash = 0;
        $total_shift_upi = 0;       

        $last_hour_cash_total = 0;
        $last_hour_upi_total = 0;

        $from_time = date('H:00:00');
        $to_time = date('H:59:59');

        if($check_shift != "C"){
            $shift_date = date("Y-m-d");   
   
            $total_shift_upi = Massage::where('date',$shift_date)->where('pay_type',2)->where('shift', $check_shift)->where('deleted',0)->sum("paid_amount");

            $total_shift_cash = Massage::where('date',$shift_date)->where('pay_type',1)->where('shift', $check_shift)->where('deleted',0)->sum("paid_amount");  

            $last_hour_upi_total = Massage::where('date',$shift_date)->where('pay_type',2)->where('shift', $check_shift)->where('deleted',0)->whereBetween('in_time', [$from_time, $to_time])->sum("paid_amount");

            $last_hour_cash_total = Massage::where('date',$shift_date)->where('pay_type',1)->where('shift', $check_shift)->where('deleted',0)->whereBetween('in_time', [$from_time, $to_time])->sum("paid_amount"); 


            $shift_date = date("d-m-Y");   


        }
        
        if($check_shift == "C"){

            // $p_date = date("Y-m-d",strtotime("-1 day"));

            $p_date = Entry::getPDate();
            $shift_date = date("d-m-Y",strtotime($p_date));


            $total_shift_upi = Massage::where('date',$p_date)->where('shift', $check_shift)->where('deleted',0)->where('pay_type',2)->sum("paid_amount");

            $total_shift_cash = Massage::where('date',$p_date)->where('shift', $check_shift)->where('deleted',0)->where('pay_type',1)->sum("paid_amount");

            $last_hour_upi_total = Massage::where('date',$p_date)->where('shift', $check_shift)->where('deleted',0)->where('pay_type',2)->whereBetween('in_time', [$from_time, $to_time])->sum("paid_amount"); 

            $last_hour_cash_total = Massage::where('date',$p_date)->where('shift', $check_shift)->where('deleted',0)->where('pay_type',1)->whereBetween('in_time', [$from_time, $to_time])->sum("paid_amount");
            
        }

        $total_collection = $total_shift_upi + $total_shift_cash;
        $last_hour_total = $last_hour_upi_total + $last_hour_cash_total;

        $data['total_shift_upi'] = $total_shift_upi;
        $data['total_shift_cash'] = $total_shift_cash;
        $data['total_collection'] = $total_collection;

        $data['last_hour_upi_total'] = $last_hour_upi_total;
        $data['last_hour_cash_total'] = $last_hour_cash_total;
        $data['last_hour_total'] = $last_hour_total;
        $data['check_shift'] = $check_shift;
        $data['shift_date'] = $shift_date;

        return $data;
    } 


    public static function totalPrevShiftData(){
        $check_shift = Entry::checkShift(2);
        
        $total_shift_cash = 0;
        $total_shift_upi = 0; 

        if($check_shift == "B"){
            if(date('H') > 6) {
                $shift_date = date("Y-m-d"); 
            } else {
                $shift_date = date("Y-m-d",strtotime("-1 day"));   
            }
        }else{
            $shift_date = date("Y-m-d");
        }      

        if($check_shift != "C"){
            $total_shift_upi = Massage::where('date',date("Y-m-d"))->where('pay_type',2)->where('shift', $check_shift)->where('deleted',0)->sum("paid_amount");

            $total_shift_cash = Massage::where('date',date("Y-m-d"))->where('pay_type',1)->where('shift', $check_shift)->where('deleted',0)->sum("paid_amount");  

            // $shift_date = date("d-m-Y");   

        }
        
        if($check_shift == "C"){

            $shift_date = date("Y-m-d",strtotime("-1 day"));

            $total_shift_upi = Massage::whereBetween('date',[date("Y-m-d",strtotime("-1 day")),date("Y-m-d")])->where('shift', $check_shift)->where('deleted',0)->where('pay_type',2)->sum("paid_amount");

            $total_shift_cash = Massage::whereBetween('date',[date("Y-m-d",strtotime("-1 day")),date("Y-m-d")])->where('shift', $check_shift)->where('deleted',0)->where('pay_type',1)->sum("paid_amount"); 
        }

        $total_collection = $total_shift_upi + $total_shift_cash;

        $data['total_shift_upi'] = $total_shift_upi;
        $data['total_shift_cash'] = $total_shift_cash;
        $data['total_collection'] = $total_collection;

        $data['check_shift'] = $check_shift;
        $data['shift_date'] = date('d-m-Y', strtotime($shift_date));

        return $data;
    }

}